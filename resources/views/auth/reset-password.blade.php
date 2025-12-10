<x-guest-layout>
    <!-- En-tête -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Réinitialiser le mot de passe</h2>
        <p class="mt-2 text-sm text-gray-600">
            Choisissez un nouveau mot de passe sécurisé
        </p>
    </div>

    <!-- Formulaire -->
    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Token caché -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Adresse email
            </label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email', $request->email) }}"
                required 
                autofocus
                autocomplete="username"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('email') border-red-500 @enderror"
                aria-label="Adresse email"
                aria-required="true"
            >
            @error('email')
                <p class="mt-2 text-sm text-red-600" role="alert">
                    <span class="font-medium">Erreur :</span> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Nouveau mot de passe -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Nouveau mot de passe
            </label>
            <input 
                id="password" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('password') border-red-500 @enderror"
                aria-label="Nouveau mot de passe"
                aria-required="true"
            >
            @error('password')
                <p class="mt-2 text-sm text-red-600" role="alert">
                    <span class="font-medium">Erreur :</span> {{ $message }}
                </p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">
                Minimum 8 caractères avec majuscules, minuscules et chiffres
            </p>
        </div>

        <!-- Confirmation du mot de passe -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirmer le mot de passe
            </label>
            <input 
                id="password_confirmation" 
                type="password" 
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                aria-label="Confirmer le mot de passe"
                aria-required="true"
            >
        </div>

        <!-- Bouton -->
        <button 
            type="submit" 
            class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 shadow-lg"
        >
            Réinitialiser le mot de passe
        </button>
    </form>

    <!-- Recommandations de sécurité -->
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">Recommandations de sécurité :</h3>
        <ul class="text-xs text-blue-800 space-y-1 list-disc list-inside">
            <li>Utilisez un mot de passe unique et complexe</li>
            <li>Ne réutilisez pas un ancien mot de passe</li>
            <li>Évitez les informations personnelles évidentes</li>
            <li>Envisagez d'activer l'authentification à deux facteurs</li>
        </ul>
    </div>
</x-guest-layout>