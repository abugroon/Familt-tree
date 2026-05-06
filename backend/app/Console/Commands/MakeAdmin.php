<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature   = 'user:make-admin {email}';
    protected $description = 'Grant admin privileges to a user by email';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("User [{$this->argument('email')}] not found.");
            return self::FAILURE;
        }

        $user->update(['is_admin' => true]);
        $this->info("User [{$user->name}] is now an admin.");
        return self::SUCCESS;
    }
}
