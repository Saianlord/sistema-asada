<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Proyectos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
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

            @hasanyrole('admin|administration')
            <div class="flex justify-end mb-6">
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Registrar Iniciativa
                </a>
            </div>
            @endhasanyrole

            <div class="bg-white p-6 shadow-sm rounded-lg border border-slate-200 mb-6">
                <form method="GET" action="{{ route('projects.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <x-input-label for="status" value="Estado" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700 text-sm">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Aprobado</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrado</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="criticality" value="Criticidad" />
                        <select id="criticality" name="criticality" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700 text-sm">
                            <option value="">Todas</option>
                            <option value="low" {{ request('criticality') === 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ request('criticality') === 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ request('criticality') === 'high' ? 'selected' : '' }}>Alta</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="priority" value="Prioridad" />
                        <select id="priority" name="priority" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700 text-sm">
                            <option value="">Todas</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                        </select>
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            Filtrar
                        </button>
                        @if(request()->anyFilled(['status', 'criticality', 'priority']))
                            <a href="{{ route('projects.index') }}" class="inline-flex justify-center items-center px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Título</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proponente</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Criticidad</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Prioridad</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider pr-8">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($projects as $project)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $project->title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ $project->user->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                        @if ($project->criticality === 'low')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Baja</span>
                                        @elseif ($project->criticality === 'medium')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Media</span>
                                        @elseif ($project->criticality === 'high')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Alta</span>
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
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
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
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-right font-medium pr-8">
                                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900">
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">
                                        @if(request()->anyFilled(['status', 'criticality', 'priority']))
                                            No se encontraron proyectos que coincidan con los filtros seleccionados.
                                        @else
                                            No hay iniciativas de proyectos registradas.
                                        @endif
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
