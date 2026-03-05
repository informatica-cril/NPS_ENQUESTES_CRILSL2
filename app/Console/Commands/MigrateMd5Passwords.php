<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MigrateMd5Passwords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passwords:migrate-md5 {--dry-run : Show what would be migrated without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check password hash formats in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking password hash formats in database...');

        $users = User::all();
        $md5Count = 0;
        $bcryptCount = 0;
        $otherCount = 0;

        foreach ($users as $user) {
            $password = $user->password;

            if ($this->isMd5Password($password)) {
                $md5Count++;
                $this->line("MD5: {$user->email}");
            } elseif (password_get_info($password)['algo'] > 0) {
                $bcryptCount++;
            } else {
                $otherCount++;
                $this->warn("Unknown format: {$user->email} - {$password}");
            }
        }

        $this->info("Password format summary:");
        $this->info("- MD5 passwords: {$md5Count}");
        $this->info("- Bcrypt passwords: {$bcryptCount}");
        $this->info("- Other/Unknown: {$otherCount}");

        if ($bcryptCount > 0) {
            $this->warn("Found {$bcryptCount} bcrypt passwords. The system now uses MD5.");
            $this->warn("Consider updating these users to use MD5 or implement dual verification.");
        }

        return Command::SUCCESS;
    }

    /**
     * Check if password hash is MD5 (32 characters, hexadecimal)
     */
    private function isMd5Password(string $hash): bool
    {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }
}