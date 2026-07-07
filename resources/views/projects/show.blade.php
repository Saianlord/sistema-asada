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
                <div class="flex items-center gap-3">
                    @hasanyrole('admin|junta')
                    @if ($project->status === 'pending')
                        @php
                            $userEvaluation = $project->evaluations->where('user_id', auth()->id())->first();
                        @endphp
                        @if ($userEvaluation)
                            <a href="{{ route('evaluations.edit', [$project, $userEvaluation]) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar Mi Evaluación
                            </a>
                        @else
                            <a href="{{ route('evaluations.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                Evaluar Proyecto
                            </a>
                        @endif
                    @endif
                    @if ($project->status === 'pending')
                        <form method="POST" action="{{ route('projects.approve', $project) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors" onclick="return confirm('¿Estás seguro de que deseas aprobar este proyecto?');">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Aprobar Proyecto
                            </button>
                        </form>
                        <form method="POST" action="{{ route('projects.reject', $project) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors" onclick="return confirm('¿Estás seguro de que deseas rechazar este proyecto?');">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Rechazar Proyecto
                            </button>
                        </form>
                    @endif
                    @endhasanyrole
                    @hasanyrole('admin|administration')
                    @if (!in_array($project->status, ['approved', 'rejected', 'closed']))
                        <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar Iniciativa
                        </a>
                    @endif
                    @endhasanyrole
                    @if ($project->status === 'in_progress')
                        <a href="{{ route('projects.kanban.index', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Tablero Kanban
                        </a>
                    @endif
                    <a href="{{ route('projects.document-record.index', $project) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        Ver Expediente Documental
                    </a>
                </div>
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

            @if (session('info'))
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-sm font-medium">
                    {{ session('info') }}
                </div>
            @endif

            @hasrole('junta')
                @if (is_null($project->estimated_cost) || $project->estimated_cost <= 0)
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-medium">
                        No se encontró presupuesto asignado para esta iniciativa. No será posible aprobarla hasta que se asigne presupuesto.
                    </div>
                @endif
            @endhasrole

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
                                Registrado
                            </span>
                        @elseif ($project->status === 'en_analisis')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800">
                                En análisis
                            </span>
                        @elseif ($project->status === 'prioritized')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                Priorizado
                            </span>
                        @elseif ($project->status === 'approved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                Aprobado
                            </span>
                        @elseif ($project->status === 'in_progress')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                En ejecución
                            </span>
                        @elseif ($project->status === 'paused')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800">
                                Pausado
                            </span>
                        @elseif ($project->status === 'closed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-800">
                                Finalizado
                            </span>
                        @elseif ($project->status === 'rejected')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                Rechazado
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                {{ $project->status_label }}
                            </span>
                        @endif
                    </div>
                </div>

                @if ($project->status !== 'closed' && (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id))
                    <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-lg shadow-sm">
                        <form method="POST" action="{{ route('projects.status.update', $project) }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
                            @csrf
                            @method('PATCH')
                            <div class="flex-1">
                                <label for="status" class="block text-sm font-semibold text-slate-700 mb-1">Cambiar Estado del Proyecto</label>
                                <select id="status" name="status" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700 text-sm">
                                    <option value="pending" {{ $project->status === 'pending' ? 'selected' : '' }}>Registrado</option>
                                    <option value="en_analisis" {{ $project->status === 'en_analisis' ? 'selected' : '' }}>En análisis</option>
                                    <option value="prioritized" {{ $project->status === 'prioritized' ? 'selected' : '' }}>Priorizado</option>
                                    <option value="approved" {{ $project->status === 'approved' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>En ejecución</option>
                                    <option value="paused" {{ $project->status === 'paused' ? 'selected' : '' }}>Pausado</option>
                                    <option value="closed" {{ $project->status === 'closed' ? 'selected' : '' }}>Finalizado</option>
                                </select>
                            </div>
                            <div class="sm:self-end">
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    Actualizar Estado
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                @if ($project->approval_agreement)
                    <div class="mt-4 p-4 bg-white border border-slate-200 rounded-lg shadow-sm">
                        <h4 class="text-sm font-semibold text-slate-900">Registro de Aprobación</h4>
                        <p class="text-sm text-slate-600 mt-2">Acuerdo: <span class="font-medium text-slate-800">{{ $project->approval_agreement }}</span></p>
                        <p class="text-sm text-slate-600">Fecha: <span class="font-medium text-slate-800">{{ optional($project->approval_date)->format('d/m/Y') }}</span></p>
                        <p class="text-sm text-slate-600">Responsable: <span class="font-medium text-slate-800">{{ $project->approval_responsible }}</span></p>
                        <div class="mt-2 text-sm text-slate-700 bg-slate-50 p-3 rounded-md border border-slate-100">{{ $project->approval_justification }}</div>
                    </div>
                @elseif ($project->status === 'approved')
                    @hasanyrole('admin|administration')
                        <div class="mt-4">
                            <a href="{{ route('projects.approval.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">Registrar Acuerdo</a>
                        </div>
                    @endhasanyrole
                @endif

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

                    <div class="pt-6 border-t border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Evaluaciones</h3>

                        @if ($project->evaluations->count() > 0)
                            <div class="space-y-4">
                                @foreach ($project->evaluations as $evaluation)
                                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
                                        <div class="flex justify-between items-center mb-3">
                                            <p class="text-sm font-semibold text-slate-800">{{ $evaluation->user->name }}</p>
                                            <div class="flex items-center gap-3">
                                                @if ($evaluation->viability_status === 'viable')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Viable</span>
                                                @elseif ($evaluation->viability_status === 'conditional')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Condicional</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">No Viable</span>
                                                @endif
                                                <span class="text-sm font-bold text-slate-900">{{ number_format($evaluation->average_score, 2) }}/10</span>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-4 gap-4">
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 uppercase tracking-wider">Técnico</p>
                                                <p class="text-lg font-bold text-slate-900">{{ $evaluation->technical_score }}</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 uppercase tracking-wider">Financiero</p>
                                                <p class="text-lg font-bold text-slate-900">{{ $evaluation->financial_score }}</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 uppercase tracking-wider">Operativo</p>
                                                <p class="text-lg font-bold text-slate-900">{{ $evaluation->operational_score }}</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 uppercase tracking-wider">Normativo</p>
                                                <p class="text-lg font-bold text-slate-900">{{ $evaluation->regulatory_score }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if ($project->evaluations->count() > 0)
                                    <div class="bg-white border-2 border-blue-200 rounded-lg p-5 mt-4">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 uppercase tracking-wider">Dictamen General de Viabilidad</p>
                                                <p class="text-xs text-slate-500 mt-1">Basado en {{ $project->evaluations->count() }} evaluación(es)</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                @if ($project->viability_label === 'viable')
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">Viable</span>
                                                @elseif ($project->viability_label === 'conditional')
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">Condicional</span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">No Viable</span>
                                                @endif
                                                <span class="text-xl font-bold text-blue-700">{{ number_format($project->average_viability_score, 2) }}/10</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center p-6 bg-slate-50 border border-dashed border-slate-300 rounded-lg">
                                <p class="text-sm text-slate-600">Este proyecto aún no cuenta con evaluaciones registradas.</p>
                                <p class="text-xs text-slate-400 mt-1">No es posible generar un dictamen de viabilidad sin evaluaciones.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-8 mt-6">
                <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Seguimiento del Proyecto</h3>
                    @if ($project->status === 'in_progress' && (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id))
                        <a href="{{ route('projects.tracking.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            Registrar Seguimiento
                        </a>
                    @endif
                </div>

                @if ($project->trackings->count() > 0)
                    <div class="space-y-6">
                        @foreach ($project->trackings as $tracking)
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $badgeClasses = [
                                                'milestone' => 'bg-purple-100 text-purple-800',
                                                'progress' => 'bg-green-100 text-green-800',
                                                'incident' => 'bg-red-100 text-red-800',
                                            ][$tracking->type] ?? 'bg-slate-100 text-slate-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                                            {{ $tracking->type_label }}
                                        </span>
                                        <h4 class="text-sm font-bold text-slate-900">{{ $tracking->title }}</h4>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="text-xs text-slate-500 font-medium">{{ \Carbon\Carbon::parse($tracking->date)->format('d/m/Y') }}</span>
                                        @if ($project->status === 'in_progress' && (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id))
                                            <a href="{{ route('projects.tracking.edit', [$project, $tracking]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-900 transition-colors">
                                                Editar
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $tracking->description }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-6">No hay registros de seguimiento para este proyecto.</p>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-8 mt-6">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Documentos del Proyecto</h3>
                </div>

                @if ($project->status === 'in_progress' && (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id))
                    <form method="POST" action="{{ route('projects.documents.store', $project) }}" enctype="multipart/form-data" class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                        @csrf
                        <div class="flex flex-col sm:flex-row items-end gap-4">
                            <div class="flex-1 w-full">
                                <label for="document" class="block text-sm font-medium text-slate-700 mb-1">Adjuntar archivo (PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX - Máx. 10MB)</label>
                                <input type="file" name="document" id="document" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                            </div>
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                Adjuntar Documento
                            </button>
                        </div>
                        @error('document')
                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </form>
                @endif

                @if ($project->documents->count() > 0)
                    <div class="space-y-4">
                        @foreach ($project->documents as $document)
                            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="hover:underline">
                                                {{ $document->original_name }}
                                            </a>
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            <span>Formato: {{ strtoupper($document->file_type) }}</span>
                                            <span class="mx-1.5">&bull;</span>
                                            <span>Subido por: {{ $document->uploadedBy->name }}</span>
                                            <span class="mx-1.5">&bull;</span>
                                            <span>Fecha: {{ $document->created_at->format('d/m/Y H:i') }}</span>
                                        </p>
                                    </div>
                                </div>
                                @if ($project->status === 'in_progress' && (auth()->user()->hasAnyRole(['admin', 'administration']) || auth()->id() === $project->user_id))
                                    <form method="POST" action="{{ route('projects.documents.destroy', [$project, $document]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-900 transition-colors">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-6">No hay documentos adjuntos para este proyecto.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
