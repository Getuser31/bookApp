<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

            // Create Sanctum Token
            $deviceName = $request->userAgent(); // Or a custom device identifier
            $abilities = ['*']; // Grant all permissions or customize
            $token = $user->createToken($deviceName, $abilities)->plainTextToken;

            // Store Token in Session
            Session::put('api_token', $token);

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
     * @return RedirectResponse The redirect response.
     */
    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();
        Session::flush();
        // Invalidate the session to ensure all session data is removed
        request()->session()->invalidate();
        // Generate a new session token to prevent session fixation attacks
        request()->session()->regenerateToken();
        return redirect()->intended('/');
    }

    public function createAccount(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('User.createAccount');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $validatedData['role_id'],
        ]);

        return redirect()->route('checkUser', ['id' => $user->id]);
    }

    public function checkUser(int $id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = User::with('role')->find($id);
        return view('User.checkUser', compact('user'));
    }

    public function deleteUser(int $id): RedirectResponse
    {
        $user = User::find($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User has been deleted');
    }

    public function listOfUsers(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $users = User::paginate(10);
        return view('User.listOfUsers', compact('users'));
    }

    /**
     * Updates the user account.
     *
     * This method updates a user's account information and returns a view with the updated user object and roles.
     *
     * @return View|Application|Factory|RedirectResponse|\Illuminate\Contracts\Foundation\Application Returns a view with the updated user object and roles.
     * @throws ModelNotFoundException If the user cannot be found.
     */
    public function updateAccount(): View|Application|Factory|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $roles = Role::all();
        $user = Auth::user();

        return view('User.updateAccount', compact('user'), compact('roles'));
    }

    /**
     * @throws Exception
     */
    public function updateAccountPost(UpdateUserRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $user->update($request->validated());

        return redirect()->route('updateAccount', $user)->with('status', 'User updated successfully!');
    }

    /**
     * @throws Exception
     */
    public function register(StoreUserRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        if (!isset($validatedData['role_id'])) {
            $role_id = Role::getUserRole();
            $validatedData['role_id'] = $role_id;
        }

        $user = User::storeFromRequest($validatedData);
        Auth::login($user);

        return redirect()->route('book.index');
    }

    public function userProfile(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = Auth::user();
        $userWithBooks = User::with('books')->find($user->id);
        $averageRanking = BookRating::getAverageBookRating($user->id);
        $booksStarted = Book::BooksStarted($user->id);
        $bookNotStarted = Book::BooksNotStarted($user->id);
        return view('User.profile', [
            'user' => $userWithBooks,
            'averageRanking' => $averageRanking,
            'bookStarted' => $booksStarted,
            'bookNotStarted' => $bookNotStarted
        ]);
    }
}
