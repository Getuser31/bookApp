<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserController
{
    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function login(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('User.login');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function loginPost(Request $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
                // Authentication passed...
                $user = Auth::user();
                $role = $user->checkAdmin();
            Session::put('admin', $role);
            return redirect()->route('book.index');
            } else {
            return redirect()->back()->withErrors([
                    'email' => 'Invalid email',
                    'password' => 'Invalid password',
                ]);
        }
    }

    /**
     * Logs out the currently authenticated user.
     *
     * @return \Illuminate\Http\RedirectResponse The redirect response.
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();
        Session::flush();
        return redirect()->intended('/');
    }
}
