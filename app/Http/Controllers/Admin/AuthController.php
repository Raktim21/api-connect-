<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        
        $user = User::where('email', $request->email)->first();

        if ($user != null) {
            
            if ($user->status == 0) {
                return back()->with('error', 'Your account is not active');
            }
        }


        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            $user->last_login = Carbon::now();
            $user->save();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);

        // $request->session()->regenerate();

        // return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function registerCreate()
    {
        return view('admin.auth.register');
    }

    public function registerStore(Request $request)
    {
       $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'min:4', 
                'unique:users,email', 
                'regex:/^[\w\.-]+@[\w\.-]+\.(com|net|sa)$/'
            ],
            'password' => ['required', 'confirmed', 'min:6'],
            'port'     => ['required', 'numeric']
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'port' => $request->port,
            'last_login' => Carbon::now(),
        ]);
        Auth::login($user);
        return redirect()->intended(route('admin.dashboard'));
    }


    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
