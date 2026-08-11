<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Registrar Acuerdo de Aprobación</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-8">
                <form method="POST" action="{{ route('projects.approval.store', $project) }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="approval_agreement">Acuerdo</x-input-label>
                        <x-text-input id="approval_agreement" name="approval_agreement" value="{{ old('approval_agreement') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('approval_agreement')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="approval_date">Fecha</x-input-label>
                        <x-text-input type="date" id="approval_date" name="approval_date" value="{{ old('approval_date') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('approval_date')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="approval_responsible">Responsable</x-input-label>
                        <x-text-input id="approval_responsible" name="approval_responsible" value="{{ old('approval_responsible') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('approval_responsible')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="approval_justification">Justificación</x-input-label>
                        <textarea id="approval_justification" name="approval_justification" rows="4" class="mt-1 block w-full rounded-md border-slate-200 bg-slate-50 p-3">{{ old('approval_justification') }}</textarea>
                        <x-input-error :messages="$errors->get('approval_justification')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Guardar Registro</x-primary-button>
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
