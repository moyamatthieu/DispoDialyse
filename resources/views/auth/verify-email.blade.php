<x-guest-layout>
    <!-- En-tête -->
    <div class="mb-6 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Vérification de l'email</h2>
        <p class="mt-2 text-sm text-gray-600">
            Un email de vérification vous a été envoyé
        </p>
    </div>

    <!-- Message d'information -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800">
            Merci de vous être inscrit ! Avant de commencer, pourriez-vous vérifier votre adresse email 
            en cliquant sur le lien que nous venons de vous envoyer ? Si vous n'avez pas reçu l'email, 
            nous vous en enverrons un nouveau avec plaisir.
        </p>
    </div>

    <!-- Message de statut -->
    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm text-green-800">
                    Un nouveau lien de vérification a été envoyé à votre adresse email.
                </p>
            </div>
        </div>
    @endif

    <!-- Formulaire de renvoi -->
    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 shadow-lg"
            >
                Renvoyer l'email de vérification
            </button>
        </form>

        <!-- Déconnexion -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                type="submit" 
                class="w-full py-2 px-4 text-sm text-gray-600 hover:text-gray-900 transition duration-200"
            >
                Se déconnecter
            </button>
        </form>
    </div>

    <!-- Aide -->
    <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Besoin d'aide ?</h3>
        <p class="text-xs text-gray-600">
            Si vous ne recevez pas l'email de vérification après plusieurs tentatives, 
            vérifiez votre dossier spam ou contactez l'administrateur système.
        </p>
    </div>
</x-guest-layout>