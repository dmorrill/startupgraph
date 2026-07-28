<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Issue a personal API token for a user. The plaintext token is shown once —
 * it's what agents put in their Authorization: Bearer header for the write
 * API and the hosted MCP endpoint.
 *
 * Usage: php artisan api:token elle@example.com --name=investing-agent
 */
class IssueApiToken extends Command
{
    protected $signature = 'api:token {email : Email of the user to issue the token for}
                            {--name=agent : A label for the token (e.g. which agent will use it)}';

    protected $description = 'Issue a Sanctum API token for a user (for agents and the iOS app)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email {$this->argument('email')}.");

            return self::FAILURE;
        }

        $token = $user->createToken($this->option('name'));

        $this->info('Token created. Shown once — store it now:');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
