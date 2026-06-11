<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Editar Iniciativa de Proyecto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-slate-200 p-8">
                <form method="POST" action="{{ route('projects.update', $project) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="title" value="Título" />
                        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $project->title)" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="description" value="Descripción" />
                        <textarea id="description" name="description" rows="5" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700" required>{{ old('description', $project->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="criticality" value="Criticidad" />
                            <select id="criticality" name="criticality" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700" required>
                                <option value="low" {{ old('criticality', $project->criticality) === 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ old('criticality', $project->criticality) === 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ old('criticality', $project->criticality) === 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                            <x-input-error :messages="$errors->get('criticality')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="priority" value="Prioridad" />
                            <select id="priority" name="priority" class="block mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700" required>
                                <option value="low" {{ old('priority', $project->priority) === 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ old('priority', $project->priority) === 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ old('priority', $project->priority) === 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                            <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-4">
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white">
                            Actualizar Iniciativa
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
