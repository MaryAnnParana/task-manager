<?php

// Initialize SQLite database on first request for Vercel
$db_path = '/tmp/database.sqlite';
if (!file_exists($db_path)) {
    // Create empty database file
    touch($db_path);
    
    // Bootstrap Laravel to run migrations
    require __DIR__ . '/../bootstrap/app.php';
    
    // Run migrations
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
}

// Forward Vercel requests to the native Laravel public index file
require __DIR__ . '/../public/index.php';