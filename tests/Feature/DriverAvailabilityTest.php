<?php

namespace Tests\Feature;

use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DriverAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function driver(array $attrs = []): Driver
    {
        return Driver::create(array_merge([
            'name' => 'Ali', 'phone' => '71000000',
            'vehicle_type' => 'motorcycle', 'status' => 'available', 'is_active' => true,
        ], $attrs));
    }

    /** No shift configured must not hide a driver from dispatch. */
    public function test_driver_without_hours_is_on_shift(): void
    {
        $this->assertTrue($this->driver()->is_on_shift);
        $this->assertSame('available', $this->driver()->availability);
    }

    public function test_driver_is_off_shift_outside_working_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-06 23:30')); // Thursday night

        $driver = $this->driver(['working_hours' => [
            'thursday' => ['is_open' => true, 'open' => '09:00', 'close' => '17:00'],
        ]]);

        $this->assertFalse($driver->is_on_shift);
        $this->assertSame('off_shift', $driver->availability);
        $this->assertFalse($driver->isDispatchable());

        Carbon::setTestNow();
    }

    public function test_driver_is_on_shift_inside_working_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-06 12:00'));

        $driver = $this->driver(['working_hours' => [
            'thursday' => ['is_open' => true, 'open' => '09:00', 'close' => '17:00'],
        ]]);

        $this->assertTrue($driver->is_on_shift);
        $this->assertSame('available', $driver->availability);

        Carbon::setTestNow();
    }

    /** A manual Busy overrides the shift — dispatchers need that override. */
    public function test_busy_beats_being_on_shift(): void
    {
        $driver = $this->driver(['status' => 'busy']);

        $this->assertSame('busy', $driver->availability);
        $this->assertFalse($driver->isDispatchable());
    }

    public function test_available_now_excludes_off_shift_drivers(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-06 23:30'));

        $this->driver(['name' => 'OnCall']);
        $this->driver(['name' => 'Day', 'working_hours' => [
            'thursday' => ['is_open' => true, 'open' => '09:00', 'close' => '17:00'],
        ]]);

        $names = Driver::availableNow()->pluck('name')->all();

        $this->assertContains('OnCall', $names);
        $this->assertNotContains('Day', $names);

        Carbon::setTestNow();
    }

    public function test_delivery_fee_override_is_optional(): void
    {
        $this->assertNull($this->driver()->overrideFee());
        $this->assertSame(3.5, $this->driver(['delivery_fee' => 3.5])->overrideFee());
    }
}
