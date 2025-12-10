{{-- Modale de création/modification de réservation --}}
<div 
    x-data="reservationForm()" 
    x-show="open" 
    @open-modal-create.window="openCreate()"
    @open-modal-edit.window="openEdit($event.detail)"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true">
    
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        {{-- Overlay --}}
        <div 
            x-show="open" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
            @click="close()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Contenu de la modale --}}
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <form @submit.prevent="submit">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                <span x-text="isEditMode ? 'Modifier la réservation' : 'Nouvelle réservation'"></span>
                            </h3>

                            <div class="space-y-4">
                                
                                {{-- Salle --}}
                                <div>
                                    <label for="salle_id" class="block text-sm font-medium text-gray-700">
                                        Salle <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        x-model="form.salle_id" 
                                        @change="checkAvailability()"
                                        id="salle_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Sélectionner une salle</option>
                                        @foreach($salles as $salle)
                                        <option value="{{ $salle->id }}">
                                            {{ $salle->name }} 
                                            @if($salle->is_isolation) (Isolement) @endif
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Patient --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="patient_reference" class="block text-sm font-medium text-gray-700">
                                            Référence patient <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            x-model="form.patient_reference" 
                                            id="patient_reference"
                                            placeholder="PAT-2024-001"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="patient_initials" class="block text-sm font-medium text-gray-700">
                                            Initiales patient
                                        </label>
                                        <input 
                                            type="text" 
                                            x-model="form.patient_initials" 
                                            id="patient_initials"
                                            placeholder="J.D."
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                {{-- Type de dialyse --}}
                                <div>
                                    <label for="type_dialyse" class="block text-sm font-medium text-gray-700">
                                        Type de dialyse <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        x-model="form.type_dialyse" 
                                        id="type_dialyse"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach($typesDialyse as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date et heure --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="date_debut" class="block text-sm font-medium text-gray-700">
                                            Date et heure de début <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="datetime-local" 
                                            x-model="form.date_debut" 
                                            @change="checkConflicts()"
                                            id="date_debut"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="date_fin" class="block text-sm font-medium text-gray-700">
                                            Date et heure de fin <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="datetime-local" 
                                            x-model="form.date_fin" 
                                            @change="checkConflicts()"
                                            id="date_fin"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                {{-- Personnel assigné --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Personnel assigné <span class="text-red-500">*</span>
                                    </label>
                                    <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-md p-2 space-y-1">
                                        @foreach($personnel as $person)
                                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                :value="{{ $person->id }}" 
                                                x-model="form.personnel_ids"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm">
                                                {{ $person->full_name }} 
                                                <span class="text-gray-500">({{ $person->job_title }})</span>
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Options --}}
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            x-model="form.isolement_requis"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Isolement requis</span>
                                    </label>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">
                                        Notes opérationnelles
                                    </label>
                                    <textarea 
                                        x-model="form.notes" 
                                        id="notes"
                                        rows="3"
                                        placeholder="Précautions particulières, informations importantes..."
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                </div>

                                {{-- Alertes de conflits --}}
                                <div x-show="conflicts.length > 0" class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">⚠️ Conflits détectés :</h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <ul class="list-disc list-inside space-y-1">
                                                    <template x-for="conflict in conflicts" :key="conflict.message">
                                                        <li x-text="conflict.message"></li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Suggestions alternatives --}}
                                <div x-show="alternatives.length > 0" class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                    <h3 class="text-sm font-medium text-blue-800 mb-2">💡 Créneaux alternatifs disponibles :</h3>
                                    <div class="space-y-2">
                                        <template x-for="alt in alternatives" :key="alt.label">
                                            <button 
                                                type="button" 
                                                @click="applyAlternative(alt)"
                                                class="w-full text-left text-sm bg-white hover:bg-blue-50 border border-blue-300 rounded px-3 py-2 transition duration-150 ease-in-out">
                                                <span x-text="alt.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="submit" 
                        :disabled="conflicts.length > 0 || loading"
                        :class="conflicts.length > 0 || loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition duration-150 ease-in-out">
                        <span x-show="!loading">Enregistrer</span>
                        <span x-show="loading">Enregistrement...</span>
                    </button>
                    <button 
                        type="button" 
                        @click="close()" 
                        :disabled="loading"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition duration-150 ease-in-out">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>