<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Historial de Proyecto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-slate-500">Proyecto: {{ $project->title }}</p>
                    <h1 class="text-2xl font-bold text-slate-900">Historial de cambios y aprobaciones</h1>
                </div>
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                    Volver al proyecto
                </a>
            </div>

            @if ($histories->isEmpty())
                <div class="bg-white p-8 rounded-lg border border-slate-200 shadow-sm">
                    <p class="text-slate-600">No hay información disponible para este proyecto.</p>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Descripción</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($histories as $history)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            @if ($history->action_type === 'project_updated')
                                                Modificación
                                            @elseif ($history->action_type === 'support_updated')
                                                Actualización de respaldo
                                            @elseif ($history->action_type === 'project_approved')
                                                Aprobación
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $history->action_type)) }}
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">{{ $history->user->name }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-700 truncate max-w-xl">{{ $history->title }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium pr-8">
                                            <a href="{{ route('projects.history.show', [$project, $history]) }}" class="text-blue-600 hover:text-blue-900">Ver detalle</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
