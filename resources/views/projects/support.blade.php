<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ $project->evidence_path ? 'Editar Información de Respaldo' : 'Adjuntar Información de Respaldo' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a los detalles del proyecto
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-8">
                <form method="POST" action="{{ route('projects.support.update', $project) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="technical_justification" value="Justificación Técnica" />
                        <textarea id="technical_justification" name="technical_justification" rows="5" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700" required>{{ old('technical_justification', $project->technical_justification) }}</textarea>
                        <x-input-error :messages="$errors->get('technical_justification')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="estimated_cost" value="Costo Estimado" />
                        <x-text-input id="estimated_cost" class="block mt-1 w-full" type="number" step="0.01" name="estimated_cost" :value="old('estimated_cost', $project->estimated_cost)" required />
                        <x-input-error :messages="$errors->get('estimated_cost')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="impact" value="Impacto" />
                        <textarea id="impact" name="impact" rows="3" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700" required>{{ old('impact', $project->impact) }}</textarea>
                        <x-input-error :messages="$errors->get('impact')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="risk" value="Riesgo" />
                        <textarea id="risk" name="risk" rows="3" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700" required>{{ old('risk', $project->risk) }}</textarea>
                        <x-input-error :messages="$errors->get('risk')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="evidence" value="Evidencia Inicial" />
                        <input id="evidence" type="file" name="evidence" class="block mt-1 w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" {{ $project->evidence_path ? '' : 'required' }} />
                        <p class="text-xs text-slate-500 mt-1">Formatos permitidos: PDF, PNG, JPG, JPEG (máximo 10 MB).</p>
                        @if ($project->evidence_path)
                            <div class="mt-3 flex items-center p-3 bg-slate-50 border border-slate-100 rounded-md">
                                <svg class="h-5 w-5 text-slate-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <a href="{{ route('projects.evidence.download', $project) }}" target="_blank" class="text-sm font-medium text-blue-600 hover:text-blue-800 underline">
                                    Ver Evidencia Actual
                                </a>
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('evidence')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-4">
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white">
                            {{ $project->evidence_path ? 'Actualizar Información' : 'Guardar Información' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
