<?php

// Initialize SQLite database for Vercel (ephemeral storage)
$db_path = '/tmp/database.sqlite';

// Create database if it doesn't exist
if (!file_exists($db_path)) {
    try {
        // Create the SQLite database
        $pdo = new PDO("sqlite:$db_path");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Enable foreign keys
        $pdo->exec('PRAGMA foreign_keys = ON;');
        
        // Create tables
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ');
        
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                due_date DATE,
                completed BOOLEAN DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE CASCADE
            )
        ');
        
        // Seed categories
        $pdo->exec("
            INSERT OR IGNORE INTO categories (id, name) VALUES
            (1, 'Work'),
            (2, 'Personal'),
            (3, 'Study')
        ");
        
    } catch (Exception $e) {
        // Log error but continue - database might already exist
        error_log('Database init error: ' . $e->getMessage());
    }
}

// Forward Vercel requests to the native Laravel public index file
require __DIR__ . '/../public/index.php';