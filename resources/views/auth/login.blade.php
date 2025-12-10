<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DispoDialyse - Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center justify-center">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h1 class="text-2xl font-bold text-gray-900">DispoDialyse</h1>
                        <p class="text-sm text-gray-600">Gestion de planning</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Contenu -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-xl rounded-2xl overflow-hidden">
            <!-- En-tête -->
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">Connexion</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Accédez à votre espace DispoDialyse
                </p>
            </div>

            <!-- Messages d'erreur de session -->
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Erreurs globales -->
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Formulaire de connexion -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
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
                        placeholder="exemple@dispodialyse.fr"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Se souvenir de moi -->
                <div class="flex items-center justify-between">
                    <label for="remember" class="flex items-center">
                        <input 
                            id="remember" 
                            type="checkbox" 
                            name="remember"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded"
                        >
                        <span class="ml-2 text-sm text-gray-700">Se souvenir de moi</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <!-- Bouton de connexion -->
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition duration-200 shadow-lg"
                >
                    Se connecter
                </button>
            </form>

            <!-- Lien retour -->
            <div class="mt-6 text-center">
                <a href="/" class="text-sm text-blue-600 hover:text-blue-800">
                    Retour à l'accueil
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600">
                © {{ date('Y') }} DispoDialyse. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
