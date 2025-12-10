<?php

namespace Tests\Feature\Annuaire;

use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests fonctionnels pour le module Annuaire du Personnel
 */
class PersonnelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;

    /**
     * Configuration avant chaque test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur normal
        $this->user = User::factory()->create();
        
        // Créer un administrateur
        $this->admin = User::factory()->create();
        
        // Note: Les permissions doivent être configurées via le seeder RolePermissionSeeder
    }

    /**
     * Test: Afficher la liste du personnel
     */
    public function test_peut_afficher_liste_personnel(): void
    {
        // Créer quelques fiches personnel
        Personnel::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->get(route('annuaire.index'));

        $response->assertStatus(200);
        $response->assertViewIs('annuaire.index');
        $response->assertViewHas('personnel');
    }

    /**
     * Test: Rechercher du personnel par nom
     */
    public function test_peut_rechercher_personnel_par_nom(): void
    {
        Personnel::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'job_title' => 'Médecin',
            'department' => 'Dialyse',
            'employment_type' => 'full_time',
            'email_pro' => 'jean.dupont@test.fr',
            'hire_date' => '2020-01-01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('annuaire.index', ['search' => 'Dupont']));

        $response->assertStatus(200);
        $response->assertSee('Dupont');
    }

    /**
     * Test: Afficher une fiche détaillée
     */
    public function test_peut_afficher_fiche_personnel(): void
    {
        $personnel = Personnel::factory()->create([
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'job_title' => 'Infirmier',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('annuaire.show', $personnel));

        $response->assertStatus(200);
        $response->assertViewIs('annuaire.show');
        $response->assertSee('Marie Martin');
        $response->assertSee('Infirmier');
    }

    /**
     * Test: Créer une fiche personnel (nécessite permission)
     */
    public function test_admin_peut_creer_fiche_personnel(): void
    {
        Storage::fake('public');

        $data = [
            'first_name' => 'Sophie',
            'last_name' => 'Bernard',
            'job_title' => 'Cadre de Santé',
            'department' => 'Dialyse',
            'employment_type' => 'full_time',
            'email_pro' => 'sophie.bernard@test.fr',
            'phone_mobile' => '0612345678',
            'hire_date' => '2020-06-01',
            'is_active' => true,
            'qualifications' => ['IDE', 'Master Management'],
            'languages' => ['Français', 'Anglais'],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('annuaire.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('personnel', [
            'first_name' => 'Sophie',
            'last_name' => 'Bernard',
            'email_pro' => 'sophie.bernard@test.fr',
        ]);
    }

    /**
     * Test: Créer une fiche avec photo
     */
    public function test_peut_creer_fiche_avec_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('photo.jpg', 400, 400);

        $data = [
            'first_name' => 'Luc',
            'last_name' => 'Petit',
            'job_title' => 'Technicien',
            'department' => 'Dialyse',
            'employment_type' => 'full_time',
            'email_pro' => 'luc.petit@test.fr',
            'hire_date' => '2021-01-01',
            'is_active' => true,
            'photo' => $photo,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('annuaire.store'), $data);

        $response->assertRedirect();
        
        $personnel = Personnel::where('email_pro', 'luc.petit@test.fr')->first();
        $this->assertNotNull($personnel->photo_url);
    }

    /**
     * Test: Valider les données obligatoires
     */
    public function test_validation_donnees_obligatoires(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('annuaire.store'), []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'job_title',
            'department',
            'email_pro',
            'hire_date',
        ]);
    }

    /**
     * Test: Valider le format de l'email
     */
    public function test_validation_format_email(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'job_title' => 'Test',
            'department' => 'Test',
            'employment_type' => 'full_time',
            'email_pro' => 'email-invalide',
            'hire_date' => '2020-01-01',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('annuaire.store'), $data);

        $response->assertSessionHasErrors('email_pro');
    }

    /**
     * Test: Modifier une fiche personnel
     */
    public function test_peut_modifier_fiche_personnel(): void
    {
        $personnel = Personnel::factory()->create([
            'first_name' => 'Original',
            'email_pro' => 'original@test.fr',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('annuaire.update', $personnel), [
                'first_name' => 'Modifié',
                'last_name' => $personnel->last_name,
                'job_title' => $personnel->job_title,
                'department' => $personnel->department,
                'employment_type' => $personnel->employment_type,
                'email_pro' => 'modifie@test.fr',
                'hire_date' => $personnel->hire_date->format('Y-m-d'),
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('personnel', [
            'id' => $personnel->id,
            'first_name' => 'Modifié',
            'email_pro' => 'modifie@test.fr',
        ]);
    }

    /**
     * Test: Archiver un personnel (soft delete)
     */
    public function test_admin_peut_archiver_personnel(): void
    {
        $personnel = Personnel::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('annuaire.destroy', $personnel));

        $response->assertRedirect(route('annuaire.index'));
        
        // Vérifier que le personnel est désactivé
        $this->assertDatabaseHas('personnel', [
            'id' => $personnel->id,
            'is_active' => false,
        ]);
        
        // Vérifier le soft delete
        $this->assertSoftDeleted('personnel', [
            'id' => $personnel->id,
        ]);
    }

    /**
     * Test: API - Recherche de personnel
     */
    public function test_api_recherche_personnel(): void
    {
        Personnel::factory()->create([
            'first_name' => 'Alexandre',
            'last_name' => 'Durand',
            'job_title' => 'Infirmier',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/recherche/personnel?q=Alexandre');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'nom_complet',
                    'fonction',
                    'service',
                ],
            ],
            'count',
        ]);
    }

    /**
     * Test: API - Autocomplete
     */
    public function test_api_autocomplete(): void
    {
        Personnel::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/recherche/personnel/autocomplete?q=test');

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    /**
     * Test: API - Personnel disponible
     */
    public function test_api_personnel_disponible(): void
    {
        Personnel::factory()->count(5)->create(['is_active' => true]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/recherche/personnel/disponibles');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'date',
            'count',
        ]);
    }

    /**
     * Test: Export CSV
     */
    public function test_peut_exporter_csv(): void
    {
        Personnel::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->get(route('annuaire.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test: Filtrer par service
     */
    public function test_peut_filtrer_par_service(): void
    {
        Personnel::factory()->create(['department' => 'Dialyse']);
        Personnel::factory()->create(['department' => 'Urgences']);

        $response = $this->actingAs($this->user)
            ->get(route('annuaire.index', ['service' => 'Dialyse']));

        $response->assertStatus(200);
        $response->assertSee('Dialyse');
    }

    /**
     * Test: Accès non autorisé
     */
    public function test_utilisateur_non_authentifie_ne_peut_pas_acceder(): void
    {
        $response = $this->get(route('annuaire.index'));

        $response->assertRedirect(route('login'));
    }
}