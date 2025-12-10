@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
            <p class="mt-1 text-sm text-gray-600">
                Bienvenue, {{ auth()->user()->first_name }} | {{ auth()->user()->role->label() }}
            </p>
        </div>
        <div class="text-sm text-gray-500">
            {{ now()->isoFormat('dddd D MMMM YYYY') }}
        </div>
    </div>
@endsection

@section('content')
    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if(auth()->user()->isAdmin())
            <!-- Stats admin -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Réservations aujourd'hui</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalReservations ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Personnel actif</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalPersonnel ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Transmissions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalTransmissions ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Messages non lus (tous) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Messages non lus</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $unreadMessages ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu spécifique au rôle -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if(auth()->user()->role->isMedical())
            <!-- Planning du jour pour personnel médical -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Planning du jour</h2>
                @if(isset($reservationsAujourdhui) && $reservationsAujourdhui->count() > 0)
                    <div class="space-y-3">
                        @foreach($reservationsAujourdhui->take(5) as $reservation)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $reservation->salle->nom ?? 'Salle' }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $reservation->heure_debut }} - {{ $reservation->heure_fin }}
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                    {{ $reservation->statut }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Aucune réservation pour aujourd'hui</p>
                @endif
            </div>

            <!-- Transmissions urgentes -->
            @if(isset($transmissionsUrgentes))
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Transmissions urgentes</h2>
                    @if($transmissionsUrgentes->count() > 0)
                        <div class="space-y-3">
                            @foreach($transmissionsUrgentes as $transmission)
                                <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                                    <p class="font-medium text-gray-900">{{ $transmission->titre }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($transmission->contenu, 100) }}</p>
                                    <p class="text-xs text-gray-500 mt-2">{{ $transmission->created_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucune transmission urgente</p>
                    @endif
                </div>
            @endif
        @endif

        @if(auth()->user()->isAdmin())
            <!-- Activité récente pour admin -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Activité récente</h2>
                @if(isset($recentActivity) && $recentActivity->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentActivity as $activity)
                            <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $activity->causer->full_name ?? 'Système' }} • {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Aucune activité récente</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Actions rapides -->
    <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @can('planning.create')
                <a href="{{ route('planning.create') }}" 
                   class="flex flex-col items-center p-4 bg-white rounded-lg hover:shadow-md transition duration-200">
                    <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Nouvelle réservation</span>
                </a>
            @endcan

            @can('transmissions.create')
                <a href="{{ route('transmissions.create') }}" 
                   class="flex flex-col items-center p-4 bg-white rounded-lg hover:shadow-md transition duration-200">
                    <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Nouvelle transmission</span>
                </a>
            @endcan

            @can('messages.send')
                <a href="{{ route('messages.create') }}" 
                   class="flex flex-col items-center p-4 bg-white rounded-lg hover:shadow-md transition duration-200">
                    <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Nouveau message</span>
                </a>
            @endcan

            <a href="{{ route('planning.calendar') }}" 
               class="flex flex-col items-center p-4 bg-white rounded-lg hover:shadow-md transition duration-200">
                <svg class="w-8 h-8 text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700">Voir le calendrier</span>
            </a>
        </div>
    </div>
@endsection