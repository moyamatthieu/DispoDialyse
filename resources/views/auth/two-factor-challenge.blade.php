<x-guest-layout>
    <!-- En-tête -->
    <div class="mb-6 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Authentification à deux facteurs</h2>
        <p class="mt-2 text-sm text-gray-600">
            Entrez le code de vérification depuis votre application
        </p>
    </div>

    <!-- Formulaire -->
    <form method="POST" action="{{ route('two-factor.challenge') }}" class="space-y-6">
        @csrf

        <!-- Code 2FA -->
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                Code de vérification (6 chiffres)
            </label>
            <input 
                id="code" 
                type="text" 
                name="code" 
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                required 
                autofocus
                autocomplete="one-time-code"
                class="w-full px-4 py-3 text-center text-2xl font-mono tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 @error('code') border-red-500 @enderror"
                placeholder="000000"
                aria-label="Code de vérification"
                aria-required="true"
                aria-invalid="@error('code') true @else false @enderror"
                aria-describedby="@error('code') code-error @else code-help @enderror"
            >
            @error('code')
                <p class="mt-2 text-sm text-red-600" id="code-error" role="alert">
                    <span class="font-medium">Erreur :</span> {{ $message }}
                </p>
            @else
                <p class="mt-2 text-xs text-gray-500 text-center" id="code-help">
                    Ouvrez votre application d'authentification (Google Authenticator, Authy, etc.)
                </p>
            @enderror
        </div>

        <!-- Boutons -->
        <div class="space-y-3">
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 shadow-lg"
            >
                Vérifier le code
            </button>

            <!-- Retour à la connexion -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="w-full py-2 px-4 text-sm text-gray-600 hover:text-gray-900 transition duration-200"
                >
                    ← Annuler et se déconnecter
                </button>
            </form>
        </div>
    </form>

    <!-- Informations d'aide -->
    <div class="mt-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
        <h3 class="text-sm font-semibold text-indigo-900 mb-2">Code non reçu ?</h3>
        <ul class="text-xs text-indigo-800 space-y-1">
            <li>• Vérifiez que l'heure de votre appareil est correcte</li>
            <li>• Assurez-vous d'utiliser la bonne application d'authentification</li>
            <li>• Le code change toutes les 30 secondes</li>
            <li>• Contactez l'administrateur si le problème persiste</li>
        </ul>
    </div>

    <!-- Script pour auto-focus et validation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('code');
            
            // Auto-submit quand 6 chiffres sont entrés
            codeInput.addEventListener('input', function(e) {
                // Ne garder que les chiffres
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto-submit si 6 chiffres
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
        });
    </script>
</x-guest-layout>