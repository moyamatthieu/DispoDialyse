<aside class="fixed top-16 left-0 z-30 w-64 h-[calc(100vh-4rem)] bg-white border-r border-gray-200 overflow-y-auto transition-transform duration-300"
       :class="{ '-translate-x-full lg:translate-x-0': !sidebarOpen, 'translate-x-0': sidebarOpen }">
    
    <nav class="px-3 py-4 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Tableau de bord
        </a>

        <!-- Planning (tous les rôles peuvent voir) -->
        @can('planning.view')
            <a href="{{ route('planning.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ request()->routeIs('planning.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Planning des salles
            </a>
        @endcan

        <!-- Personnel (Annuaire) -->
        @can('personnel.view')
            <a href="{{ route('personnel.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ request()->routeIs('personnel.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Annuaire personnel
            </a>
        @endcan

        <!-- Transmissions (personnel médical) -->
        @can('transmissions.view')
            <a href="{{ route('transmissions.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ request()->routeIs('transmissions.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Transmissions
                @if(auth()->user()->can('transmissions.view'))
                    <span class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">3</span>
                @endif
            </a>
        @endcan

        <!-- Gardes -->
        @can('gardes.view')
            <a href="{{ route('gardes.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ request()->routeIs('gardes.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Planning de garde
            </a>
        @endcan

        <!-- Documents -->
        @can('documents.view')
            <a href="{{ route('documents.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ request()->routeIs('documents.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Documents
            </a>
        @endcan

        <!-- Messagerie -->
        @can('messages.view')
            <a href="{{ route('messages.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 {{ request()->routeIs('messages.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Messagerie
                <span class="ml-auto bg-blue-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">5</span>
            </a>
        @endcan

        <!-- Séparateur pour admin -->
        @if(auth()->user()->isAdmin())
            <div class="pt-4 mt-4 border-t border-gray-200">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</p>
            </div>

            <!-- Gestion des utilisateurs -->
            @can('users.manage')
                <a href="{{ route('register') }}" 
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 text-gray-700 hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Utilisateurs
                </a>
            @endcan

            <!-- Statistiques -->
            <a href="#" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition duration-150 text-gray-700 hover:bg-gray-100">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Statistiques
            </a>
        @endif
    </nav>

    <!-- Information utilisateur en bas -->
    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
            <p class="text-xs text-gray-600">Connecté en tant que</p>
        </div>
        <p class="text-sm font-medium text-gray-900 mt-1 truncate">{{ auth()->user()->role->label() }}</p>
    </div>
</aside>