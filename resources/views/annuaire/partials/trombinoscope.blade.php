<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
    @forelse($personnel as $person)
    <div class="group">
        <a href="{{ route('annuaire.show', $person) }}" class="block">
            <div class="text-center transform transition-all duration-200 hover:scale-105">
                <!-- Photo avec effet hover -->
                <div class="relative mb-3">
                    <img src="{{ $person->photo_url ?? '/images/default-avatar.png' }}" 
                         alt="{{ $person->full_name }}"
                         class="w-28 h-28 mx-auto rounded-full object-cover border-4 border-gray-200 shadow-md group-hover:border-blue-400 transition-colors">
                    
                    <!-- Badge statut -->
                    <div class="absolute bottom-0 right-1/2 transform translate-x-14 translate-y-2">
                        @if($person->isOnCall())
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-500 text-white text-xs font-bold shadow-lg animate-pulse" title="De garde">
                            🚨
                        </span>
                        @elseif($person->is_active)
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white text-xs font-bold shadow-lg" title="Disponible">
                            ✓
                        </span>
                        @endif
                    </div>
                </div>
                
                <!-- Informations -->
                <h3 class="font-semibold text-sm text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-1" title="{{ $person->full_name }}">
                    {{ $person->first_name }} {{ $person->last_name }}
                </h3>
                <p class="text-xs text-gray-600 mt-1 line-clamp-1" title="{{ $person->job_title }}">
                    {{ $person->job_title }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-1" title="{{ $person->department }}">
                    {{ $person->department }}
                </p>
                
                <!-- Contact rapide -->
                <div class="flex justify-center gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    @if($person->phone_mobile)
                    <a href="tel:{{ $person->phone_mobile }}" 
                       onclick="event.stopPropagation();"
                       class="inline-flex items-center justify-center w-7 h-7 bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition"
                       title="Appeler">
                        📱
                    </a>
                    @endif
                    <a href="mailto:{{ $person->email_pro }}" 
                       onclick="event.stopPropagation();"
                       class="inline-flex items-center justify-center w-7 h-7 bg-green-100 text-green-600 rounded-full hover:bg-green-200 transition"
                       title="Email">
                        ✉️
                    </a>
                </div>
            </div>
        </a>
    </div>
    @empty
    <div class="col-span-full py-12 text-center text-gray-500">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-lg font-medium">Aucun personnel à afficher</p>
        <p class="text-sm mt-1">Modifiez vos critères de recherche</p>
    </div>
    @endforelse
</div>

<!-- Pagination pour trombinoscope -->
@if($personnel->hasPages())
<div class="mt-6 flex justify-center">
    {{ $personnel->links() }}
</div>
@endif