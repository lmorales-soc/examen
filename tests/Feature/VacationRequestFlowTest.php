<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Employee;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VacationRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'ADMIN', 'guard_name' => 'web']);
        Role::create(['name' => 'HR_MANAGER', 'guard_name' => 'web']);
        Role::create(['name' => 'AREA_MANAGER', 'guard_name' => 'web']);
        Role::create(['name' => 'EMPLOYEE', 'guard_name' => 'web']);
    }

    public function test_vacation_requests_index_requires_authentication(): void
    {
        $response = $this->get(route('vacation-requests.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_vacation_requests_index_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('EMPLOYEE');

        $response = $this->actingAs($user)->get(route('vacation-requests.index'));

        $response->assertStatus(200);
        $response->assertSee('Solicitudes de vacaciones');
    }

    public function test_vacation_requests_create_redirects_when_user_has_no_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('EMPLOYEE');

        $response = $this->actingAs($user)->get(route('vacation-requests.create'));

        $response->assertRedirect(route('vacation-requests.index'));
        $response->assertSessionHas('error');
    }

    public function test_vacation_requests_create_shows_form_when_user_has_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('EMPLOYEE');
        $area = Area::create(['name' => 'Desarrollo', 'slug' => 'desarrollo']);
        Employee::create([
            'user_id' => $user->id,
            'area_id' => $area->id,
            'employee_number' => 'EMP001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'hire_date' => now()->subYears(2),
            'vacation_days_annual' => 14,
            'active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('vacation-requests.create'));

        $response->assertStatus(200);
        $response->assertSee('Solicitar vacaciones');
    }
}
