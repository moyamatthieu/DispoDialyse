<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('annuaire.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mb-4">
                ← Retour à l'annuaire
            </a>
            <h1 class="text-3xl font-bold text-gray-900">➕ Nouveau Personnel</h1>
            <p class="text-gray-600 mt-2">Créer une nouvelle fiche dans l'annuaire du personnel</p>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('annuaire.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-8">
            @csrf
            
            @include('annuaire.partials.form')
        </form>
    </div>
</x-app-layout>