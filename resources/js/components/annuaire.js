/**
 * Composant Alpine.js pour la recherche de personnel
 */
export function personnelSearch() {
    return {
        searchQuery: '',
        filters: {
            fonction: '',
            service: '',
            disponibilite: ''
        },
        results: [],
        suggestions: [],
        loading: false,
        showSuggestions: false,
        
        /**
         * Initialisation
         */
        init() {
            // Récupérer les paramètres de l'URL
            const urlParams = new URLSearchParams(window.location.search);
            this.searchQuery = urlParams.get('search') || '';
            this.filters.fonction = urlParams.get('fonction') || '';
            this.filters.service = urlParams.get('service') || '';
            this.filters.disponibilite = urlParams.get('disponibilite') || '';
        },
        
        /**
         * Recherche avec filtres
         */
        async search() {
            if (this.searchQuery.length < 2 && !this.hasFilters()) {
                return;
            }
            
            this.loading = true;
            
            try {
                const params = new URLSearchParams();
                if (this.searchQuery) params.append('q', this.searchQuery);
                if (this.filters.fonction) params.append('fonction', this.filters.fonction);
                if (this.filters.service) params.append('service', this.filters.service);
                if (this.filters.disponibilite) params.append('disponibilite', this.filters.disponibilite);
                
                const response = await fetch(`/api/recherche/personnel?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.results = data.data || [];
                    
                    // Recharger la page avec les résultats
                    window.location.href = `${window.location.pathname}?${params.toString()}`;
                }
            } catch (error) {
                console.error('Erreur de recherche:', error);
            } finally {
                this.loading = false;
            }
        },
        
        /**
         * Autocomplete pour la recherche
         */
        async autocomplete() {
            if (this.searchQuery.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }
            
            try {
                const response = await fetch(`/api/recherche/personnel/autocomplete?q=${encodeURIComponent(this.searchQuery)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    this.suggestions = await response.json();
                    this.showSuggestions = this.suggestions.length > 0;
                }
            } catch (error) {
                console.error('Erreur autocomplete:', error);
            }
        },
        
        /**
         * Sélectionner une suggestion
         */
        selectSuggestion(suggestion) {
            window.location.href = `/annuaire/${suggestion.id}`;
        },
        
        /**
         * Vérifier si des filtres sont appliqués
         */
        hasFilters() {
            return this.filters.fonction || this.filters.service || this.filters.disponibilite;
        },
        
        /**
         * Réinitialiser les filtres
         */
        resetFilters() {
            this.searchQuery = '';
            this.filters = {
                fonction: '',
                service: '',
                disponibilite: ''
            };
            this.results = [];
            window.location.href = window.location.pathname;
        }
    }
}

/**
 * Composant pour l'organigramme
 */
export function organigrammeData() {
    return {
        organigramme: [],
        loading: true,
        error: null,
        
        /**
         * Initialisation et chargement des données
         */
        async init() {
            await this.loadOrganigramme();
        },
        
        /**
         * Charger l'organigramme depuis l'API
         */
        async loadOrganigramme() {
            this.loading = true;
            this.error = null;
            
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
                    this.error = 'Erreur lors du chargement de l\'organigramme';
                    console.error('Erreur HTTP:', response.status);
                }
            } catch (error) {
                this.error = 'Impossible de charger l\'organigramme';
                console.error('Erreur:', error);
            } finally {
                this.loading = false;
            }
        },
        
        /**
         * Obtenir le nombre total de personnes
         */
        getTotalPersonnel() {
            return this.organigramme.reduce((total, service) => total + service.effectif, 0);
        },
        
        /**
         * Obtenir le nombre de services
         */
        getTotalServices() {
            return this.organigramme.length;
        }
    }
}

/**
 * Composant pour le trombinoscope
 */
export function trombinoscope() {
    return {
        viewMode: 'grid', // 'grid' ou 'list'
        sortBy: 'name', // 'name', 'service', 'fonction'
        
        /**
         * Changer le mode d'affichage
         */
        toggleViewMode() {
            this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
        },
        
        /**
         * Changer le tri
         */
        changeSortBy(field) {
            this.sortBy = field;
            // Recharger avec le nouveau tri
            const params = new URLSearchParams(window.location.search);
            params.set('sort', field);
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }
    }
}

/**
 * Composant pour les actions rapides sur une fiche personnel
 */
export function personnelActions(personnelId) {
    return {
        loading: false,
        disponibilite: null,
        
        /**
         * Charger la disponibilité
         */
        async loadDisponibilite() {
            try {
                const response = await fetch(`/annuaire/${personnelId}/disponibilite`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    this.disponibilite = await response.json();
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        },
        
        /**
         * Copier les informations de contact
         */
        copyContact(type, value) {
            navigator.clipboard.writeText(value).then(() => {
                // Afficher une notification
                this.$dispatch('notification', {
                    type: 'success',
                    message: `${type} copié dans le presse-papiers`
                });
            }).catch(err => {
                console.error('Erreur de copie:', err);
            });
        }
    }
}

/**
 * Initialisation globale
 */
document.addEventListener('alpine:init', () => {
    // Enregistrer les composants globalement si nécessaire
    window.personnelSearch = personnelSearch;
    window.organigrammeData = organigrammeData;
    window.trombinoscope = trombinoscope;
    window.personnelActions = personnelActions;
});