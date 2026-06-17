<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Evaluar Iniciativa
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
                <div class="mb-6 pb-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">{{ $project->title }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Registre su evaluación para este proyecto utilizando una escala de 1 a 10.</p>
                </div>

                <form method="POST" action="{{ route('evaluations.store', $project) }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="technical_score" value="Criterio Técnico (1-10)" />
                            <p class="text-xs text-slate-500 mt-1 mb-2">Evalúa la viabilidad técnica, complejidad y recursos tecnológicos necesarios.</p>
                            <x-text-input id="technical_score" name="technical_score" type="number" min="1" max="10" step="1" class="block w-full" :value="old('technical_score')" required />
                            <x-input-error :messages="$errors->get('technical_score')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="financial_score" value="Criterio Financiero (1-10)" />
                            <p class="text-xs text-slate-500 mt-1 mb-2">Evalúa el costo-beneficio, retorno de inversión y sostenibilidad financiera.</p>
                            <x-text-input id="financial_score" name="financial_score" type="number" min="1" max="10" step="1" class="block w-full" :value="old('financial_score')" required />
                            <x-input-error :messages="$errors->get('financial_score')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="operational_score" value="Criterio Operativo (1-10)" />
                            <p class="text-xs text-slate-500 mt-1 mb-2">Evalúa la capacidad operativa, logística y recursos humanos disponibles.</p>
                            <x-text-input id="operational_score" name="operational_score" type="number" min="1" max="10" step="1" class="block w-full" :value="old('operational_score')" required />
                            <x-input-error :messages="$errors->get('operational_score')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="regulatory_score" value="Criterio Normativo (1-10)" />
                            <p class="text-xs text-slate-500 mt-1 mb-2">Evalúa el cumplimiento normativo, regulaciones y requisitos legales.</p>
                            <x-text-input id="regulatory_score" name="regulatory_score" type="number" min="1" max="10" step="1" class="block w-full" :value="old('regulatory_score')" required />
                            <x-input-error :messages="$errors->get('regulatory_score')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-slate-100">
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-sm transition-colors">
                            GUARDAR EVALUACIÓN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
