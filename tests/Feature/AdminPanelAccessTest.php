<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filament denies panel access outside local unless the User model implements
 * FilamentUser::canAccessPanel(). Missing it meant /admin returned 403 on the
 * live site while working fine locally, so this pins the production behaviour.
 */
class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_user_can_open_the_panel_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['app.env' => 'production']);

        $user = User::create([
            'name'     => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('irrelevant-for-this-test'),
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertSuccessful();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
