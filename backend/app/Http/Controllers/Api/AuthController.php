<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // --- 1. REGISTER MANUAL (DIPERSINGKAT) ---
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed', 
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'user' => $user,
            'access_token' => $token,
        ], 201);
    }

    // --- 2. LOGIN MANUAL ---
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // --- 3. REDIRECT KE GOOGLE ---
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account']) // TAMBAHKAN BARIS INI
            ->redirect();
    }

    // --- 4. TANGKAP CALLBACK DARI GOOGLE ---
    public function handleGoogleCallback()
    {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Cek apakah user sudah ada berdasarkan email atau google_id
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Jika belum ada, buat user baru (Register via Google)
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'role' => 'customer',
                    'password' => null, // Tidak butuh password
                ]);
            } else {
                // Jika sudah ada, update google_id nya
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Buat token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // Arahkan ke halaman login Vue dengan membawa token
            return redirect("http://localhost:5173/customer/login?token=" . $token);
    }

    // --- 5. LOGOUT ---
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil!']);
    }

    // --- 6. KIRIM LINK RESET PASSWORD ---
    // --- 6. KIRIM LINK RESET PASSWORD ---
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        // --- KODE YANG DIUBAH (DINAMIS) ---
        // 1. Deteksi asal request (Origin) dari browser pengguna
        $origin = $request->header('origin');
        
        // 2. Jika Origin terbaca, gunakan itu. Jika tidak, ambil dari .env
        $frontendUrl = $origin ? $origin : env('FRONTEND_URL', 'http://localhost:5173');
        
        // 3. Buat link
        $resetLink = $frontendUrl . '/customer/reset-password?token=' . $token . '&email=' . urlencode($request->email);
        // ---------------------------------
        
        Mail::raw("Klik link berikut untuk mereset password Anda: \n\n" . $resetLink, function ($message) use ($request) {
            $message->to($request->email)->subject('Reset Password Kerjapro Solutions');
        });

        return response()->json(['message' => 'Link reset password telah dikirim ke email Anda.']);
    }

    // --- 7. PROSES RESET PASSWORD ---
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Cek token valid atau tidak
        $resetRequest = DB::table('password_reset_tokens')->where([
            'email' => $request->email,
            'token' => $request->token
        ])->first();

        if (!$resetRequest) {
            return response()->json(['message' => 'Token tidak valid atau sudah kadaluarsa.'], 400);
        }

        // Update password baru
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus token setelah dipakai
        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();

        return response()->json(['message' => 'Password berhasil diubah. Silakan login.']);
    }
}