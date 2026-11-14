<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'CR-001',
            'name' => 'Budi Agung',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'level' => 2,
            'is_active' => true,
            'vehicle_type' => 'motorcycle',
            'license_plate' => 'L 1234 AB',
            'address' => 'Surabaya',
        ], $overrides);
    }

    public function test_store_persists_courier(): void
    {
        $this->postJson('/api/couriers', $this->payload())
            ->assertCreated()->assertJsonPath('data.name', 'Budi Agung');
        $this->assertDatabaseHas('couriers', ['code' => 'CR-001', 'level' => 2]);
    }

    public function test_store_rejects_invalid_and_duplicate_data(): void
    {
        Courier::factory()->create(['code' => 'CR-001', 'phone' => '081234567890']);
        $this->postJson('/api/couriers', $this->payload(['level' => 6, 'email' => 'not-email']))
            ->assertUnprocessable()->assertJsonValidationErrors(['code', 'phone', 'email', 'level']);
        $this->assertDatabaseCount('couriers', 1);
    }

    public function test_update_persists_changes_and_allows_own_unique_values(): void
    {
        $courier = Courier::factory()->create(['code' => 'CR-001', 'phone' => '081234567890']);
        $this->patchJson("/api/couriers/{$courier->id}", [
            'code' => 'CR-001', 'phone' => '081234567890', 'name' => 'Budi Baru', 'level' => 5,
        ])->assertOk()->assertJsonPath('data.level', 5);
        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'name' => 'Budi Baru', 'level' => 5]);
    }

    public function test_update_rejects_invalid_level_and_duplicate_phone(): void
    {
        $first = Courier::factory()->create();
        $second = Courier::factory()->create();
        $this->patchJson("/api/couriers/{$first->id}", ['level' => 0, 'phone' => $second->phone])
            ->assertUnprocessable()->assertJsonValidationErrors(['level', 'phone']);
        $this->assertDatabaseHas('couriers', ['id' => $first->id, 'phone' => $first->phone]);
    }

    public function test_show_returns_every_courier_field(): void
    {
        $courier = Courier::factory()->create(['notes' => 'Priority']);
        $this->getJson("/api/couriers/{$courier->id}")
            ->assertOk()->assertJsonPath('data.notes', 'Priority')
            ->assertJsonStructure(['data' => ['id', 'code', 'name', 'phone', 'email', 'level',
                'is_active', 'vehicle_type', 'license_plate', 'address', 'notes',
                'registered_at', 'created_at', 'updated_at']]);
    }

    public function test_destroy_removes_record(): void
    {
        $courier = Courier::factory()->create();
        $this->deleteJson("/api/couriers/{$courier->id}")->assertOk();
        $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
        $this->getJson("/api/couriers/{$courier->id}")->assertNotFound();
    }

    public function test_index_defaults_to_name_ascending_with_pagination(): void
    {
        Courier::factory()->create(['name' => 'Zaki']);
        Courier::factory()->create(['name' => 'Andi']);
        Courier::factory()->create(['name' => 'Budi']);
        $this->getJson('/api/couriers?per_page=2&page=1')
            ->assertOk()->assertJsonPath('data.0.name', 'Andi')
            ->assertJsonPath('data.1.name', 'Budi')
            ->assertJsonPath('total', 3)->assertJsonPath('last_page', 2);
    }

    public function test_index_can_override_sort_by_registration_date(): void
    {
        Courier::factory()->create(['name' => 'Zaki', 'registered_at' => '2026-01-02 00:00:00']);
        Courier::factory()->create(['name' => 'Andi', 'registered_at' => '2026-02-02 00:00:00']);
        $this->getJson('/api/couriers?sort=registered_at')
            ->assertOk()->assertJsonPath('data.0.name', 'Andi');
    }

    public function test_index_search_matches_non_adjacent_partial_name_terms(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Budi Santoso']);
        $this->getJson('/api/couriers?search=budi+agung')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    public function test_index_filter_supports_multiple_levels_and_combined_parameters(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung', 'level' => 2]);
        Courier::factory()->create(['name' => 'Budiman Agung', 'level' => 3]);
        Courier::factory()->create(['name' => 'Budiono Agung', 'level' => 4]);
        $this->getJson('/api/couriers?search=budi+agung&level=2,3&per_page=10')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('total', 2);
    }

    public function test_index_rejects_unsafe_sort_and_invalid_filter(): void
    {
        $this->getJson('/api/couriers?sort=password&level=2,9&per_page=500')
            ->assertUnprocessable()->assertJsonValidationErrors(['sort', 'level', 'per_page']);
    }
}
