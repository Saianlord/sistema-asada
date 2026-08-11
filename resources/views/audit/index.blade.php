<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Historial Global de Acciones Críticas
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-slate-200">
                    @if($auditLogs->isEmpty())
                        <div class="text-center py-8 text-slate-500">
                            No hay información disponible.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usuario</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acción</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Módulo / ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Detalles</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($auditLogs as $log)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                                {{ $log->user ? $log->user->name : 'Sistema' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                @switch($log->action_type)
                                                    @case('created')
                                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Creación</span>
                                                        @break
                                                    @case('updated')
                                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Actualización</span>
                                                        @break
                                                    @case('deleted')
                                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Eliminación</span>
                                                        @break
                                                    @case('status_changed')
                                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Cambio Estado</span>
                                                        @break
                                                    @default
                                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">{{ ucfirst($log->action_type) }}</span>
                                                @endswitch
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate" title="{{ json_encode(['old' => $log->old_values, 'new' => $log->new_values]) }}">
                                                @if($log->action_type === 'status_changed')
                                                    {{ $log->old_values['status'] ?? 'N/A' }} &rarr; {{ $log->new_values['status'] ?? 'N/A' }}
                                                @elseif($log->action_type === 'updated')
                                                    {{ collect($log->new_values)->keys()->implode(', ') }}
                                                @else
                                                    Registro de nueva entidad
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $auditLogs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
