<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VacationCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'ADMIN', 'guard_name' => 'web']);
        Role::create(['name' => 'EMPLOYEE', 'guard_name' => 'web']);
    }

    public function test_calendar_page_requires_authentication(): void
    {
        $response = $this->get(route('vacation-requests.calendar'));

        $response->assertRedirect(route('login'));
    }

    public function test_calendar_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('EMPLOYEE');

        $response = $this->actingAs($user)->get(route('vacation-requests.calendar'));

        $response->assertStatus(200);
        $response->assertSee('Calendario de vacaciones');
        $response->assertSee('vacation-calendar', false);
    }

    public function test_calendar_events_endpoint_returns_json(): void
    {
        $user = User::factory()->create();
        $user->assignRole('ADMIN');

        $response = $this->actingAs($user)->getJson(route('vacation-requests.calendar.events') . '?start=2026-03-01&end=2026-03-31');

        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }
}
