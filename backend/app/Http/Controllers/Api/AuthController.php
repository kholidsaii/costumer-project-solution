<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // --- 0. AMBIL MASTER TIER ---
    public function getTiers()
    {
        $tiers = Tier::select('id', 'name', 'slug', 'description')->get();
        return response()->json($tiers, 200);
    }

    // --- 1. REGISTER MANUAL ---
    public function register(Request $request) 
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            // HAPUS validasi 'tier_id' di sini
        ]);

        // Cari master tier 'free' dari database
        $freeTier = Tier::where('slug', 'free')->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            // Assign otomatis ke tier free (jika ada di DB), jika tidak null
            'tier_id' => $freeTier ? $freeTier->id : null, 
        ]);

        $user->load('tier'); // Ambil data tier yang berelasi
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'tier_slug' => $user->tier->slug ?? 'free',
                'tier_name' => $user->tier->name ?? 'Free Member'
            ]
        ], 201);
    }

    // --- 2. LOGIN MANUAL ---
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        // PERBAIKAN: Selalu muat relasi tier saat login
        $user = User::with('tier')->where('email', $request->email)->firstOrFail();

        // Jika user lama belum punya tier_id, berikan default 'free'
        if (!$user->tier_id) {
            $freeTier = Tier::where('slug', 'free')->first();
            if($freeTier) {
                $user->update(['tier_id' => $freeTier->id]);
                $user->load('tier'); // Refresh relasi
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                // PERBAIKAN: Gunakan ternary operator untuk menghindari error null
                'tier_slug' => $user->tier ? $user->tier->slug : 'free',
                'tier_name' => $user->tier ? $user->tier->name : 'Free Member',
            ],
        ]);
    }

    // --- 3. LOGOUT ---
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil'], 200);
    }

    // --- 4. REDIRECT KE GOOGLE ---
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account']) 
            ->redirect();
    }

    // --- 5. CALLBACK GOOGLE OAUTH ---
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::with('tier')->where('email', $googleUser->getEmail())->first();
            $freeTier = Tier::where('slug', 'free')->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'role' => 'customer',
                    'tier_id' => $freeTier ? $freeTier->id : null,
                    'password' => null,
                ]);
            } else {
                if (!$user->tier_id && $freeTier) {
                    $user->tier_id = $freeTier->id;
                }
                $user->google_id = $googleUser->getId();
                $user->save();
            }

            $user->load('tier');
            $token = $user->createToken('auth_token')->plainTextToken;
            
            $tierSlug = $user->tier->slug ?? 'free';
            $tierName = urlencode($user->tier->name ?? 'Free Member');
            $nameParam = urlencode($user->name);

            return redirect("http://localhost:5173/customer/login?token={$token}&tier_slug={$tierSlug}&tier_name={$tierName}&role={$user->role}&name={$nameParam}");
        } catch (\Exception $e) {
            return redirect("http://localhost:5173/customer/login?error=oauth_failed");
        }
    }

    // --- 6. KIRIM LINK RESET PASSWORD ---
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::send([], [], function ($message) use ($request) {
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

        $resetRequest = DB::table('password_reset_tokens')->where([
            'email' => $request->email,
            'token' => $request->token
        ])->first();

        if (!$resetRequest) {
            return response()->json(['message' => 'Token tidak valid atau sudah kadaluarsa.'], 400);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();

        return response()->json(['message' => 'Password berhasil diubah. Silakan login.']);
    }
    
    // [ADMIN] Lihat daftar customer
    public function adminCustomers()
    {
        $customers = User::with('tier')->where('role', 'customer')->latest()->get();
        return response()->json($customers, 200);
    }
}