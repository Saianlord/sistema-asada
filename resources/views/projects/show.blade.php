<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Detalles de la Iniciativa
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('projects.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al listado
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-8">
                <div class="flex justify-between items-start border-b border-slate-100 pb-6">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 uppercase tracking-wider mb-2">
                            Iniciativa
                        </span>
                        <h1 class="text-2xl font-bold text-slate-950">{{ $project->title }}</h1>
                        <p class="text-sm text-slate-500 mt-1">Registrado por {{ $project->user->name }} el {{ $project->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        @if ($project->status === 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                Pendiente
                            </span>
                        @elseif ($project->status === 'approved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                Aprobado
                            </span>
                        @elseif ($project->status === 'closed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-800">
                                Cerrado
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                {{ $project->status }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-8 space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Descripción</h3>
                        <div class="mt-2 text-slate-700 whitespace-pre-line text-base leading-relaxed bg-slate-50 p-4 rounded-lg border border-slate-100">
                            {{ $project->description }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-b border-slate-100 pb-6">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Criticidad</h3>
                            <div class="mt-2">
                                @if ($project->criticality === 'low')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800">Baja</span>
                                @elseif ($project->criticality === 'medium')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Media</span>
                                @elseif ($project->criticality === 'high')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Alta</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Prioridad</h3>
                            <div class="mt-2">
                                @if ($project->priority === 'low')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800">Baja</span>
                                @elseif ($project->priority === 'medium')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Media</span>
                                @elseif ($project->priority === 'high')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Alta</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-900">Información de Respaldo</h3>
                            @hasanyrole('admin|operations')
                            @if ($project->evidence_path)
                                <a href="{{ route('projects.support.edit', $project) }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-900 transition-colors">
                                    Editar Información
                                </a>
                            @endif
                            @endhasanyrole
                        </div>

                        @if ($project->evidence_path)
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Justificación Técnica</h4>
                                    <div class="mt-2 text-slate-700 whitespace-pre-line text-sm bg-slate-50 p-4 rounded-lg border border-slate-100">
                                        {{ $project->technical_justification }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Costo Estimado</h4>
                                        <p class="text-lg font-bold text-slate-900 mt-1">₡{{ number_format($project->estimated_cost, 2) }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Evidencia Inicial</h4>
                                        <div class="mt-2 flex items-center p-3 bg-slate-50 border border-slate-100 rounded-md">
                                            <svg class="h-5 w-5 text-slate-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <a href="{{ asset('storage/' . $project->evidence_path) }}" target="_blank" class="text-sm font-medium text-blue-600 hover:text-blue-800 underline">
                                                Ver Evidencia
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Impacto</h4>
                                        <div class="mt-2 text-slate-700 whitespace-pre-line text-sm bg-slate-50 p-4 rounded-lg border border-slate-100">
                                            {{ $project->impact }}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Riesgo</h4>
                                        <div class="mt-2 text-slate-700 whitespace-pre-line text-sm bg-slate-50 p-4 rounded-lg border border-slate-100">
                                            {{ $project->risk }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center p-6 bg-slate-50 border border-dashed border-slate-300 rounded-lg">
                                <p class="text-sm text-slate-600 mb-4">Esta iniciativa aún no cuenta con información de respaldo técnica.</p>
                                @hasanyrole('admin|operations')
                                <a href="{{ route('projects.support.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Adjuntar Información
                                </a>
                                @endhasanyrole
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
