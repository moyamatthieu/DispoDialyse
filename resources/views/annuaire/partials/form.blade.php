@php
    $isEdit = isset($personnel);
@endphp

<div class="space-y-6">
    <!-- Identité -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 Identité</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="first_name" 
                       id="first_name" 
                       value="{{ old('first_name', $personnel->first_name ?? '') }}"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-500 @enderror">
                @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="last_name" 
                       id="last_name" 
                       value="{{ old('last_name', $personnel->last_name ?? '') }}"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('last_name') border-red-500 @enderror">
                @error('last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Photo -->
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Photo de profil</label>
            @if($isEdit && $personnel->photo_url)
            <div class="flex items-center gap-4 mb-3">
                <img src="{{ $personnel->photo_url }}" alt="Photo actuelle" class="w-20 h-20 rounded-full object-cover">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remove_photo" value="1" class="form-checkbox text-red-600">
                    <span class="ml-2 text-sm text-red-600">Supprimer la photo</span>
                </label>
            </div>
            @endif
            <input type="file" 
                   name="photo" 
                   id="photo" 
                   accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF. Taille max: 2 Mo</p>
            @error('photo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    
    <!-- Informations professionnelles -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">💼 Informations Professionnelles</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1">Fonction <span class="text-red-500">*</span></label>
                <input type="text" 
                       name="job_title" 
                       id="job_title" 
                       value="{{ old('job_title', $personnel->job_title ?? '') }}"
                       required
                       list="job_titles"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('job_title') border-red-500 @enderror">
                <datalist id="job_titles">
                    <option value="Médecin">
                    <option value="Infirmier">
                    <option value="Aide-Soignant">
                    <option value="Secrétaire Médicale">
                    <option value="Cadre de Santé">
                    <option value="Technicien">
                </datalist>
                @error('job_title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">Spécialité</label>
                <input type="text" 
                       name="specialty" 
                       id="specialty" 
                       value="{{ old('specialty', $personnel->specialty ?? '') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Service <span class="text-red-500">*</span></label>
                <select name="department" 
                        id="department" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department') border-red-500 @enderror">
                    <option value="">-- Sélectionner --</option>
                    <option value="Dialyse" {{ old('department', $personnel->department ?? '') == 'Dialyse' ? 'selected' : '' }}>Dialyse</option>
                    <option value="Néphrologie" {{ old('department', $personnel->department ?? '') == 'Néphrologie' ? 'selected' : '' }}>Néphrologie</option>
                    <option value="Urgences" {{ old('department', $personnel->department ?? '') == 'Urgences' ? 'selected' : '' }}>Urgences</option>
                    <option value="Administration" {{ old('department', $personnel->department ?? '') == 'Administration' ? 'selected' : '' }}>Administration</option>
                    <option value="Laboratoire" {{ old('department', $personnel->department ?? '') == 'Laboratoire' ? 'selected' : '' }}>Laboratoire</option>
                </select>
                @error('department')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-1">Type de contrat <span class="text-red-500">*</span></label>
                <select name="employment_type" 
                        id="employment_type" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('employment_type') border-red-500 @enderror">
                    <option value="full_time" {{ old('employment_type', $personnel->employment_type ?? 'full_time') == 'full_time' ? 'selected' : '' }}>Temps plein</option>
                    <option value="part_time" {{ old('employment_type', $personnel->employment_type ?? '') == 'part_time' ? 'selected' : '' }}>Temps partiel</option>
                    <option value="contractor" {{ old('employment_type', $personnel->employment_type ?? '') == 'contractor' ? 'selected' : '' }}>Contractuel</option>
                </select>
                @error('employment_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-1">Date d'embauche <span class="text-red-500">*</span></label>
                <input type="date" 
                       name="hire_date" 
                       id="hire_date" 
                       value="{{ old('hire_date', $personnel->hire_date?->format('Y-m-d') ?? '') }}"
                       required
                       max="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('hire_date') border-red-500 @enderror">
                @error('hire_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex items-center pt-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $personnel->is_active ?? true) ? 'checked' : '' }}
                           class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="ml-2 text-sm font-medium text-gray-700">Personnel actif</span>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Contact -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📞 Contact</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="email_pro" class="block text-sm font-medium text-gray-700 mb-1">Email professionnel <span class="text-red-500">*</span></label>
                <input type="email" 
                       name="email_pro" 
                       id="email_pro" 
                       value="{{ old('email_pro', $personnel->email_pro ?? '') }}"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email_pro') border-red-500 @enderror">
                @error('email_pro')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="phone_office" class="block text-sm font-medium text-gray-700 mb-1">Téléphone fixe</label>
                <input type="tel" 
                       name="phone_office" 
                       id="phone_office" 
                       value="{{ old('phone_office', $personnel->phone_office ?? '') }}"
                       placeholder="0123456789"
                       pattern="0[1-9][0-9]{8}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Format: 0123456789</p>
            </div>
            
            <div>
                <label for="phone_mobile" class="block text-sm font-medium text-gray-700 mb-1">Téléphone mobile</label>
                <input type="tel" 
                       name="phone_mobile" 
                       id="phone_mobile" 
                       value="{{ old('phone_mobile', $personnel->phone_mobile ?? '') }}"
                       placeholder="0612345678"
                       pattern="0[67][0-9]{8}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Format: 06 ou 07</p>
            </div>
            
            <div>
                <label for="phone_pager" class="block text-sm font-medium text-gray-700 mb-1">Bipeur / Pager</label>
                <input type="text" 
                       name="phone_pager" 
                       id="phone_pager" 
                       value="{{ old('phone_pager', $personnel->phone_pager ?? '') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label for="extension" class="block text-sm font-medium text-gray-700 mb-1">Extension téléphonique</label>
                <input type="text" 
                       name="extension" 
                       id="extension" 
                       value="{{ old('extension', $personnel->extension ?? '') }}"
                       placeholder="1234"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
    </div>
    
    <!-- Compétences et langues -->
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🎓 Compétences et Qualifications</h3>
        
        <div x-data="{ 
            qualifications: {{ json_encode(old('qualifications', $personnel->qualifications ?? [])) }},
            certifications: {{ json_encode(old('certifications', $personnel->certifications ?? [])) }},
            languages: {{ json_encode(old('languages', $personnel->languages ?? [])) }}
        }">
            <!-- Qualifications -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Qualifications / Formations</label>
                <div class="space-y-2">
                    <template x-for="(qual, index) in qualifications" :key="index">
                        <div class="flex gap-2">
                            <input type="text" 
                                   :name="'qualifications[' + index + ']'" 
                                   x-model="qualifications[index]"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" 
                                    @click="qualifications.splice(index, 1)"
                                    class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">
                                ✕
                            </button>
                        </div>
                    </template>
                    <button type="button" 
                            @click="qualifications.push('')"
                            class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                        + Ajouter une qualification
                    </button>
                </div>
            </div>
            
            <!-- Certifications -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Certifications</label>
                <div class="space-y-2">
                    <template x-for="(cert, index) in certifications" :key="index">
                        <div class="flex gap-2">
                            <input type="text" 
                                   :name="'certifications[' + index + ']'" 
                                   x-model="certifications[index]"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" 
                                    @click="certifications.splice(index, 1)"
                                    class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">
                                ✕
                            </button>
                        </div>
                    </template>
                    <button type="button" 
                            @click="certifications.push('')"
                            class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                        + Ajouter une certification
                    </button>
                </div>
            </div>
            
            <!-- Langues -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Langues parlées</label>
                <div class="space-y-2">
                    <template x-for="(lang, index) in languages" :key="index">
                        <div class="flex gap-2">
                            <input type="text" 
                                   :name="'languages[' + index + ']'" 
                                   x-model="languages[index]"
                                   list="common_languages"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" 
                                    @click="languages.splice(index, 1)"
                                    class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">
                                ✕
                            </button>
                        </div>
                    </template>
                    <button type="button" 
                            @click="languages.push('')"
                            class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                        + Ajouter une langue
                    </button>
                </div>
                <datalist id="common_languages">
                    <option value="Français">
                    <option value="Anglais">
                    <option value="Arabe">
                    <option value="Espagnol">
                    <option value="Italien">
                    <option value="Allemand">
                </datalist>
            </div>
        </div>
    </div>
    
    <!-- Boutons d'action -->
    <div class="flex justify-end gap-4">
        <a href="{{ route('annuaire.index') }}" 
           class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
            Annuler
        </a>
        <button type="submit" 
                class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md transition">
            {{ $isEdit ? '✅ Mettre à jour' : '✅ Créer la fiche' }}
        </button>
    </div>
</div>