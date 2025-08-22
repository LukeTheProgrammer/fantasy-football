<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create 
                            {--email= : Email address for the user}
                            {--password= : Password for the user}
                            {--name= : Name for the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user with a Sanctum token';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?? config('user.default.email');
        $password = $this->option('password') ?? config('user.default.password');
        $name = $this->option('name') ?? config('user.default.name');

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->info("User with email '{$email}' already exists.");
            $this->info('Generating new token for existing user...');
        } else {
            // Create new user
            $userData = [
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
            ];

            // Add email_verified_at if the field is fillable
            if (in_array('email_verified_at', (new User)->getFillable())) {
                $userData['email_verified_at'] = now();
            }

            $user = User::create($userData);

            $this->info("User '{$name}' created successfully with email '{$email}'.");
        }

        // Generate Sanctum token
        $token = $user->createToken('api-token');

        $this->info('');
        $this->info('User Details:');
        $this->info("ID: {$user->id}");
        $this->info("Name: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info('');
        $this->info('Sanctum Token:');
        $this->line($token->plainTextToken);

        return Command::SUCCESS;
    }
}
