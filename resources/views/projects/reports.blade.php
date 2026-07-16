<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Reporte de Proyectos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Reporte de Proyectos</h3>
                        <p class="text-sm text-slate-500 mt-1">Filtre por estado, prioridad y criticidad para apoyar reuniones y rendición de cuentas.</p>
                    </div>
                    <form method="GET" action="{{ route('projects.reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full lg:w-auto">
                        <div>
                            <x-input-label for="status" value="Estado" />
                            <select id="status" name="status" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-slate-700">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Aprobado</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrado</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="criticality" value="Criticidad" />
                            <select id="criticality" name="criticality" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-slate-700">
                                <option value="">Todas</option>
                                <option value="low" {{ request('criticality') === 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ request('criticality') === 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ request('criticality') === 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="priority" value="Prioridad" />
                            <select id="priority" name="priority" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-slate-700">
                                <option value="">Todas</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                Generar reporte
                            </button>
                            @if(request()->anyFilled(['status', 'criticality', 'priority']))
                                <a href="{{ route('projects.reports.index') }}" class="inline-flex justify-center items-center px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm text-slate-500">Total de proyectos</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $summary['total'] }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm text-slate-500">Aprobados</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $summary['approved'] }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm text-slate-500">Pendientes</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ $summary['pending'] }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proyecto</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proponente</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Prioridad</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Criticidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($projects as $project)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $project->title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ $project->user->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                        @if ($project->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendiente</span>
                                        @elseif ($project->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                                        @elseif ($project->status === 'closed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Cerrado</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $project->status }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                        @if ($project->priority === 'low')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Baja</span>
                                        @elseif ($project->priority === 'medium')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Media</span>
                                        @elseif ($project->priority === 'high')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Alta</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                        @if ($project->criticality === 'low')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Baja</span>
                                        @elseif ($project->criticality === 'medium')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Media</span>
                                        @elseif ($project->criticality === 'high')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Alta</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">
                                        No hay proyectos para mostrar con los filtros seleccionados.
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
