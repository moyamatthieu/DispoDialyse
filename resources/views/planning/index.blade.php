<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📅 Planning des Salles de Dialyse
            </h2>
            @can('planning.create')
            <button 
                @click="$dispatch('open-modal', 'create-reservation')"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle Réservation
            </button>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Messages de succès/erreur --}}
            @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            {{-- Filtres --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('planning.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <div>
                            <label for="filter-salle" class="block text-sm font-medium text-gray-700 mb-1">
                                Filtrer par salle
                            </label>
                            <select 
                                id="filter-salle" 
                                name="salle_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Toutes les salles</option>
                                @foreach($salles as $salle)
                                <option value="{{ $salle->id }}" {{ $filters['salle_id'] == $salle->id ? 'selected' : '' }}>
                                    {{ $salle->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-1">
                                Type de dialyse
                            </label>
                            <select 
                                id="filter-type" 
                                name="type_dialyse"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Tous les types</option>
                                @foreach($typesDialyse as $type)
                                <option value="{{ $type->value }}" {{ $filters['type_dialyse'] == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-date" class="block text-sm font-medium text-gray-700 mb-1">
                                Date
                            </label>
                            <input 
                                type="date" 
                                id="filter-date" 
                                name="date"
                                value="{{ $filters['date'] }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="flex items-end">
                            <button 
                                type="submit"
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Onglets de vue --}}
            <div x-data="{ activeTab: 'calendar' }" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                
                {{-- Navigation des onglets --}}
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button 
                            @click="activeTab = 'calendar'"
                            :class="activeTab === 'calendar' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition duration-150 ease-in-out">
                            📅 Calendrier
                        </button>
                        <button 
                            @click="activeTab = 'day'"
                            :class="activeTab === 'day' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition duration-150 ease-in-out">
                            📋 Vue Journée
                        </button>
                        <button 
                            @click="activeTab = 'list'"
                            :class="activeTab === 'list' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition duration-150 ease-in-out">
                            📄 Vue Liste
                        </button>
                    </nav>
                </div>

                {{-- Contenu des onglets --}}
                <div class="p-6">
                    
                    {{-- Vue Calendrier (FullCalendar) --}}
                    <div x-show="activeTab === 'calendar'" x-cloak>
                        <div id="calendar" class="min-h-[600px]"></div>
                    </div>

                    {{-- Vue Journalière --}}
                    <div x-show="activeTab === 'day'" x-cloak>
                        <div id="day-view">
                            <p class="text-gray-500 text-center py-8">Chargement de la vue journalière...</p>
                        </div>
                    </div>

                    {{-- Vue Liste --}}
                    <div x-show="activeTab === 'list'" x-cloak>
                        <div id="list-view">
                            <p class="text-gray-500 text-center py-8">Chargement de la liste...</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Légende des couleurs --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Légende</h3>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center">
                            <span class="inline-block w-4 h-4 rounded mr-2" style="background-color: #3b82f6;"></span>
                            <span class="text-sm text-gray-600">Hémodialyse</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-4 h-4 rounded mr-2" style="background-color: #8b5cf6;"></span>
                            <span class="text-sm text-gray-600">Hémodiafiltration</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-4 h-4 rounded mr-2" style="background-color: #10b981;"></span>
                            <span class="text-sm text-gray-600">Dialyse Péritonéale</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale de création/modification --}}
    @include('planning.partials.reservation-form-modal')

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.planningCalendar.init();
        });
    </script>
    @endpush
</x-app-layout>