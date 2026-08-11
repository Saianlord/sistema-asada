<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Historial del Proyecto: {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-6 bg-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al proyecto
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-6">Línea de tiempo de cambios y aprobaciones</h3>
                    
                    @if($auditLogs->isEmpty())
                        <div class="text-center py-8 text-slate-500 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                            <p>No hay información registrada en el historial.</p>
                        </div>
                    @else
                        <div class="space-y-8">
                            @foreach($auditLogs as $log)
                                <div class="relative pl-8 sm:pl-32 py-2 group">
                                    <div class="font-medium text-2xl text-indigo-500 mb-1 sm:mb-0 sm:hidden">
                                        {{ $log->created_at->format('d M, Y') }}
                                    </div>
                                    <div class="flex flex-col sm:flex-row items-start mb-1 group-last:before:hidden before:absolute before:left-2 sm:before:left-0 before:h-full before:px-px before:bg-slate-200 sm:before:ml-[6.5rem] before:self-start before:-translate-x-1/2 before:translate-y-3 after:absolute after:left-2 sm:after:left-0 after:w-2 after:h-2 after:bg-indigo-600 after:border-4 after:box-content after:border-slate-50 after:rounded-full sm:after:ml-[6.5rem] after:-translate-x-1/2 after:translate-y-1.5">
                                        <time class="sm:absolute left-0 translate-y-0.5 inline-flex items-center justify-center text-xs font-semibold uppercase w-20 h-6 mb-3 sm:mb-0 text-indigo-600 bg-indigo-100 rounded-full">
                                            {{ $log->created_at->format('d/m/Y') }}
                                        </time>
                                        <div class="text-sm font-medium text-slate-900">
                                            <span class="text-indigo-600">{{ $log->user ? $log->user->name : 'Sistema' }}</span>
                                            <span class="text-slate-500">
                                                @switch($log->action_type)
                                                    @case('created')
                                                        creó este registro.
                                                        @break
                                                    @case('updated')
                                                        editó la información.
                                                        @break
                                                    @case('status_changed')
                                                        cambió el estado.
                                                        @break
                                                    @case('deleted')
                                                        eliminó este registro.
                                                        @break
                                                    @default
                                                        realizó una acción: {{ $log->action_type }}.
                                                @endswitch
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($log->old_values || $log->new_values)
                                        <div class="text-sm text-slate-500 mt-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                                            @if($log->action_type === 'status_changed')
                                                <p>
                                                    <span class="font-semibold text-slate-700">Estado anterior:</span> 
                                                    <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded-md text-xs font-medium">{{ $log->old_values['status'] ?? 'N/A' }}</span>
                                                    <span class="mx-2">&rarr;</span>
                                                    <span class="font-semibold text-slate-700">Nuevo estado:</span> 
                                                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-md text-xs font-medium">{{ $log->new_values['status'] ?? 'N/A' }}</span>
                                                </p>
                                            @elseif($log->action_type === 'updated')
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div>
                                                        <h4 class="font-semibold text-xs text-slate-400 uppercase tracking-wider mb-2">Valores Anteriores</h4>
                                                        <ul class="space-y-1">
                                                            @foreach($log->old_values as $key => $value)
                                                                <li class="break-all"><span class="font-medium text-slate-600">{{ $key }}:</span> {{ Str::limit($value, 50) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-xs text-indigo-400 uppercase tracking-wider mb-2">Nuevos Valores</h4>
                                                        <ul class="space-y-1">
                                                            @foreach($log->new_values as $key => $value)
                                                                <li class="break-all"><span class="font-medium text-slate-600">{{ $key }}:</span> {{ Str::limit($value, 50) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
