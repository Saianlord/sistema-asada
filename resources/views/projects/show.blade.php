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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
