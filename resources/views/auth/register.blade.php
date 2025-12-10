<x-guest-layout>
    <!-- En-tête -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Créer un utilisateur</h2>
        <p class="mt-2 text-sm text-gray-600">
            Réservé aux administrateurs
        </p>
    </div>

    <!-- Formulaire d'inscription -->
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Nom d'utilisateur -->
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                Nom d'utilisateur <span class="text-red-500">*</span>
            </label>
            <input 
                id="username" 
                type="text" 
                name="username" 
                value="{{ old('username') }}"
                required 
                autofocus 
                autocomplete="username"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('username') border-red-500 @enderror"
                aria-required="true"
            >
            @error('username')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-red-500">*</span>
            </label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}"
                required 
                autocomplete="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                aria-required="true"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Prénom et Nom -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Prénom <span class="text-red-500">*</span>
                </label>
                <input 
                    id="first_name" 
                    type="text" 
                    name="first_name" 
                    value="{{ old('first_name') }}"
                    required 
                    autocomplete="given-name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-500 @enderror"
                    aria-required="true"
                >
                @error('first_name')
                    <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom <span class="text-red-500">*</span>
                </label>
                <input 
                    id="last_name" 
                    type="text" 
                    name="last_name" 
                    value="{{ old('last_name') }}"
                    required 
                    autocomplete="family-name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('last_name') border-red-500 @enderror"
                    aria-required="true"
                >
                @error('last_name')
                    <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Téléphone -->
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                Téléphone
            </label>
            <input 
                id="phone" 
                type="tel" 
                name="phone" 
                value="{{ old('phone') }}"
                autocomplete="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                placeholder="Ex: 06 12 34 56 78"
            >
            @error('phone')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Rôle -->
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                Rôle <span class="text-red-500">*</span>
            </label>
            <select 
                id="role" 
                name="role" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror"
                aria-required="true"
            >
                <option value="">Sélectionnez un rôle</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Mot de passe -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                Mot de passe <span class="text-red-500">*</span>
            </label>
            <input 
                id="password" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                aria-required="true"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">
                Minimum 8 caractères, avec majuscules, minuscules et chiffres
            </p>
        </div>

        <!-- Confirmation mot de passe -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                Confirmer le mot de passe <span class="text-red-500">*</span>
            </label>
            <input 
                id="password_confirmation" 
                type="password" 
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                aria-required="true"
            >
        </div>

        <!-- Boutons -->
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Retour au tableau de bord
            </a>
            <button 
                type="submit" 
                class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200"
            >
                Créer l'utilisateur
            </button>
        </div>
    </form>
</x-guest-layout>