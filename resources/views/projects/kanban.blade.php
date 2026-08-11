<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Tablero Kanban: {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a los detalles del proyecto
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id)
                <div class="bg-white p-6 shadow-sm rounded-lg border border-slate-200 mb-8">
                    <h3 class="text-lg font-bold text-slate-950 mb-4">Nueva Tarea</h3>
                    <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <div class="md:col-span-2">
                            <x-input-label for="title" value="Título de la Tarea" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="due_date" value="Fecha Compromiso" />
                            <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date')" required />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="assigned_user_id" value="Responsable" />
                            <select id="assigned_user_id" name="assigned_user_id" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700 text-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_user_id')" class="mt-2" />
                        </div>

                        <div class="md:col-span-3">
                            <x-input-label for="description" value="Descripción (Opcional)" />
                            <textarea id="description" name="description" rows="2" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700 text-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                Registrar Tarea
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-100 p-4 rounded-lg border border-slate-200" data-testid="kanban-pending-column">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-200 pb-2">
                        <h4 class="font-bold text-slate-900 uppercase tracking-wider text-sm">Pendiente</h4>
                        <span class="bg-slate-200 text-slate-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $pendingTasks->count() }}</span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($pendingTasks as $task)
                            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                                <h5 class="font-semibold text-slate-950 text-base mb-1">{{ $task->title }}</h5>
                                @if ($task->description)
                                    <p class="text-sm text-slate-600 mb-3 whitespace-pre-wrap">{{ $task->description }}</p>
                                @endif
                                <div class="text-xs text-slate-500 space-y-1 mb-4">
                                    <p>Responsable: <span class="font-medium text-slate-700">{{ $task->assignedUser->name }}</span></p>
                                    <p>Fecha Compromiso: <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}</span></p>
                                </div>
                                @if (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id)
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                                        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="text-xs font-medium text-blue-600 hover:text-blue-900 transition-colors">Editar</a>
                                        <form method="POST" action="{{ route('projects.tasks.status.update', [$project, $task]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold rounded transition-colors">
                                                Iniciar
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 text-center py-6">No hay tareas pendientes.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-indigo-50/50 p-4 rounded-lg border border-indigo-100" data-testid="kanban-in-progress-column">
                    <div class="flex justify-between items-center mb-4 border-b border-indigo-100 pb-2">
                        <h4 class="font-bold text-indigo-900 uppercase tracking-wider text-sm">En progreso</h4>
                        <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $inProgressTasks->count() }}</span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($inProgressTasks as $task)
                            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                                <h5 class="font-semibold text-slate-950 text-base mb-1">{{ $task->title }}</h5>
                                @if ($task->description)
                                    <p class="text-sm text-slate-600 mb-3 whitespace-pre-wrap">{{ $task->description }}</p>
                                @endif
                                <div class="text-xs text-slate-500 space-y-1 mb-4">
                                    <p>Responsable: <span class="font-medium text-slate-700">{{ $task->assignedUser->name }}</span></p>
                                    <p>Fecha Compromiso: <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}</span></p>
                                </div>
                                @if (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id)
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-3 gap-2">
                                        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="text-xs font-medium text-blue-600 hover:text-blue-900 transition-colors">Editar</a>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('projects.tasks.status.update', [$project, $task]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="inline-flex items-center px-2 py-1 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold rounded transition-colors">
                                                    Detener
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('projects.tasks.status.update', [$project, $task]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="inline-flex items-center px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded transition-colors">
                                                    Completar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 text-center py-6">No hay tareas en progreso.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-green-50/50 p-4 rounded-lg border border-green-100" data-testid="kanban-completed-column">
                    <div class="flex justify-between items-center mb-4 border-b border-green-100 pb-2">
                        <h4 class="font-bold text-green-900 uppercase tracking-wider text-sm">Completada</h4>
                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $completedTasks->count() }}</span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($completedTasks as $task)
                            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                                <h5 class="font-semibold text-slate-950 text-base mb-1 line-through text-slate-500">{{ $task->title }}</h5>
                                @if ($task->description)
                                    <p class="text-sm text-slate-400 mb-3 line-through whitespace-pre-wrap">{{ $task->description }}</p>
                                @endif
                                <div class="text-xs text-slate-400 space-y-1 mb-4">
                                    <p>Responsable: <span>{{ $task->assignedUser->name }}</span></p>
                                    <p>Fecha Compromiso: <span>{{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}</span></p>
                                </div>
                                @if (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id)
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                                        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="text-xs font-medium text-blue-600 hover:text-blue-900 transition-colors">Editar</a>
                                        <form method="POST" action="{{ route('projects.tasks.status.update', [$project, $task]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold rounded transition-colors">
                                                Reabrir
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 text-center py-6">No hay tareas completadas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
