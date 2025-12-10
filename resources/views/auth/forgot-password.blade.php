<x-guest-layout>
    <!-- En-tête -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Mot de passe oublié</h2>
        <p class="mt-2 text-sm text-gray-600">
            Entrez votre adresse email pour recevoir un lien de réinitialisation
        </p>
    </div>

    <!-- Messages de statut -->
    @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm text-green-800">{{ session('status') }}</p>
            </div>
        </div>
    @endif

    <!-- Formulaire -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Adresse email
            </label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}"
                required 
                autofocus
                autocomplete="email"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('email') border-red-500 @enderror"
                placeholder="votre.email@example.com"
                aria-label="Adresse email"
                aria-required="true"
                aria-invalid="@error('email') true @else false @enderror"
                aria-describedby="@error('email') email-error @enderror"
            >
            @error('email')
                <p class="mt-2 text-sm text-red-600" id="email-error" role="alert">
                    <span class="font-medium">Erreur :</span> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Boutons -->
        <div class="space-y-3">
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 shadow-lg"
            >
                Envoyer le lien de réinitialisation
            </button>

            <a 
                href="{{ route('login') }}" 
                class="block text-center text-sm text-blue-600 hover:text-blue-800 transition duration-200"
            >
                ← Retour à la connexion
            </a>
        </div>
    </form>

    <!-- Information -->
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div class="ml-3">
                <p class="text-sm text-yellow-800">
                    <strong>Important :</strong> Le lien de réinitialisation sera valide pendant 60 minutes. 
                    Si vous ne recevez pas l'email, vérifiez votre dossier spam.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>