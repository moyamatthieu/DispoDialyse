<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DispoDialyse - Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">DispoDialyse</h1>
                            <p class="text-xs text-gray-500">Gestion de planning</p>
                        </div>
                    </div>
                    <div>
                        @auth
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                    Déconnexion
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Connexion
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="flex-1 flex flex-col items-center justify-center px-4 py-20">
            <div class="text-center max-w-2xl">
                <h2 class="text-5xl font-bold text-gray-900 mb-6">
                    Bienvenue sur <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">DispoDialyse</span>
                </h2>
                <p class="text-xl text-gray-600 mb-8">
                    Plateforme de gestion de planning pour les centres de dialyse
                </p>
                
                @auth
                    <div class="space-y-4">
                        <p class="text-lg text-gray-700">
                            Bienvenue, <span class="font-semibold">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                        </p>
                        <a href="{{ route('dashboard') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:shadow-lg transition transform hover:scale-105">
                            Accéder au tableau de bord
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        <a href="{{ route('login') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:shadow-lg transition transform hover:scale-105">
                            Se connecter
                        </a>
                        <p class="text-sm text-gray-600 mt-4">
                            Identifiants de test : <br>
                            <span class="font-mono text-xs">admin@dispodialyse.fr</span> / <span class="font-mono text-xs">password</span>
                        </p>
                    </div>
                @endauth
            </div>

            <!-- Features Grid -->
            <div class="mt-20 grid md:grid-cols-3 gap-8 max-w-4xl w-full">
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Planning Efficace</h3>
                    <p class="text-sm text-gray-600">Gérez vos plannings de dialyse en temps réel</p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Gestion des Ressources</h3>
                    <p class="text-sm text-gray-600">Optimisez l'allocation des salles et du personnel</p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Suivi Complet</h3>
                    <p class="text-sm text-gray-600">Consultez les transmissions et documents importants</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-6 px-4">
            <div class="max-w-7xl mx-auto text-center text-sm text-gray-600">
                <p>&copy; 2025 DispoDialyse. Tous droits réservés.</p>
            </div>
        </footer>
    </div>
</body>
</html>
