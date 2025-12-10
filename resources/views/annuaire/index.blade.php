<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <!-- Header avec recherche -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">📖 Annuaire du Personnel</h1>
            
            @can('create', App\Models\Personnel::class)
            <a href="{{ route('annuaire.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition">
                ➕ Nouveau Personnel
            </a>
            @endcan
        </div>
        
        <!-- Barre de recherche avancée -->
        <div x-data="personnelSearch()" class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Recherche textuelle avec autocomplete -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Recherche</label>
                    <input type="search" 
                           x-model="searchQuery" 
                           @input.debounce.500ms="search()"
                           placeholder="Nom, fonction, service, téléphone..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Filtre fonction -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fonction</label>
                    <select x-model="filters.fonction" @change="search()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Toutes les fonctions</option>
                        <option value="medecin">Médecin</option>
                        <option value="infirmier">Infirmier</option>
                        <option value="aide">Aide-Soignant</option>
                        <option value="secretaire">Secrétaire</option>
                        <option value="technicien">Technicien</option>
                        <option value="cadre">Cadre</option>
                    </select>
                </div>
                
                <!-- Filtre service -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                    <select x-model="filters.service" @change="search()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les services</option>
                        <option value="Dialyse">Dialyse</option>
                        <option value="Néphrologie">Néphrologie</option>
                        <option value="Urgences">Urgences</option>
                        <option value="Administration">Administration</option>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center justify-between mt-4">
                <!-- Filtre disponibilité -->
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="filters.disponibilite" value="" @change="search()" class="form-radio text-blue-600">
                        <span class="ml-2 text-sm">Tous</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="filters.disponibilite" value="disponible" @change="search()" class="form-radio text-green-600">
                        <span class="ml-2 text-sm">✅ Disponibles</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="filters.disponibilite" value="en_garde" @change="search()" class="form-radio text-orange-600">
                        <span class="ml-2 text-sm">🚨 De garde</span>
                    </label>
                </div>
                
                <!-- Export CSV -->
                @can('viewAny', App\Models\Personnel::class)
                <a href="{{ route('annuaire.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                    📥 Export CSV
                </a>
                @endcan
            </div>
            
            <!-- Résultats de recherche -->
            <div class="mt-4" x-show="searchQuery.length > 0" x-cloak>
                <p class="text-sm text-gray-600">
                    <span x-text="results.length"></span> résultat(s) trouvé(s)
                </p>
            </div>
        </div>
        
        <!-- Onglets de vue -->
        <div x-data="{ activeTab: '{{ request()->get('vue', 'liste') }}' }" class="mb-6">
            <div class="border-b border-gray-200 bg-white rounded-t-lg">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button @click="activeTab = 'liste'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'liste', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'liste'}"
                            class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition">
                        📋 Liste
                    </button>
                    <button @click="activeTab = 'trombinoscope'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'trombinoscope', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'trombinoscope'}"
                            class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition">
                        👥 Trombinoscope
                    </button>
                    <button @click="activeTab = 'organigramme'" 
                            :class="{'border-blue-500 text-blue-600': activeTab === 'organigramme', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'organigramme'}"
                            class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition">
                        🏢 Organigramme
                    </button>
                </nav>
            </div>
            
            <!-- Vue Liste (défaut) -->
            <div x-show="activeTab === 'liste'" class="bg-white rounded-b-lg shadow-md">
                @include('annuaire.partials.liste', ['personnel' => $personnel])
            </div>
            
            <!-- Vue Trombinoscope -->
            <div x-show="activeTab === 'trombinoscope'" x-cloak class="bg-white rounded-b-lg shadow-md p-6">
                @include('annuaire.partials.trombinoscope', ['personnel' => $personnel])
            </div>
            
            <!-- Vue Organigramme -->
            <div x-show="activeTab === 'organigramme'" x-cloak class="bg-white rounded-b-lg shadow-md p-6">
                @include('annuaire.partials.organigramme')
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function personnelSearch() {
            return {
                searchQuery: '{{ request()->get('search', '') }}',
                filters: {
                    fonction: '{{ request()->get('fonction', '') }}',
                    service: '{{ request()->get('service', '') }}',
                    disponibilite: '{{ request()->get('disponibilite', '') }}'
                },
                results: [],
                
                search() {
                    // Construire l'URL avec les paramètres
                    const params = new URLSearchParams();
                    if (this.searchQuery) params.append('search', this.searchQuery);
                    if (this.filters.fonction) params.append('fonction', this.filters.fonction);
                    if (this.filters.service) params.append('service', this.filters.service);
                    if (this.filters.disponibilite) params.append('disponibilite', this.filters.disponibilite);
                    
                    // Recharger la page avec les nouveaux paramètres
                    window.location.href = '{{ route('annuaire.index') }}?' + params.toString();
                }
            }
        }
    </script>
    @endpush
</x-app-layout>