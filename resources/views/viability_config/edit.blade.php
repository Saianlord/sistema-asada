<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Configuración del Modelo de Viabilidad
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <form method="POST" action="{{ route('viability-config.update') }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 border-b pb-2 mb-4">
                                Ponderación de Criterios (Suma total debe ser 100%)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <x-input-label for="technical_weight" value="Criterio Técnico (%)" />
                                    <x-text-input id="technical_weight" name="technical_weight" type="number" min="0" max="100" class="block mt-1 w-full weight-input" value="{{ old('technical_weight', $config->technical_weight) }}" required />
                                    <x-input-error :messages="$errors->get('technical_weight')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="financial_weight" value="Criterio Financiero (%)" />
                                    <x-text-input id="financial_weight" name="financial_weight" type="number" min="0" max="100" class="block mt-1 w-full weight-input" value="{{ old('financial_weight', $config->financial_weight) }}" required />
                                    <x-input-error :messages="$errors->get('financial_weight')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="operational_weight" value="Criterio Operativo (%)" />
                                    <x-text-input id="operational_weight" name="operational_weight" type="number" min="0" max="100" class="block mt-1 w-full weight-input" value="{{ old('operational_weight', $config->operational_weight) }}" required />
                                    <x-input-error :messages="$errors->get('operational_weight')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="regulatory_weight" value="Criterio Normativo (%)" />
                                    <x-text-input id="regulatory_weight" name="regulatory_weight" type="number" min="0" max="100" class="block mt-1 w-full weight-input" value="{{ old('regulatory_weight', $config->regulatory_weight) }}" required />
                                    <x-input-error :messages="$errors->get('regulatory_weight')" class="mt-2" />
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-slate-50 rounded-lg flex items-center justify-between border border-slate-100">
                                <span class="text-sm font-medium text-slate-600">Suma Total de Ponderaciones:</span>
                                <span id="total-weight" class="text-lg font-bold">0%</span>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 border-b pb-2 mb-4">
                                Umbrales de Viabilidad (Escala del 1.00 al 10.00)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="viable_threshold" value="Umbral Viable" />
                                    <x-text-input id="viable_threshold" name="viable_threshold" type="number" step="0.01" min="1" max="10" class="block mt-1 w-full" value="{{ old('viable_threshold', $config->viable_threshold) }}" required />
                                    <x-input-error :messages="$errors->get('viable_threshold')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="conditional_threshold" value="Umbral Condicional" />
                                    <x-text-input id="conditional_threshold" name="conditional_threshold" type="number" step="0.01" min="1" max="10" class="block mt-1 w-full" value="{{ old('conditional_threshold', $config->conditional_threshold) }}" required />
                                    <x-input-error :messages="$errors->get('conditional_threshold')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label value="Criterio No Viable (Calculado)" />
                                    <div class="mt-1 block w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-md text-slate-500 shadow-sm text-sm">
                                        Menor que <span id="not-viable-threshold-label">{{ old('conditional_threshold', $config->conditional_threshold) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 border-t pt-6">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Guardar Configuración
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('.weight-input');
            const totalSpan = document.getElementById('total-weight');
            const conditionalInput = document.getElementById('conditional_threshold');
            const notViableLabel = document.getElementById('not-viable-threshold-label');

            function updateTotal() {
                let sum = 0;
                inputs.forEach(input => {
                    sum += parseInt(input.value) || 0;
                });
                totalSpan.textContent = sum + '%';
                if (sum === 100) {
                    totalSpan.className = 'text-lg font-bold text-green-600';
                } else {
                    totalSpan.className = 'text-lg font-bold text-red-600';
                }
            }

            function updateNotViableLabel() {
                const val = parseFloat(conditionalInput.value) || 0;
                notViableLabel.textContent = val.toFixed(2);
            }

            inputs.forEach(input => input.addEventListener('input', updateTotal));
            conditionalInput.addEventListener('input', updateNotViableLabel);

            updateTotal();
            updateNotViableLabel();
        });
    </script>
</x-app-layout>
