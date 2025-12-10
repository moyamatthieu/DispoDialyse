import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

/**
 * Composant FullCalendar pour le planning des salles de dialyse
 */
export function planningCalendar() {
    return {
        calendar: null,
        
        /**
         * Initialiser le calendrier
         */
        init() {
            const calendarEl = document.getElementById('calendar');
            
            if (!calendarEl) {
                console.error('Element #calendar non trouvé');
                return;
            }

            this.calendar = new Calendar(calendarEl, {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                
                // Configuration de base
                initialView: 'timeGridWeek',
                locale: frLocale,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,dayGridMonth'
                },
                
                // Heures d'ouverture
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                slotDuration: '00:30:00',
                
                // Paramètres d'affichage
                height: 'auto',
                contentHeight: 600,
                expandRows: true,
                nowIndicator: true,
                allDaySlot: false,
                
                // Interactions
                editable: true,
                droppable: true,
                eventStartEditable: true,
                eventDurationEditable: true,
                
                // Format de l'heure
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                
                // Charger les événements via API
                events: (info, successCallback, failureCallback) => {
                    this.loadEvents(info, successCallback, failureCallback);
                },
                
                // Handlers d'événements
                eventClick: (info) => this.handleEventClick(info),
                eventDrop: (info) => this.handleEventDrop(info),
                eventResize: (info) => this.handleEventResize(info),
                dateClick: (info) => this.handleDateClick(info),
                
                // Style des événements
                eventClassNames: (arg) => {
                    return [`dialyse-${arg.event.extendedProps.type_dialyse}`];
                },
                
                // Info-bulle personnalisée
                eventDidMount: (info) => {
                    this.addTooltip(info);
                },
            });

            this.calendar.render();
        },

        /**
         * Charger les événements depuis l'API
         */
        async loadEvents(info, successCallback, failureCallback) {
            try {
                const params = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr,
                });

                // Ajouter les filtres si présents
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('salle_id')) {
                    params.append('salle_id', urlParams.get('salle_id'));
                }
                if (urlParams.get('type_dialyse')) {
                    params.append('type_dialyse', urlParams.get('type_dialyse'));
                }

                const response = await axios.get(`/planning/calendrier?${params.toString()}`);
                successCallback(response.data);
            } catch (error) {
                console.error('Erreur lors du chargement des événements:', error);
                failureCallback(error);
                this.showNotification('Erreur lors du chargement du planning', 'error');
            }
        },

        /**
         * Clic sur un événement
         */
        handleEventClick(info) {
            info.jsEvent.preventDefault();
            
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },

        /**
         * Déplacement d'un événement (drag & drop)
         */
        async handleEventDrop(info) {
            try {
                const response = await axios.post(`/planning/${info.event.id}/move`, {
                    date_debut: info.event.start.toISOString(),
                    date_fin: info.event.end.toISOString(),
                });

                if (response.data.conflicts && response.data.conflicts.length > 0) {
                    info.revert();
                    this.showNotification(
                        'Conflit détecté: ' + response.data.conflicts.join(', '),
                        'error'
                    );
                } else {
                    this.showNotification('Réservation déplacée avec succès', 'success');
                }
            } catch (error) {
                info.revert();
                console.error('Erreur lors du déplacement:', error);
                this.showNotification('Erreur lors du déplacement de la réservation', 'error');
            }
        },

        /**
         * Redimensionnement d'un événement
         */
        async handleEventResize(info) {
            try {
                const response = await axios.post(`/planning/${info.event.id}/move`, {
                    date_debut: info.event.start.toISOString(),
                    date_fin: info.event.end.toISOString(),
                });

                if (response.data.conflicts && response.data.conflicts.length > 0) {
                    info.revert();
                    this.showNotification(
                        'Conflit détecté: ' + response.data.conflicts.join(', '),
                        'error'
                    );
                } else {
                    this.showNotification('Durée modifiée avec succès', 'success');
                }
            } catch (error) {
                info.revert();
                console.error('Erreur lors du redimensionnement:', error);
                this.showNotification('Erreur lors de la modification', 'error');
            }
        },

        /**
         * Clic sur une date (pour créer une réservation)
         */
        handleDateClick(info) {
            // Dispatcher un événement pour ouvrir la modale de création
            window.dispatchEvent(new CustomEvent('open-modal-create', {
                detail: {
                    date_debut: info.dateStr,
                    salle_id: null,
                }
            }));
        },

        /**
         * Ajouter une info-bulle à un événement
         */
        addTooltip(info) {
            const props = info.event.extendedProps;
            const tooltipContent = `
                <div class="p-2">
                    <strong>${info.event.title}</strong><br>
                    <small>Salle: ${props.salle_nom}</small><br>
                    <small>Type: ${props.type_dialyse}</small><br>
                    <small>Personnel: ${props.personnel.length}</small>
                </div>
            `;
            
            info.el.setAttribute('title', tooltipContent);
            info.el.setAttribute('data-tippy-content', tooltipContent);
        },

        /**
         * Afficher une notification
         */
        showNotification(message, type = 'info') {
            // Utiliser une bibliothèque de notifications ou un système custom
            console.log(`[${type.toUpperCase()}] ${message}`);
            
            // Exemple simple avec alert (à remplacer par une vraie notification)
            if (type === 'error') {
                alert(message);
            }
        },

        /**
         * Rafraîchir le calendrier
         */
        refresh() {
            if (this.calendar) {
                this.calendar.refetchEvents();
            }
        },
    };
}

/**
 * Composant de formulaire de réservation (Alpine.js)
 */
export function reservationForm() {
    return {
        open: false,
        isEditMode: false,
        loading: false,
        conflicts: [],
        alternatives: [],
        reservationId: null,
        
        form: {
            salle_id: '',
            patient_reference: '',
            patient_initials: '',
            type_dialyse: 'hemodialysis',
            date_debut: '',
            date_fin: '',
            personnel_ids: [],
            isolement_requis: false,
            notes: '',
        },

        /**
         * Ouvrir la modale en mode création
         */
        openCreate() {
            this.isEditMode = false;
            this.reservationId = null;
            this.resetForm();
            this.open = true;
        },

        /**
         * Ouvrir la modale en mode édition
         */
        openEdit(reservation) {
            this.isEditMode = true;
            this.reservationId = reservation.id;
            this.loadReservation(reservation);
            this.open = true;
        },

        /**
         * Fermer la modale
         */
        close() {
            this.open = false;
            this.resetForm();
        },

        /**
         * Réinitialiser le formulaire
         */
        resetForm() {
            this.form = {
                salle_id: '',
                patient_reference: '',
                patient_initials: '',
                type_dialyse: 'hemodialysis',
                date_debut: '',
                date_fin: '',
                personnel_ids: [],
                isolement_requis: false,
                notes: '',
            };
            this.conflicts = [];
            this.alternatives = [];
        },

        /**
         * Charger les données d'une réservation
         */
        loadReservation(reservation) {
            this.form = {
                salle_id: reservation.salle_id,
                patient_reference: reservation.patient_reference,
                patient_initials: reservation.patient_initials,
                type_dialyse: reservation.dialysis_type,
                date_debut: reservation.start_time,
                date_fin: reservation.end_time,
                personnel_ids: reservation.personnel.map(p => p.id),
                isolement_requis: reservation.special_requirements?.includes('Isolement'),
                notes: reservation.notes,
            };
        },

        /**
         * Vérifier les conflits
         */
        async checkConflicts() {
            if (!this.form.salle_id || !this.form.date_debut || !this.form.date_fin) {
                return;
            }

            try {
                const response = await axios.get('/planning/api/conflits', {
                    params: {
                        ...this.form,
                        exclude_reservation_id: this.reservationId,
                    }
                });

                this.conflicts = response.data.conflicts || [];
                this.alternatives = response.data.alternatives || [];
            } catch (error) {
                console.error('Erreur lors de la vérification des conflits:', error);
            }
        },

        /**
         * Vérifier la disponibilité
         */
        async checkAvailability() {
            await this.checkConflicts();
        },

        /**
         * Appliquer une alternative
         */
        applyAlternative(alternative) {
            this.form.date_debut = alternative.date_debut;
            this.form.date_fin = alternative.date_fin;
            if (alternative.salle_id) {
                this.form.salle_id = alternative.salle_id;
            }
            this.checkConflicts();
        },

        /**
         * Soumettre le formulaire
         */
        async submit() {
            if (this.conflicts.length > 0) {
                alert('Veuillez résoudre les conflits avant de continuer');
                return;
            }

            this.loading = true;

            try {
                const url = this.isEditMode 
                    ? `/planning/${this.reservationId}` 
                    : '/planning';
                
                const method = this.isEditMode ? 'put' : 'post';

                const response = await axios[method](url, this.form);

                // Rafraîchir le calendrier
                if (window.planningCalendar) {
                    window.planningCalendar.refresh();
                }

                // Fermer la modale
                this.close();

                // Recharger la page pour voir les changements
                window.location.reload();
            } catch (error) {
                console.error('Erreur lors de la soumission:', error);
                
                if (error.response && error.response.data && error.response.data.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    alert('Erreurs de validation:\n' + errors.join('\n'));
                } else {
                    alert('Erreur lors de l\'enregistrement de la réservation');
                }
            } finally {
                this.loading = false;
            }
        },
    };
}

// Exposer globalement pour utilisation dans Blade
window.planningCalendar = planningCalendar();
window.reservationForm = reservationForm;