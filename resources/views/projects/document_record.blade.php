<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Expediente Documental: {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a los detalles del proyecto
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 p-8">
                @if ($project->documents->count() > 0)
                    <div class="space-y-4">
                        @foreach ($project->documents as $document)
                            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            <a href="{{ route('projects.document-record.download', [$project, $document]) }}" class="hover:underline">
                                                {{ $document->original_name }}
                                            </a>
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            <span>Formato: {{ strtoupper($document->file_type) }}</span>
                                            <span class="mx-1.5">&bull;</span>
                                            <span>Subido por: {{ $document->uploadedBy->name }}</span>
                                            <span class="mx-1.5">&bull;</span>
                                            <span>Fecha: {{ $document->created_at->format('d/m/Y H:i') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('projects.document-record.download', [$project, $document]) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                                        Descargar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-6">No hay documentos adjuntos para este proyecto.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
