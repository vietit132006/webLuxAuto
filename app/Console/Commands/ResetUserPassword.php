<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email} {password}';

    protected $description = 'Đặt lại mật khẩu (plain text; model sẽ hash theo cast)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('Không tìm thấy user với email đó.');

            return self::FAILURE;
        }

        $user->password = $this->argument('password');
        $user->save();

        $this->info('Đã cập nhật mật khẩu cho '.$user->email);

        return self::SUCCESS;
    }
}
