<div x-data="{ open: false }" class="relative">
    <!-- Bouton utilisateur -->
    <button @click="open = !open" 
            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            aria-label="Menu utilisateur"
            aria-expanded="false">
        <!-- Avatar -->
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-{{ auth()->user()->role->color() }}-500 to-{{ auth()->user()->role->color() }}-600 flex items-center justify-center text-white font-semibold shadow-lg">
            {{ auth()->user()->initials }}
        </div>
        
        <!-- Infos utilisateur (desktop) -->
        <div class="hidden md:block text-left">
            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->full_name }}</p>
            <p class="text-xs text-gray-500">{{ auth()->user()->role->label() }}</p>
        </div>

        <!-- Icône dropdown -->
        <svg class="w-5 h-5 text-gray-400" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Menu dropdown -->
    <div x-show="open" 
         @click.away="open = false"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-50"
         style="display: none;">
        
        <!-- En-tête du profil -->
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-{{ auth()->user()->role->color() }}-500 to-{{ auth()->user()->role->color() }}-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                    {{ auth()->user()->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    <span class="inline-block px-2 py-1 mt-1 text-xs font-medium text-{{ auth()->user()->role->color() }}-800 bg-{{ auth()->user()->role->color() }}-100 rounded-full">
                        {{ auth()->user()->role->label() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Liens du menu -->
        <div class="py-2">
            <a href="{{ route('profile.edit') }}" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150">
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Mon profil
            </a>

            @if(auth()->user()->can('users.manage'))
                <a href="#" 
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Gestion des utilisateurs
                </a>
            @endif

            @if(auth()->user()->can('settings.manage'))
                <a href="#" 
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Paramètres
                </a>
            @endif

            @if(auth()->user()->can('audit.view'))
                <a href="#" 
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Logs d'audit
                </a>
            @endif
        </div>

        <!-- Informations de connexion -->
        <div class="px-4 py-2 border-t border-gray-200 text-xs text-gray-500">
            <p>Dernière connexion :</p>
            <p class="font-medium text-gray-700">
                @if(auth()->user()->last_login_at)
                    {{ auth()->user()->last_login_at->diffForHumans() }}
                @else
                    Première connexion
                @endif
            </p>
        </div>

        <!-- Déconnexion -->
        <div class="border-t border-gray-200 pt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</div>