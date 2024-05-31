<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController
{
    public function login(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('User.login');
    }

    public function loginPost(Request $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
                // Authentication passed...
                return redirect()->intended('book.index');
            } else {
            return redirect()->back()->withErrors([
                    'email' => 'Invalid email',
                    'password' => 'Invalid password',
                ]);
        }
    }
}
