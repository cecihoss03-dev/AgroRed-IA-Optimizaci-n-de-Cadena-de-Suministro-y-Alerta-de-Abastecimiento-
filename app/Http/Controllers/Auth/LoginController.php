<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm($role = null)
    {
        return view('auth.login', ['role' => $role]);
    }

    public function login(Request $request, $role = null)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Map requested role to allowed user roles in DB
            $roleMap = [
                'productor' => ['productor'],
                'comerciante' => ['mayorista', 'minorista'],
            ];

            $allowed = $roleMap[$role] ?? [$role];

            if (! in_array(Auth::user()->role, $allowed)) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'No autorizado para este tipo de acceso.',
                ])->onlyInput('email');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
