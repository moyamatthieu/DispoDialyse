<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-6">
        <!-- Bouton retour -->
        <div class="mb-6">
            <a href="{{ route('annuaire.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                ← Retour à l'annuaire
            </a>
        </div>

        <!-- Header avec photo et informations principales -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <div class="flex flex-col md:flex-row items-start gap-8">
                <!-- Photo -->
                <div class="flex-shrink-0">
                    <img src="{{ $personnel->photo_url ?? '/images/default-avatar.png' }}" 
                         alt="{{ $personnel->full_name }}"
                         class="w-40 h-40 rounded-full object-cover border-4 border-gray-200 shadow-md">
                </div>
                
                <!-- Informations principales -->
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        {{ $personnel->first_name }} {{ $personnel->last_name }}
                    </h1>
                    <p class="text-2xl text-gray-600 mb-4">{{ $personnel->job_title }}</p>
                    
                    @if($personnel->specialty)
                    <p class="text-lg text-gray-500 mb-4">🩺 {{ $personnel->specialty }}</p>
                    @endif
                    
                    <div class="flex flex-wrap gap-3 mb-6">
                        <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            🏢 {{ $personnel->department }}
                        </span>
                        
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                     {{ $personnel->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $personnel->is_active ? '✅ Actif' : '⏸️ Inactif' }}
                        </span>
                        
                        @if($stats['est_de_garde'])
                        <span class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-800 rounded-full text-sm font-medium animate-pulse">
                            🚨 De garde
                        </span>
                        @endif
                        
                        @if($personnel->hasUserAccount())
                        <span class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                            🔐 Compte utilisateur
                        </span>
                        @endif
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    @can('update', $personnel)
                    <a href="{{ route('annuaire.edit', $personnel) }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition">
                        ✏️ Modifier
                    </a>
                    @endcan
                    
                    <a href="mailto:{{ $personnel->email_pro }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                        ✉️ Email
                    </a>
                    
                    @if($personnel->phone_mobile)
                    <a href="tel:{{ $personnel->phone_mobile }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                        📱 Appeler
                    </a>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Sections d'informations -->
        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Contact -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    📞 Contact
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Email :</dt>
                        <dd class="text-gray-900">
                            <a href="mailto:{{ $personnel->email_pro }}" class="text-blue-600 hover:underline">
                                {{ $personnel->email_pro }}
                            </a>
                        </dd>
                    </div>
                    
                    @if($personnel->phone_office)
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Fixe :</dt>
                        <dd class="text-gray-900">{{ $personnel->phone_office }}</dd>
                    </div>
                    @endif
                    
                    @if($personnel->phone_mobile)
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Mobile :</dt>
                        <dd class="text-gray-900">{{ $personnel->phone_mobile }}</dd>
                    </div>
                    @endif
                    
                    @if($personnel->phone_pager)
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Bip :</dt>
                        <dd class="text-gray-900">{{ $personnel->phone_pager }}</dd>
                    </div>
                    @endif
                    
                    @if($personnel->extension)
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Extension :</dt>
                        <dd class="text-gray-900 font-mono">{{ $personnel->extension }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            
            <!-- Qualifications -->
            @if($personnel->qualifications || $personnel->certifications)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    🎓 Qualifications
                </h2>
                
                @if($personnel->qualifications)
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-700 mb-2">Formations :</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-900">
                        @foreach($personnel->qualifications as $qualif)
                        <li>{{ $qualif }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                @if($personnel->certifications)
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Certifications :</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-900">
                        @foreach($personnel->certifications as $cert)
                        <li>{{ $cert }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif
            
            <!-- Langues -->
            @if($personnel->languages)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    🌍 Langues Parlées
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($personnel->languages as $langue)
                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        {{ $langue }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Statistiques -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    📊 Statistiques
                </h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Ancienneté :</dt>
                        <dd class="text-gray-900">{{ $stats['anciennete'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Gardes ce mois :</dt>
                        <dd class="text-gray-900 font-bold">{{ $stats['gardes_mois'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Statut actuel :</dt>
                        <dd>
                            @if($stats['est_de_garde'])
                            <span class="text-orange-600 font-semibold">🚨 De garde</span>
                            @else
                            <span class="text-green-600 font-semibold">✅ Disponible</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
        
        <!-- Informations organisationnelles -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                🏢 Informations Organisationnelles
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Service :</dt>
                        <dd class="text-gray-900">{{ $personnel->department }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Type de contrat :</dt>
                        <dd class="text-gray-900">
                            @switch($personnel->employment_type)
                                @case('full_time')
                                    Temps plein
                                    @break
                                @case('part_time')
                                    Temps partiel
                                    @break
                                @case('contractor')
                                    Contractuel
                                    @break
                                @default
                                    {{ $personnel->employment_type }}
                            @endswitch
                        </dd>
                    </div>
                </dl>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Date d'arrivée :</dt>
                        <dd class="text-gray-900">{{ $personnel->hire_date?->format('d/m/Y') ?? 'Non renseignée' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-700">Ancienneté :</dt>
                        <dd class="text-gray-900 font-semibold">{{ $stats['anciennete'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        <!-- Gardes récentes -->
        @if($personnel->gardes->isNotEmpty())
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                🚨 Gardes Récentes
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horaires</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($personnel->gardes as $garde)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $garde->start_datetime->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ ucfirst($garde->shift_type) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $garde->start_datetime->format('H:i') }} - {{ $garde->end_datetime->format('H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                           {{ $garde->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($garde->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>