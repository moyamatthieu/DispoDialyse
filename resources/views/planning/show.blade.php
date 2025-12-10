<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 Détails de la Réservation #{{ $reservation->id }}
            </h2>
            <div class="flex space-x-2">
                @if($reservation->isEditable())
                @can('planning.edit')
                <a href="{{ route('planning.edit', $reservation) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                @endcan
                @endif
                
                @if($reservation->isCancellable())
                @can('planning.delete')
                <button 
                    @click="$dispatch('open-modal', 'cancel-reservation')"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annuler
                </button>
                @endcan
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Informations principales --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Statut et type --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Informations générales</h3>
                                <div class="flex space-x-2">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $reservation->status->getBadgeClass() }}">
                                        {{ $reservation->status->label() }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold" 
                                          style="background-color: {{ $reservation->dialysis_type->getColor() }}; color: white;">
                                        {{ $reservation->dialysis_type->label() }}
                                    </span>
                                </div>
                            </div>

                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Patient</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $reservation->patient_initials ?? $reservation->patient_reference }}
                                        <span class="text-gray-500 text-xs">({{ $reservation->patient_reference }})</span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Salle</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ $reservation->salle->name }}
                                        @if($reservation->salle->is_isolation)
                                        <span class="ml-2 px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">Isolement</span>
                                        @endif
                                    </dd>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Date et heure de début</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $reservation->start_time->format('d/m/Y à H:i') }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Date et heure de fin</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $reservation->end_time->format('d/m/Y à H:i') }}
                                        </dd>
                                    </div>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Durée</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-medium">
                                        {{ $reservation->duration_formatted }}
                                    </dd>
                                </div>

                                @if($reservation->special_requirements)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Besoins spéciaux</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $reservation->special_requirements }}
                                    </dd>
                                </div>
                                @endif

                                @if($reservation->notes)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Notes opérationnelles</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $reservation->notes }}
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Personnel assigné --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Personnel assigné</h3>
                            
                            @if($reservation->personnel->count() > 0)
                            <div class="space-y-3">
                                @foreach($reservation->personnel as $person)
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ $person->initials }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $person->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $person->job_title }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-gray-500 italic">Aucun personnel assigné</p>
                            @endif
                        </div>
                    </div>

                    {{-- Transmissions liées --}}
                    @if($reservation->transmissions->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transmissions associées</h3>
                            <div class="space-y-3">
                                @foreach($reservation->transmissions as $transmission)
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $transmission->title }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $transmission->created_at->format('d/m/Y à H:i') }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $transmission->priority->getBadgeClass() }}">
                                            {{ $transmission->priority->label() }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Métadonnées --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Métadonnées</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-xs text-gray-500">Créée par</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $reservation->creator->name ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Créée le</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $reservation->created_at->format('d/m/Y à H:i') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Dernière modification</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $reservation->updated_at->format('d/m/Y à H:i') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Annulation (si applicable) --}}
                    @if($reservation->cancelled_at)
                    <div class="bg-red-50 border border-red-200 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-semibold text-red-900 mb-4">Annulation</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-xs text-red-700">Date d'annulation</dt>
                                    <dd class="mt-1 text-sm text-red-900">
                                        {{ $reservation->cancelled_at->format('d/m/Y à H:i') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-red-700">Motif</dt>
                                    <dd class="mt-1 text-sm text-red-900">
                                        {{ $reservation->cancellation_reason }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    @endif

                    {{-- Actions rapides --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Actions rapides</h3>
                            <div class="space-y-2">
                                @can('planning.create')
                                <a href="{{ route('planning.duplicate', $reservation) }}" 
                                   class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded transition duration-150 ease-in-out">
                                    📋 Dupliquer
                                </a>
                                @endcan
                                
                                <a href="{{ route('planning.index') }}" 
                                   class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded transition duration-150 ease-in-out">
                                    ← Retour au planning
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>