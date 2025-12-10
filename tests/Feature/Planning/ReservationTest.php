<?php

namespace Tests\Feature\Planning;

use App\Models\Reservation;
use App\Models\Salle;
use App\Models\Personnel;
use App\Models\User;
use App\Enums\StatutReservationEnum;
use App\Enums\TypeDialyseEnum;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests automatisés pour le module Planning
 * 
 * Teste les fonctionnalités critiques du planning
 */
class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Salle $salle;
    protected Personnel $personnel;

    /**
     * Configuration avant chaque test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur avec permissions
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['planning.view', 'planning.create', 'planning.edit', 'planning.delete']);

        // Créer une salle de test
        $this->salle = Salle::create([
            'name' => 'Salle Test',
            'code' => 'TEST-01',
            'capacity' => 4,
            'is_isolation' => false,
            'is_active' => true,
        ]);

        // Créer du personnel de test
        $this->personnel = Personnel::factory()->create([
            'is_active' => true,
        ]);
    }

    /**
     * Test: Créer une réservation valide
     */
    public function test_can_create_valid_reservation(): void
    {
        $startTime = now()->addDay()->setTime(10, 0);
        $endTime = $startTime->copy()->addHours(4);

        $response = $this->actingAs($this->user)->post(route('planning.store'), [
            'salle_id' => $this->salle->id,
            'patient_reference' => 'PAT-2024-001',
            'patient_initials' => 'J.D.',
            'type_dialyse' => TypeDialyseEnum::HEMODIALYSIS->value,
            'date_debut' => $startTime->format('Y-m-d H:i:s'),
            'date_fin' => $endTime->format('Y-m-d H:i:s'),
            'personnel_ids' => [$this->personnel->id],
            'notes' => 'Test de création',
        ]);

        $response->assertRedirect(route('planning.index'));
        $this->assertDatabaseHas('reservations', [
            'salle_id' => $this->salle->id,
            'patient_reference' => 'PAT-2024-001',
            'status' => StatutReservationEnum::SCHEDULED->value,
        ]);
    }

    /**
     * Test: Détecter conflit de salle occupée
     */
    public function test_detects_room_conflict(): void
    {
        // Créer une première réservation
        $startTime = now()->addDay()->setTime(10, 0);
        $endTime = $startTime->copy()->addHours(4);

        Reservation::create([
            'salle_id' => $this->salle->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'patient_reference' => 'PAT-2024-001',
            'dialysis_type' => TypeDialyseEnum::HEMODIALYSIS,
            'status' => StatutReservationEnum::SCHEDULED,
            'created_by' => $this->user->id,
        ]);

        // Tenter de créer une réservation en conflit
        $response = $this->actingAs($this->user)->post(route('planning.store'), [
            'salle_id' => $this->salle->id,
            'patient_reference' => 'PAT-2024-002',
            'type_dialyse' => TypeDialyseEnum::HEMODIALYSIS->value,
            'date_debut' => $startTime->copy()->addHour()->format('Y-m-d H:i:s'),
            'date_fin' => $endTime->copy()->addHour()->format('Y-m-d H:i:s'),
            'personnel_ids' => [$this->personnel->id],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('reservations', [
            'patient_reference' => 'PAT-2024-002',
        ]);
    }

    /**
     * Test: Détecter conflit de personnel indisponible
     */
    public function test_detects_personnel_conflict(): void
    {
        $startTime = now()->addDay()->setTime(10, 0);
        $endTime = $startTime->copy()->addHours(4);

        // Créer une première réservation avec ce personnel
        $reservation1 = Reservation::create([
            'salle_id' => $this->salle->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'patient_reference' => 'PAT-2024-001',
            'dialysis_type' => TypeDialyseEnum::HEMODIALYSIS,
            'status' => StatutReservationEnum::SCHEDULED,
            'created_by' => $this->user->id,
        ]);
        $reservation1->personnel()->attach($this->personnel->id);

        // Créer une autre salle
        $salle2 = Salle::create([
            'name' => 'Salle Test 2',
            'code' => 'TEST-02',
            'capacity' => 4,
            'is_active' => true,
        ]);

        // Tenter de créer une réservation avec le même personnel
        $response = $this->actingAs($this->user)->post(route('planning.store'), [
            'salle_id' => $salle2->id,
            'patient_reference' => 'PAT-2024-002',
            'type_dialyse' => TypeDialyseEnum::HEMODIALYSIS->value,
            'date_debut' => $startTime->copy()->addHour()->format('Y-m-d H:i:s'),
            'date_fin' => $endTime->copy()->addHour()->format('Y-m-d H:i:s'),
            'personnel_ids' => [$this->personnel->id],
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test: Drag & drop - déplacer une réservation
     */
    public function test_can_move_reservation_via_drag_drop(): void
    {
        $reservation = Reservation::create([
            'salle_id' => $this->salle->id,
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(14, 0),
            'patient_reference' => 'PAT-2024-001',
            'dialysis_type' => TypeDialyseEnum::HEMODIALYSIS,
            'status' => StatutReservationEnum::SCHEDULED,
            'created_by' => $this->user->id,
        ]);

        $newStartTime = now()->addDays(2)->setTime(11, 0);
        $newEndTime = now()->addDays(2)->setTime(15, 0);

        $response = $this->actingAs($this->user)->postJson(
            route('planning.move', $reservation),
            [
                'date_debut' => $newStartTime->toIso8601String(),
                'date_fin' => $newEndTime->toIso8601String(),
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $reservation->refresh();
        $this->assertEquals($newStartTime->format('Y-m-d H:i'), $reservation->start_time->format('Y-m-d H:i'));
    }

    /**
     * Test: Vérifier durée minimale selon type dialyse
     */
    public function test_validates_minimum_duration(): void
    {
        $startTime = now()->addDay()->setTime(10, 0);
        $endTime = $startTime->copy()->addHour(); // 1h seulement pour hémodialyse

        $response = $this->actingAs($this->user)->post(route('planning.store'), [
            'salle_id' => $this->salle->id,
            'patient_reference' => 'PAT-2024-001',
            'type_dialyse' => TypeDialyseEnum::HEMODIALYSIS->value,
            'date_debut' => $startTime->format('Y-m-d H:i:s'),
            'date_fin' => $endTime->format('Y-m-d H:i:s'),
            'personnel_ids' => [$this->personnel->id],
        ]);

        $response->assertSessionHasErrors('date_fin');
    }

    /**
     * Test: Annuler une réservation avec motif
     */
    public function test_can_cancel_reservation_with_reason(): void
    {
        $reservation = Reservation::create([
            'salle_id' => $this->salle->id,
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(14, 0),
            'patient_reference' => 'PAT-2024-001',
            'dialysis_type' => TypeDialyseEnum::HEMODIALYSIS,
            'status' => StatutReservationEnum::SCHEDULED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('planning.destroy', $reservation),
            ['cancellation_reason' => 'Patient indisponible pour raisons médicales']
        );

        $response->assertRedirect(route('planning.index'));

        $reservation->refresh();
        $this->assertEquals(StatutReservationEnum::CANCELLED, $reservation->status);
        $this->assertNotNull($reservation->cancelled_at);
        $this->assertEquals('Patient indisponible pour raisons médicales', $reservation->cancellation_reason);
    }

    /**
     * Test: Ne peut pas modifier une réservation terminée
     */
    public function test_cannot_edit_completed_reservation(): void
    {
        $reservation = Reservation::create([
            'salle_id' => $this->salle->id,
            'start_time' => now()->subDay()->setTime(10, 0),
            'end_time' => now()->subDay()->setTime(14, 0),
            'patient_reference' => 'PAT-2024-001',
            'dialysis_type' => TypeDialyseEnum::HEMODIALYSIS,
            'status' => StatutReservationEnum::COMPLETED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put(
            route('planning.update', $reservation),
            [
                'salle_id' => $this->salle->id,
                'patient_reference' => 'PAT-2024-001',
                'type_dialyse' => TypeDialyseEnum::HEMODIALYSIS->value,
                'date_debut' => now()->addDay()->setTime(10, 0)->format('Y-m-d H:i:s'),
                'date_fin' => now()->addDay()->setTime(14, 0)->format('Y-m-d H:i:s'),
                'personnel_ids' => [$this->personnel->id],
            ]
        );

        $response->assertForbidden();
    }

    /**
     * Test: API - Liste des réservations avec filtres
     */
    public function test_api_lists_reservations_with_filters(): void
    {
        Reservation::create([
            'salle_id' => $this->salle->id,
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(14, 0),
            'patient_reference' => 'PAT-2024-001',
            'dialysis_type' => TypeDialyseEnum::HEMODIALYSIS,
            'status' => StatutReservationEnum::SCHEDULED,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            route('api.reservations.index', [
                'salle_id' => $this->salle->id,
                'type_dialyse' => TypeDialyseEnum::HEMODIALYSIS->value,
            ])
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'patient',
                    'salle',
                    'planning',
                    'type_dialyse',
                    'statut',
                ],
            ],
            'meta',
        ]);
    }

    /**
     * Test: Vérifier isolement requis avec salle compatible
     */
    public function test_validates_isolation_room_compatibility(): void
    {
        $startTime = now()->addDay()->setTime(10, 0);
        $endTime = $startTime->copy()->addHours(4);

        // Tenter de réserver avec isolement dans une salle non-isolement
        $response = $this->actingAs($this->user)->post(route('planning.store'), [
            'salle_id' => $this->salle->id,
            'patient_reference' => 'PAT-2024-001',
            'type_dialyse' => TypeDialyseEnum::HEMODIALYSIS->value,
            'date_debut' => $startTime->format('Y-m-d H:i:s'),
            'date_fin' => $endTime->format('Y-m-d H:i:s'),
            'personnel_ids' => [$this->personnel->id],
            'isolement_requis' => true,
        ]);

        $response->assertSessionHasErrors('isolement_requis');
    }
}