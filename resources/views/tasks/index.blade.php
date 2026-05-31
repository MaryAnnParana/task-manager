<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Workspace - Matcha Edition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .animate-slide-in {
            animation: slideInUp 0.3s ease-out;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        
        button:active {
            transform: scale(0.98);
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .modal-animate {
            animation: modalSlideIn 0.3s ease-out;
        }
        
        .modal-backdrop {
            animation: fadeIn 0.2s ease-out;
        }
    </style>
    <script>
        function showDeleteConfirmation(event, taskId, taskTitle) {
            event.preventDefault();
            const modal = document.getElementById('deleteModal');
            const taskNameEl = document.getElementById('deleteTaskName');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            taskNameEl.textContent = taskTitle;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            confirmBtn.onclick = function() {
                document.getElementById(`deleteForm${taskId}`).submit();
            };
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });
        
        function sortTasks(sortType) {
            const pendingContainer = document.querySelector('[data-tasks-container="pending"]');
            if (!pendingContainer) return;
            
            const tasks = Array.from(pendingContainer.querySelectorAll('[data-task-item]'));
            
            tasks.sort((a, b) => {
                const aDeadline = new Date(a.getAttribute('data-deadline'));
                const bDeadline = new Date(b.getAttribute('data-deadline'));
                const aOverdue = a.getAttribute('data-overdue') === 'true';
                const bOverdue = b.getAttribute('data-overdue') === 'true';
                
                switch(sortType) {
                    case 'deadline-asc':
                        return aDeadline - bDeadline;
                    case 'deadline-desc':
                        return bDeadline - aDeadline;
                    case 'status-overdue':
                        return bOverdue - aOverdue;
                    default:
                        return 0;
                }
            });
            
            tasks.forEach(task => pendingContainer.appendChild(task));
        }
    </script>
    <script>
        // Custom Matcha Green Color Palette Extensions
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        matcha: {
                            50: '#f9faf8',
                            100: '#f0f3ed',
                            200: '#e1e9dd',
                            300: '#d2dfd1',
                            400: '#a8c6a9',
                            600: '#557c56',
                            700: '#436144',
                            800: '#364d37',
                            900: '#1f2d20',
                        }
                    },
                    boxShadow: {
                        'sm-elevated': '0 2px 8px rgba(0, 0, 0, 0.04)',
                        'card': '0 4px 16px rgba(0, 0, 0, 0.05)',
                        'hover': '0 8px 24px rgba(0, 0, 0, 0.08)',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-matcha-300 via-matcha-200 to-matcha-400 min-h-screen font-sans antialiased py-8 px-4 md:px-8">

    <div class="max-w-6xl mx-auto">
        <header class="mb-12 animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 mb-8">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-matcha-900 tracking-tight flex items-center gap-3 mb-2">
                        📋 Task Workspace
                    </h1>
                    <p class="text-base text-matcha-600/80 font-300">Organize your studies, projects, and objectives in one place.</p>
                </div>
                
                <form action="{{ route('tasks.index') }}" method="GET" class="w-full md:w-auto flex items-center gap-2">
                    <div class="relative w-full md:w-56">
                        <select name="category_id" onchange="this.form.submit()" class="w-full bg-white border-2 border-matcha-200 text-matcha-800 rounded-xl pl-4 pr-10 py-3 text-sm font-500 shadow-sm-elevated appearance-none focus:outline-none focus:ring-2 focus:ring-matcha-600 focus:border-matcha-600 focus:shadow-hover transition-all duration-200 hover:border-matcha-300">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    📁 {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-matcha-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </div>
                    </div>
                    
                    <div class="relative w-full md:w-48">
                        <select id="sortFilter" onchange="sortTasks(this.value)" class="w-full bg-white border-2 border-matcha-200 text-matcha-800 rounded-xl pl-4 pr-10 py-3 text-sm font-500 shadow-sm-elevated appearance-none focus:outline-none focus:ring-2 focus:ring-matcha-600 focus:border-matcha-600 focus:shadow-hover transition-all duration-200 hover:border-matcha-300">
                            <option value="">Sort Tasks</option>
                            <option value="deadline-asc">📅 Deadline (Soonest)</option>
                            <option value="deadline-desc">📅 Deadline (Latest)</option>
                            <option value="status-overdue">⚠️ Overdue First</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-matcha-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </div>
                    </div>
                    
                    @if(request('category_id'))
                        <a href="{{ route('tasks.index') }}" class="text-xs font-600 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-2.5 rounded-lg transition-all">✕ Clear</a>
                    @endif
                </form>
            </div>
        </header>

        @if(session('success'))
            <div class="bg-white border-l-4 border-emerald-500 text-emerald-900 p-5 rounded-xl mb-10 shadow-card flex items-center gap-4 transition-all animate-slide-in">
                <div class="bg-emerald-100 text-emerald-600 rounded-full p-2 text-sm font-bold flex-shrink-0">✓</div>
                <p class="text-sm font-500">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1 bg-white border-2 border-matcha-100 rounded-2xl p-7 shadow-card h-fit sticky top-8 animate-slide-in">
                <h2 class="text-xl font-bold text-matcha-900 mb-6 pb-3 border-b-2 border-matcha-100">Create Task</h2>
                
                <form action="{{ route('tasks.store') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-matcha-700 mb-2">Task Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g., Complete project report" class="w-full bg-matcha-50/50 border-2 @error('title') border-rose-400 bg-rose-50/50 @else border-matcha-200 @enderror rounded-xl px-4 py-3 text-sm font-400 shadow-sm-elevated focus:outline-none focus:ring-2 focus:ring-matcha-600 focus:border-matcha-600 focus:bg-white transition-all duration-200 placeholder:text-matcha-400">
                        @error('title') <p class="text-xs text-rose-600 mt-1.5 font-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-matcha-700 mb-2">Category *</label>
                        <select name="category_id" class="w-full bg-matcha-50/50 border-2 @error('category_id') border-rose-400 bg-rose-50/50 @else border-matcha-200 @enderror rounded-xl px-4 py-3 text-sm font-400 shadow-sm-elevated focus:outline-none focus:ring-2 focus:ring-matcha-600 focus:border-matcha-600 focus:bg-white transition-all duration-200">
                            <option value="" disabled selected class="text-matcha-400">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-xs text-rose-600 mt-1.5 font-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-matcha-700 mb-2">Due Date *</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full bg-matcha-50/50 border-2 @error('due_date') border-rose-400 bg-rose-50/50 @else border-matcha-200 @enderror rounded-xl px-4 py-3 text-sm font-400 shadow-sm-elevated focus:outline-none focus:ring-2 focus:ring-matcha-600 focus:border-matcha-600 focus:bg-white transition-all duration-200">
                        @error('due_date') <p class="text-xs text-rose-600 mt-1.5 font-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-matcha-700 mb-2">Description</label>
                        <textarea name="description" rows="3" placeholder="Add notes, requirements, or details..." class="w-full bg-matcha-50/50 border-2 border-matcha-200 rounded-xl px-4 py-3 text-sm font-400 shadow-sm-elevated focus:outline-none focus:ring-2 focus:ring-matcha-600 focus:border-matcha-600 focus:bg-white transition-all duration-200 placeholder:text-matcha-400 resize-none">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-matcha-600 to-matcha-700 hover:from-matcha-700 hover:to-matcha-800 text-white font-600 py-3 rounded-xl text-sm transition-all duration-300 shadow-sm hover:shadow-hover active:shadow-sm mt-2">
                        + Add Objective
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white border-2 border-matcha-100 rounded-2xl p-7 shadow-card animate-slide-in">
                    <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-matcha-100">
                        <h2 class="text-xl font-bold text-matcha-900 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-amber-400 animate-pulse shadow-sm"></span> Pending Actions
                        </h2>
                        <span class="bg-amber-50 text-amber-700 text-xs font-bold px-4 py-2 rounded-full border border-amber-200">{{ $pendingTasks->count() }} remaining</span>
                    </div>

                    @if($pendingTasks->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-base text-matcha-600/60 italic font-400">No pending items in your workspace.</p>
                        </div>
                    @else
                        <div class="space-y-4" data-tasks-container="pending">
                            @foreach($pendingTasks as $task)
                                @php
                                    $isOverdue = \Carbon\Carbon::parse($task->due_date)->isPast() && !\Carbon\Carbon::parse($task->due_date)->isToday();
                                @endphp
                                <div 
                                    data-task-item 
                                    data-deadline="{{ $task->due_date }}" 
                                    data-overdue="{{ $isOverdue ? 'true' : 'false' }}"
                                    class="p-5 rounded-xl border-2 transition-all flex flex-col sm:flex-row sm:items-start justify-between gap-4 {{ $isOverdue ? 'bg-rose-50/60 border-rose-200 shadow-sm hover:shadow-hover hover:border-rose-300' : 'bg-white border-matcha-200 shadow-sm hover:shadow-hover hover:border-matcha-300' }}">
                                    <div class="space-y-2.5 flex-1">
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <h3 class="font-600 text-matcha-900 text-base tracking-tight">{{ $task->title }}</h3>
                                            <span class="bg-matcha-100 text-matcha-700 text-[11px] font-bold px-3 py-1 rounded-full border border-matcha-200 tracking-wider">
                                                {{ $task->category->name }}
                                            </span>
                                        </div>
                                        @if($task->description)
                                            <p class="text-sm text-matcha-700/70 max-w-lg leading-relaxed font-400">{{ $task->description }}</p>
                                        @endif
                                        <p class="text-sm font-500 flex items-center gap-2 {{ $isOverdue ? 'text-rose-600' : 'text-matcha-600' }}">
                                            📅 {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }} 
                                            @if($isOverdue) 
                                                <span class="inline-flex items-center gap-1 bg-rose-600 text-white px-2.5 py-0.5 font-bold text-[10px] rounded-full tracking-widest">⚠ Overdue</span> 
                                            @else
                                                <span class="text-matcha-500 text-xs font-500">({{ \Carbon\Carbon::parse($task->due_date)->diffForHumans() }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center gap-2.5 self-end sm:self-start flex-shrink-0">
                                        <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 hover:text-emerald-800 border-2 border-emerald-200 hover:border-emerald-300 px-4 py-2.5 rounded-lg text-xs font-bold shadow-sm transition-all duration-200 active:scale-95">
                                                ✓ Complete
                                            </button>
                                        </form>
                                        
                                        <form id="deleteForm{{ $task->id }}" action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="showDeleteConfirmation(event, '{{ $task->id }}', '{{ $task->title }}')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 border-2 border-rose-200 hover:border-rose-300 px-4 py-2.5 rounded-lg text-xs font-bold shadow-sm transition-all duration-200 active:scale-95">
                                                🗑 Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white border-2 border-matcha-100 rounded-2xl p-7 shadow-card">
                    <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-matcha-100">
                        <h2 class="text-xl font-bold text-matcha-900 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-matcha-600"></span> Completed Objectives
                        </h2>
                        <span class="bg-matcha-100 text-matcha-700 text-xs font-bold px-4 py-2 rounded-full border border-matcha-200">{{ $completedTasks->count() }} completed</span>
                    </div>

                    @if($completedTasks->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-base text-matcha-600/60 italic font-400">No completed objectives yet. Start by completing some tasks!</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($completedTasks as $task)
                                <div class="p-5 rounded-xl border-2 border-matcha-150 bg-matcha-50/40 flex flex-col sm:flex-row sm:items-start justify-between gap-4 hover:bg-matcha-50/60 hover:shadow-sm transition-all duration-200">
                                    <div class="space-y-2 flex-1">
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <h3 class="font-500 text-matcha-600 text-sm line-through decoration-matcha-400">{{ $task->title }}</h3>
                                            <span class="bg-matcha-100/70 text-matcha-600/70 text-[11px] font-bold px-3 py-1 rounded-full border border-matcha-200/50 tracking-wider">
                                                {{ $task->category->name }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-emerald-600 font-600 flex items-center gap-1.5">✨ Completed objective</p>
                                    </div>
                                    
                                    <div class="flex items-center gap-2.5 self-end sm:self-start flex-shrink-0">
                                        <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-white border-2 border-matcha-200 text-matcha-600 hover:text-matcha-700 hover:bg-matcha-50 hover:border-matcha-300 px-3 py-2 rounded-lg text-xs font-bold transition-all duration-200 active:scale-95">
                                                ↶ Revert
                                            </button>
                                        </form>
                                        
                                        <form id="deleteForm{{ $task->id }}" action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="showDeleteConfirmation(event, '{{ $task->id }}', '{{ $task->title }}')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 border-2 border-rose-200 hover:border-rose-300 px-4 py-2.5 rounded-lg text-xs font-bold shadow-sm transition-all duration-200 active:scale-95">
                                                🗑 Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 modal-backdrop p-4">
        <div class="bg-white rounded-2xl shadow-hover max-w-sm w-full p-7 modal-animate">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-rose-100 mb-5 mx-auto">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            
            <h3 class="text-xl font-bold text-matcha-900 text-center mb-2">Delete Task?</h3>
            <p class="text-sm text-matcha-600 text-center mb-6">Are you sure you want to permanently delete <span id="deleteTaskName" class="font-semibold text-rose-600">this task</span>? This action cannot be undone.</p>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-matcha-50 hover:bg-matcha-100 text-matcha-700 hover:text-matcha-800 border-2 border-matcha-200 font-bold py-2.5 px-4 rounded-lg transition-all duration-200">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteBtn" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-4 rounded-lg transition-all duration-200 active:scale-95">
                    Delete
                </button>
            </div>
        </div>
    </div>

</body>
</html>