<?php

namespace App\Http\Middleware;

use App\Models\UserToken;
use Closure;
use Illuminate\Support\Facades\View;

class CosmosenderAuth
{
    /**
     * Batas waktu idle (tanpa aktivitas) = 15 menit (900 detik).
     */
    private const IDLE_TIMEOUT_SECONDS = 900;

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

        $lastActivity = (int) $request->session()->get('cosmosender_last_activity', 0);

        if ($lastActivity > 0 && (time() - $lastActivity) > self::IDLE_TIMEOUT_SECONDS) {
            if (!empty($user['token_id'])) {
                UserToken::where('id', $user['token_id'])->update([
                    'logout_at' => now(),
                ]);
            }

            $request->session()->forget(['cosmosender_user', 'cosmosender_last_activity']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $timeoutMsg = 'Sesi Anda telah berakhir otomatis karena tidak ada aktivitas selama 15 menit.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'code'    => 401,
                    'message' => $timeoutMsg,
                ], 401);
            }

            return redirect()->route('login')->withErrors(['login' => $timeoutMsg]);
        }

        // Perbarui waktu aktivitas terakhir (termasuk saat request AJAX pengiriman Blast berjalan)
        $request->session()->put('cosmosender_last_activity', time());

        $isStagingMode = strtolower((string) ($user['user_name'] ?? '')) === 'dev';

        View::share('currentUser', $user);
        View::share('isStagingMode', $isStagingMode);

        return $next($request);
    }
}
