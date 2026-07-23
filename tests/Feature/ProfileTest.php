<?php

namespace Tests\Feature;

use App\Models\Pueblo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile'));

        $response
            ->assertOk()
            ->assertSeeVolt('profile.update-profile-information-form')
            ->assertSeeVolt('profile.update-password-form')
            ->assertSeeVolt('profile.notificaciones-form')
            ->assertSeeVolt('profile.delete-user-form');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $pueblo = Pueblo::create(['nombre' => 'Test Pueblo', 'slug' => 'test-pueblo']);

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('puebloId', $pueblo->id)
            ->set('tema', 'oscuro')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame($pueblo->id, $user->pueblo_id);
        $this->assertSame('oscuro', $user->tema);
    }

    public function test_email_cannot_be_changed_from_the_profile_form(): void
    {
        $user = User::factory()->create(['email' => 'original@example.com']);

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->call('updateProfileInformation');

        $this->assertSame('original@example.com', $user->fresh()->email);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }

    public function test_google_linked_account_can_be_deleted_without_a_password(): void
    {
        $user = User::factory()->create([
            'google_id' => '123456789',
            'password' => Str::password(32),
        ]);

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }
}
