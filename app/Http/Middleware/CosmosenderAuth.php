<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;

class CosmosenderAuth
{
    public function handle($request, Closure $next)
    {
        $user = $request->session()->get('cosmosender_user');

        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'code'    => 401,
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }

            return redirect()->route('login');
        }

        $isStagingMode = strtolower((string) ($user['user_name'] ?? '')) === 'dev';

        View::share('currentUser', $user);
        View::share('isStagingMode', $isStagingMode);

        return $next($request);
    }
}

