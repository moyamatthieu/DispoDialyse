<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('annuaire.show', $personnel) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mb-4">
                ← Retour à la fiche
            </a>
            <h1 class="text-3xl font-bold text-gray-900">✏️ Modifier le Personnel</h1>
            <p class="text-gray-600 mt-2">{{ $personnel->full_name }} - {{ $personnel->job_title }}</p>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('annuaire.update', $personnel) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-8">
            @csrf
            @method('PUT')
            
            @include('annuaire.partials.form')
        </form>

        <!-- Zone de danger -->
        @can('delete', $personnel)
        <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-900 mb-2">⚠️ Zone de danger</h3>
            <p class="text-sm text-red-700 mb-4">L'archivage de cette fiche personnel est irréversible. Le personnel sera désactivé et ses données archivées.</p>
            <form action="{{ route('annuaire.destroy', $personnel) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir archiver ce personnel ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                    🗑️ Archiver ce personnel
                </button>
            </form>
        </div>
        @endcan
    </div>
</x-app-layout>