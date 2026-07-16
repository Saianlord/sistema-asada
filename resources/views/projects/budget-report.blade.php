<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Reporte de Ejecución Presupuestaria
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900">Resumen de ejecución</h3>
                <p class="text-sm text-slate-500 mt-1">Este reporte permite monitorear el presupuesto registrado y el avance general de los proyectos.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm text-slate-500">Presupuesto total</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($totalBudget, 2) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm text-slate-500">Avance promedio</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($averageProgress, 2) }}%</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proyecto</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proponente</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Presupuesto</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Avance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($projects as $project)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $project->title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ $project->user->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ number_format($project->estimated_cost, 2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ $project->priority === 'high' ? '75%' : ($project->priority === 'medium' ? '50%' : '25%') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">
                                        No hay información disponible
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
