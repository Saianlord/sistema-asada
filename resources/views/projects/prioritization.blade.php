<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Priorización de Proyectos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-8 mb-6">
                <h3 class="text-xl font-bold text-slate-900">Ranking de Viabilidad</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Las iniciativas de proyectos se ordenan automáticamente de mayor a menor viabilidad con base en el promedio de todas las evaluaciones registradas.
                </p>
            </div>

            @if ($projects->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-16">Puesto</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proyecto</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proponente</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Evaluaciones</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Promedio de Viabilidad</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dictamen</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider pr-8">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($projects as $project)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-900">
                                            #{{ $loop->iteration }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                            {{ $project->title }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                            {{ $project->user->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                            {{ $project->evaluations->count() }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-blue-700">
                                            {{ number_format($project->average_viability_score, 2) }}/10
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            @if ($project->viability_label === 'viable')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    Viable
                                                </span>
                                            @elseif ($project->viability_label === 'conditional')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                    Condicional
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                    No Viable
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-right font-medium pr-8">
                                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900">
                                                Ver Detalles
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">No hay datos suficientes para generar la priorización</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">
                        Para generar el ranking, es necesario registrar al menos una evaluación en alguna iniciativa de proyecto.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
