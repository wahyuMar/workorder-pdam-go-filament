<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\CustomerRegistrations\Pages\ListCustomerRegistrations;
use App\Filament\Resources\CustomerRegistrations\Pages\ViewCustomerRegistration;
use App\Models\CustomerRegistration;
use App\Models\User;
use Livewire\Livewire;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class CustomerRegistrationSourceFilterTest extends TestCase
{
    use RefreshDatabaseCompat;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_source_column_renders_in_table(): void
    {
        $manual = CustomerRegistration::factory()->create(['source' => 'manual']);
        $mobile = CustomerRegistration::factory()->create(['source' => 'mobile']);

        Livewire::test(ListCustomerRegistrations::class)
            ->assertCanSeeTableRecords([$manual, $mobile])
            ->assertTableColumnExists('source');
    }

    public function test_can_filter_by_source_manual(): void
    {
        $manual = CustomerRegistration::factory()->create(['source' => 'manual']);
        $mobile = CustomerRegistration::factory()->create(['source' => 'mobile']);

        Livewire::test(ListCustomerRegistrations::class)
            ->filterTable('source', 'manual')
            ->assertCanSeeTableRecords([$manual])
            ->assertCanNotSeeTableRecords([$mobile]);
    }

    public function test_can_filter_by_source_mobile(): void
    {
        $manual = CustomerRegistration::factory()->create(['source' => 'manual']);
        $mobile = CustomerRegistration::factory()->create(['source' => 'mobile']);

        Livewire::test(ListCustomerRegistrations::class)
            ->filterTable('source', 'mobile')
            ->assertCanSeeTableRecords([$mobile])
            ->assertCanNotSeeTableRecords([$manual]);
    }

    public function test_source_displays_in_infolist(): void
    {
        $registration = CustomerRegistration::factory()->create(['source' => 'mobile']);

        Livewire::test(ViewCustomerRegistration::class, [
            'record' => $registration->getRouteKey(),
        ])
            ->assertSuccessful()
            ->assertSeeText('mobile');
    }
}
