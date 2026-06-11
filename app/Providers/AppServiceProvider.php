<?php

namespace App\Providers;

use App\Http\Controllers\AccountSwitchController;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer(['layouts.admin', 'layouts.site'], function ($view) {
            $currentUser = Auth::user();
            $switcherId = session(AccountSwitchController::SESSION_SWITCHER_ID);
            $accountSwitcher = $switcherId ? User::select('user_id', 'name', 'email', 'role')->find($switcherId) : null;
            $quickSwitchUsers = collect();

            if ($currentUser && in_array($currentUser->role, ['admin', 'staff'], true)) {
                $quickSwitchUsers = User::select('user_id', 'name', 'email', 'role')
                    ->where('user_id', '!=', $currentUser->getKey())
                    ->where('status', true)
                    ->when($currentUser->role !== 'admin', function ($query) {
                        $query->where('role', 'customer');
                    })
                    ->orderBy('name')
                    ->limit(30)
                    ->get();
            }

            $view->with([
                'accountSwitcher' => $accountSwitcher,
                'isAccountSwitching' => (bool) $switcherId,
                'quickSwitchUsers' => $quickSwitchUsers,
            ]);
        });
    }
}
