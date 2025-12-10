<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Photo
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nom Prénom
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fonction
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Service
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contact
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($personnel as $person)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                    <img src="{{ $person->photo_url ?? '/images/default-avatar.png' }}" 
                         alt="{{ $person->full_name }}"
                         class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                <a href="{{ route('annuaire.show', $person) }}" 
                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $person->first_name }} {{ $person->last_name }}
                                </a>
                            </div>
                            @if($person->specialty)
                            <div class="text-sm text-gray-500">{{ $person->specialty }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $person->job_title }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $person->department }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">
                        @if($person->phone_mobile)
                        <div class="flex items-center mb-1">
                            <span class="mr-1">📱</span>
                            <a href="tel:{{ $person->phone_mobile }}" class="hover:text-blue-600">
                                {{ $person->phone_mobile }}
                            </a>
                        </div>
                        @endif
                        <div class="flex items-center text-gray-600">
                            <span class="mr-1">✉️</span>
                            <a href="mailto:{{ $person->email_pro }}" class="hover:text-blue-600 truncate max-w-xs">
                                {{ $person->email_pro }}
                            </a>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                   {{ $person->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $person->is_active ? '✅ Actif' : '⏸️ Inactif' }}
                        </span>
                        @if($person->isOnCall())
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                            🚨 De garde
                        </span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('annuaire.show', $person) }}" 
                           class="text-blue-600 hover:text-blue-900"
                           title="Voir la fiche">
                            👁️
                        </a>
                        @can('update', $person)
                        <a href="{{ route('annuaire.edit', $person) }}" 
                           class="text-gray-600 hover:text-gray-900"
                           title="Modifier">
                            ✏️
                        </a>
                        @endcan
                        <a href="mailto:{{ $person->email_pro }}" 
                           class="text-green-600 hover:text-green-900"
                           title="Envoyer un email">
                            ✉️
                        </a>
                        @if($person->phone_mobile)
                        <a href="tel:{{ $person->phone_mobile }}" 
                           class="text-purple-600 hover:text-purple-900"
                           title="Appeler">
                            📱
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-lg font-medium">Aucun personnel trouvé</p>
                        <p class="text-sm mt-1">Essayez de modifier vos critères de recherche</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($personnel->hasPages())
<div class="px-6 py-4 border-t border-gray-200">
    {{ $personnel->links() }}
</div>
@endif