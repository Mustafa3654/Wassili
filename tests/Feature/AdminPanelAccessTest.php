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

    /**
     * Categories and sub-categories are two screens over one model, and the
     * profile page is customised to edit a username instead of an email — all
     * three are easy to break with a bad scope or a missing page class.
     */
    public function test_admin_screens_render(): void
    {
        $user = User::create([
            'name' => 'Admin', 'username' => 'admin', 'password' => bcrypt('irrelevant'),
        ]);

        $screens = [
            '/admin/categories', '/admin/menu-sections', '/admin/profile',
            '/admin/drivers', '/admin/drivers/create',
            '/admin/vendors', '/admin/products', '/admin/orders',
            '/admin/manage-settings',
        ];

        foreach ($screens as $url) {
            $this->actingAs($user)->get($url)->assertSuccessful();
        }
    }

    public function test_profile_page_edits_username_not_email(): void
    {
        $user = User::create([
            'name' => 'Admin', 'username' => 'admin', 'password' => bcrypt('irrelevant'),
        ]);

        $this->actingAs($user)
            ->get('/admin/profile')
            ->assertSuccessful()
            ->assertSee('username');
    }
}
