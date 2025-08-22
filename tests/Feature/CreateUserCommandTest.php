<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_user_with_default_values(): void
    {
        $this->artisan('user:create')
            ->expectsOutput("User 'Test User' created successfully with email 'user@test.com'.")
            ->expectsOutput('User Details:')
            ->expectsOutput('ID: 1')
            ->expectsOutput('Name: Test User')
            ->expectsOutput('Email: user@test.com')
            ->expectsOutput('Sanctum Token:')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name'  => 'Test User',
            'email' => 'user@test.com',
        ]);

        $user = User::where('email', 'user@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password', $user->password));

        // Check email_verified_at only if the field is fillable
        if (in_array('email_verified_at', (new User)->getFillable())) {
            $this->assertNotNull($user->email_verified_at);
        }
    }

    public function test_command_creates_user_with_custom_values(): void
    {
        $this->artisan('user:create', [
            '--email'    => 'admin@example.com',
            '--password' => 'secret123',
            '--name'     => 'Admin User',
        ])
            ->expectsOutput("User 'Admin User' created successfully with email 'admin@example.com'.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_command_returns_token_for_existing_user(): void
    {
        // Create user first
        $user = User::factory()->create([
            'email' => 'user@test.com',
        ]);

        // Run command again
        $this->artisan('user:create')
            ->expectsOutput("User with email 'user@test.com' already exists.")
            ->expectsOutput('Generating new token for existing user...')
            ->assertExitCode(0);

        // Should have created a new token
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id'   => $user->id,
        ]);
    }

    public function test_command_generates_sanctum_token(): void
    {
        $this->artisan('user:create')->assertExitCode(0);

        $user = User::where('email', 'user@test.com')->first();
        $this->assertNotNull($user);

        // Check that a token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id'   => $user->id,
        ]);
    }

    public function test_command_uses_config_defaults(): void
    {
        // Test that the command uses config values when no options provided
        config(['user.default.email' => 'config@test.com']);
        config(['user.default.password' => 'configpass']);
        config(['user.default.name' => 'Config User']);

        $this->artisan('user:create')
            ->expectsOutput("User 'Config User' created successfully with email 'config@test.com'.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name'  => 'Config User',
            'email' => 'config@test.com',
        ]);
    }
}
