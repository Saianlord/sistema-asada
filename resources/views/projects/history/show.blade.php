<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Detalle del Historial
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Proyecto: {{ $project->title }}</p>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $history->title }}</h1>
                </div>
                <a href="{{ route('projects.history.index', $project) }}" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                    Volver al historial
                </a>
            </div>

            <div class="bg-white p-8 rounded-lg border border-slate-200 shadow-sm space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</p>
                        <p class="mt-2 text-slate-700">{{ $history->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</p>
                        <p class="mt-2 text-slate-700">{{ $history->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</p>
                        <p class="mt-2 text-slate-700">
                            @if ($history->action_type === 'project_updated')
                                Modificación
                            @elseif ($history->action_type === 'support_updated')
                                Actualización de respaldo
                            @elseif ($history->action_type === 'project_approved')
                                Aprobación
                            @else
                                {{ ucfirst(str_replace('_', ' ', $history->action_type)) }}
                            @endif
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Descripción</h3>
                    <div class="mt-2 p-4 bg-slate-50 rounded-lg border border-slate-100 text-slate-700 whitespace-pre-line">{{ $history->description }}</div>
                </div>

                @if ($history->details)
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Detalle</h3>
                        <div class="mt-2 p-4 bg-slate-50 rounded-lg border border-slate-100 text-slate-700 whitespace-pre-line">{{ $history->details }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
