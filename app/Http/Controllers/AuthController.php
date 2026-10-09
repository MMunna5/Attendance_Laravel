<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Minimal login/logout to match the source app's single combined
 * teacher/admin login page (registration/login.html). If you install
 * Laravel Breeze/Fortify instead, replace this with their generated
 * controllers and keep only the Blade view's teacher/admin toggle UI.
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['username' => 'Invalid username or password.'])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password', ['error' => null, 'success' => false]);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        $old = $request->input('old_password', '');
        $new = $request->input('new_password', '');
        $confirm = $request->input('confirm_password', '');

        $error = null;
        $success = false;

        if (!\Illuminate\Support\Facades\Hash::check($old, $user->password)) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 4) {
            $error = 'New password must be at least 4 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($new)]);
            $success = true;
        }

        return view('auth.change-password', compact('error', 'success'));
    }
}
