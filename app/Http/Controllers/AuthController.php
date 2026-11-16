<?php

namespace App\Http\Controllers;

use App\Models\UserAccount;
use App\Models\UserToken;
use App\Services\CosmosenderCipherService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('cosmosender_user')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request, CosmosenderCipherService $cipher)
    {
        $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:100',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = UserAccount::where('user_name', $request->input('username'))
            ->where('flag', 'Y')
            ->first();

        if (!$user) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['login' => 'Akun tidak ditemukan atau tidak aktif.']);
        }

        $decryptedPassword = $cipher->decrypt((string) $user->password);

        if ($request->input('password') !== $decryptedPassword) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['login' => 'Password yang Anda masukkan salah.']);
        }

        $tokenStr = $cipher->encrypt($user->id . '_' . $user->user_name . '_' . now()->format('YmdHis'));

        $tokenRecord = UserToken::create([
            'user_id'  => $user->id,
            'token'    => $tokenStr,
            'login_at' => now(),
        ]);

        $request->session()->put('cosmosender_user', [
            'id'        => $user->id,
            'user_name' => $user->user_name,
            'real_name' => $user->real_name ?: ucfirst($user->user_name),
            'token_id'  => $tokenRecord->id,
            'mode'      => $user->isStagingAccount() ? 'staging' : 'live',
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $user = $request->session()->get('cosmosender_user');
        if ($user && !empty($user['token_id'])) {
            UserToken::where('id', $user['token_id'])->update([
                'logout_at' => now(),
            ]);
        }

        $request->session()->forget('cosmosender_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }
}

