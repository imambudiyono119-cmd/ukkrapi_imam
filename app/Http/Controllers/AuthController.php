<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('status', 'Jika email terdaftar, tautan dan kode OTP untuk mengatur ulang kata sandi akan dikirim.');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Password::broker()->createToken($user);

        DB::table('password_reset_tokens')->where('email', $user->email)->update([
            'otp' => $otp,
        ]);

        try {
            $user->sendPasswordResetNotification($token);
            $message = 'Kode OTP dan tautan reset telah dikirim ke email Anda. Silakan periksa email dan masukkan kode OTP di bawah.';
        } catch (\Throwable $e) {
            Log::error('SMTP Error sending password reset notification: '.$e->getMessage());
            $message = 'Kode OTP telah dibuat: '.$otp.' (SMTP lokal belum terhubung, kode ditampilkan untuk pengujian).';
        }

        return redirect()->route('password.reset', ['email' => $user->email])
            ->with('status', $message);
    }

    public function showResetPassword(Request $request, ?string $token = null): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $token ?? $request->query('token', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $inputToken = trim($credentials['token']);
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Akun dengan email ini tidak ditemukan.']);
        }

        // Cek apakah token yang dimasukkan adalah kode OTP 6 digit
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $isOtpValid = false;

        if ($record && ! empty($record->otp) && $record->otp === $inputToken) {
            $isNotExpired = Carbon::parse($record->created_at)->addMinutes(60)->isFuture();
            if ($isNotExpired) {
                $isOtpValid = true;
            }
        }

        if ($isOtpValid) {
            $user->forceFill([
                'password' => Hash::make($credentials['password']),
            ])->setRememberToken(Str::random(60));

            $user->save();

            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            event(new PasswordReset($user));

            return redirect()->route('login')->with('status', 'Kata sandi berhasil diatur ulang. Silakan masuk.');
        }

        // Verifikasi menggunakan broker standar Laravel jika token bukan OTP plain
        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PasswordReset) {
            return redirect()->route('login')->with('status', 'Kata sandi berhasil diatur ulang. Silakan masuk.');
        }

        return back()->withErrors(['email' => 'Kode OTP atau tautan reset tidak valid atau sudah kedaluwarsa.']);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => ['Password salah bos.'],
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function profile()
    {
        return view('auth.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'regex:/^62[0-9]{9,15}$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->fullname = $validated['fullname'];
        $user->phone_number = $validated['phone_number'] ?? $user->phone_number;

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
