<div x-data="organigrammeData()" class="w-full">
    <!-- Loading -->
    <div x-show="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
    
    <!-- Organigramme -->
    <div x-show="!loading" x-cloak>
        <!-- Vue par service -->
        <template x-for="service in organigramme" :key="service.nom">
            <div class="mb-8 border border-gray-200 rounded-lg overflow-hidden">
                <!-- En-tête du service -->
                <div class="bg-blue-600 text-white p-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold flex items-center">
                            🏢 <span x-text="service.nom" class="ml-2"></span>
                        </h3>
                        <span class="px-3 py-1 bg-blue-500 rounded-full text-sm font-medium">
                            <span x-text="service.effectif"></span> personne(s)
                        </span>
                    </div>
                </div>
                
                <!-- Fonctions dans le service -->
                <div class="bg-white p-6">
                    <template x-for="fonction in service.fonctions" :key="fonction.nom">
                        <div class="mb-6 last:mb-0">
                            <!-- Titre de la fonction -->
                            <div class="flex items-center mb-3 pb-2 border-b border-gray-200">
                                <h4 class="text-lg font-semibold text-gray-800" x-text="fonction.nom"></h4>
                                <span class="ml-3 px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">
                                    <span x-text="fonction.effectif"></span> personne(s)
                                </span>
                            </div>
                            
                            <!-- Personnel de cette fonction -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="person in fonction.personnel" :key="person.id">
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <!-- Photo -->
                                        <img :src="person.photo" 
                                             :alt="person.nom_complet"
                                             class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                                        
                                        <!-- Info -->
                                        <div class="flex-1 min-w-0">
                                            <a :href="'/annuaire/' + person.id" 
                                               class="font-medium text-gray-900 hover:text-blue-600 block truncate"
                                               x-text="person.nom_complet"></a>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                                <span x-show="person.telephone" class="flex items-center">
                                                    📱 <span x-text="person.telephone" class="ml-1"></span>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions rapides -->
                                        <div class="flex gap-1">
                                            <a :href="'tel:' + person.telephone" 
                                               x-show="person.telephone"
                                               class="p-1.5 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition"
                                               title="Appeler">
                                                📱
                                            </a>
                                            <a :href="'mailto:' + person.email" 
                                               class="p-1.5 bg-green-100 text-green-600 rounded hover:bg-green-200 transition"
                                               title="Email">
                                                ✉️
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
        
        <!-- Message si vide -->
        <div x-show="organigramme.length === 0" class="text-center py-12 text-gray-500">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-lg font-medium">Aucune donnée d'organigramme</p>
        </div>
    </div>
</div>

<script>
function organigrammeData() {
    return {
        organigramme: [],
        loading: true,
        
        async init() {
            try {
                const response = await fetch('/api/personnel/organigramme', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    this.organigramme = await response.json();
                } else {
                    console.error('Erreur lors du chargement de l\'organigramme');
                }
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>