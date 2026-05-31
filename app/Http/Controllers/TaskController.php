<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // 1. Dashboard Workspace (Index View) [cite: 17]
    public function index(Request $request)
    {
        // Initialize database tables if they don't exist
        $this->initializeDatabase();
        
        // Fetch all categories for our creation dropdown form [cite: 18, 20]
        $categories = Category::all();

        // Start a query builder for tasks to allow flexible filtering later [cite: 14]
        $query = Task::with('category')->orderBy('due_date', 'asc');

        // Optional request queries: Filter by Category if selected [cite: 14]
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // Get all tasks matching our criteria
        $allTasks = $query->get();

        // Split tasks cleanly by their status into two separate collections [cite: 17]
        $pendingTasks = $allTasks->where('status', 'Pending');
        $completedTasks = $allTasks->where('status', 'Completed');

        return view('tasks.index', compact('pendingTasks', 'completedTasks', 'categories'));
    }

    // 2. Task Management (Store Task CRUD) [cite: 19, 20]
    public function store(Request $request)
    {
        // Strict Input Validation [cite: 23, 38]
        $validated = $request->validate([
            'title' => 'required|string|max:255', // Title is mandatory [cite: 23]
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id', // Must be a valid category ID [cite: 23]
            'due_date' => 'required|date|after_or_equal:today', // Date cannot be set in the past [cite: 23, 38]
        ], [
            // Custom friendly error messages [cite: 40]
            'due_date.after_or_equal' => 'The deadline date cannot be set in the past!',
        ]);

        // Save into database [cite: 19]
        Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'due_date' => $validated['due_date'],
            'status' => 'Pending', // Hardcoded initial state [cite: 11]
        ]);

        // Return to dashboard with a flash success notification message [cite: 40]
        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    // 3. Single-Click State Toggle Action Button [cite: 11, 21, 36]
    public function toggleStatus(Task $task)
    {
        // Dynamically switch data states back and forth [cite: 11, 21]
        if ($task->status === 'Pending') {
            $task->status = 'Completed';
        } else {
            $task->status = 'Pending';
        }
        
        $task->save();

        return redirect()->route('tasks.index')->with('success', 'Task status updated!');
    }

    // 4. Permanent Deletion CRUD [cite: 19, 22]
    public function destroy(Task $task)
    {
        $task->delete(); // Permanently remove [cite: 22]

        return redirect()->route('tasks.index')->with('success', 'Task deleted permanently!');
    }

    // Initialize database tables on first request (for Vercel deployment)
    private function initializeDatabase()
    {
        try {
            // Use absolute path that works on both local and Vercel
            // Local: database/database.sqlite
            // Vercel: /var/task/user/storage/app/database.sqlite
            $db_path = env('DB_DATABASE');
            
            // Ensure directory exists
            $db_dir = dirname($db_path);
            if (!is_dir($db_dir)) {
                @mkdir($db_dir, 0755, true);
            }
            
            // Create database if it doesn't exist
            if (!file_exists($db_path)) {
                $pdo = new \PDO("sqlite:$db_path");
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->exec('PRAGMA foreign_keys = ON;');
                
                // Create categories table
                $pdo->exec('
                    CREATE TABLE IF NOT EXISTS categories (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ');
                
                // Create tasks table
                $pdo->exec('
                    CREATE TABLE IF NOT EXISTS tasks (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        category_id INTEGER NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        description TEXT,
                        due_date DATE,
                        completed BOOLEAN DEFAULT 0,
                        status VARCHAR(50) DEFAULT "Pending",
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
            }
        } catch (\Exception $e) {
            // Log but don't fail
            \Log::debug('Database init: ' . $e->getMessage());
        }
    }
}