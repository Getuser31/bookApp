<?php

/**
 * Represents the UserController class in the Laravel application.
 *
 * This class is responsible for handling user-related actions, such as user authentication, registration, account management, and profile display. It interacts with the User model, Role model, Book model, BookRating model, and various request classes.
 *
 * @package App\Http\Controllers
 * @version v11.7.0
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserDataRequest;
use App\Http\Requests\UpdateUserIndexPreferenceRequest;
use App\Http\Requests\UpdateUserLanguagePreference;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\DefaultLanguage;
use App\Models\IndexPreference;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPreference;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 *
 */
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
            $userPreference = UserPreference::getUserPreference($user->id);
            if(!$userPreference){
                $defaultLanguage = DefaultLanguage::first();
                UserPreference::create([
                    'user_id' => $user->id
                ]);
            } else {
                $defaultLanguage = $userPreference->defaultLanguage;
            }
            Session::put('admin', $role);
            Session::put('language', $defaultLanguage->language);

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

    /**
     * Checks the user information.
     *
     * This method retrieves the user information including the associated role based on the given ID and returns a view with the user object.
     *
     * @param int $id The ID of the user to check.
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application Returns a view with the user object.
     * @throws ModelNotFoundException If the user with the given ID cannot be found.
     */
    public function checkUser(int $id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = User::with('role')->find($id);
        return view('User.checkUser', compact('user'));
    }

    /**
     * Deletes a user.
     *
     * This method deletes a user with the specified ID and redirects to the admin.users route.
     *
     * @param int $id The ID of the user to be deleted.
     * @return RedirectResponse Redirects to the admin.users route.
     * @throws ModelNotFoundException If the user with the specified ID cannot be found.
     */
    public function deleteUser(int $id): RedirectResponse
    {
        $user = User::find($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User has been deleted');
    }

    /**
     * Retrieves a list of users.
     *
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application The view instance showing the list of users.
     *      The view instance showing the list of users.
     */
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
     * Updates the user account.
     *
     * This method updates a user's account information based on the given request data.
     *
     * @param UpdateUserRequest $request The request data containing the updates for the user account.
     * @return RedirectResponse Redirects the user to the updateAccount route with a success message.
     * @throws ModelNotFoundException If the user cannot be found.
     */
    public function updateAccountPost(UpdateUserRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $user->update($request->validated());

        return redirect()->route('updateAccount', $user)->with('status', 'User updated successfully!');
    }

    public function UpdateUserData(UpdateUserDataRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $user->update($request->validated());

        return redirect()->route('userProfile', $user)->with('status', 'Personal data successfully updated!');
    }

    /**
     * Registers a new user.
     *
     * This method registers a new user based on the provided user data and returns a redirect response to the book index page.
     *
     * @param StoreUserRequest $request The request containing the validated user data.
     * @return RedirectResponse Returns a redirect response to the book index page.
     * @throws ModelNotFoundException|Exception If the user role cannot be found.
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

    /**
     * Displays the user's profile.
     *
     * This method retrieves the user object from the authentication system and fetches additional information such as the user's books, average book rating, books started, and books not started. It then returns a view with the user's profile information and the fetched data.
     *
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application Returns a view with the user's profile details, including the user's books, average book rating, books started, and books not started.
     * @throws ModelNotFoundException If the user cannot be found.
     */
    public function userProfile(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $user = Auth::user();
        $userWithBooks = User::with('books')->find($user->id);
        $averageRanking = BookRating::getAverageBookRating($user->id);
        $booksStarted = Book::BooksStarted($user->id);
        $booksFinished = Book::BooksFinished($user->id);
        $bookNotStarted = Book::BooksNotStarted($user->id);
        $indexPreferences = IndexPreference::all();
        $defaultLanguages = DefaultLanguage::all();
        $userPreferences = UserPreference::getUserPreference($user->id);
        return view('User.profile', [
            'user' => $userWithBooks,
            'averageRanking' => $averageRanking,
            'bookStarted' => $booksStarted,
            'bookNotStarted' => $bookNotStarted,
            'indexPreferences' => $indexPreferences,
            'userPreferences' => $userPreferences,
            'defaultLanguages' => $defaultLanguages,
            'booksFinished' => $booksFinished
        ]);
    }

    public function updatePassword(UpdateUserPasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $user->update($request->validated());

        return redirect()->route('userProfile', $user)->with('status', 'Password updated successfully!');
    }

    public function updateIndexPreference(UpdateUserIndexPreferenceRequest $request): JsonResponse
    {
        $user = Auth::user();
        $userPreference = UserPreference::getUserPreference($user->id);
        if ($userPreference) {
            $userPreference->update($request->validated());
        } else {
            $language = DefaultLanguage::first();
            $validated = $request->validated();
            UserPreference::create([
                'user_id' => $user->id,
                'index_preference_id' => $validated['index_preference_id'],
                'default_language_id' => $language
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function updateLanguage(UpdateUserLanguagePreference $request): JsonResponse
    {
        $user = Auth::user();
        $userPreference = UserPreference::getUserPreference($user->id);
        $language = $request->validated();
        if ($userPreference) {
            $userPreference->update($language);
        } else {
            UserPreference::create([
                'user_id' => $user->id,
                'default_language_id' => $language
            ]);
        }
        Session::put('language', $userPreference->defaultLanguage->language);

        return response()->json([
            'success' => true,
            'language' => Session::get('language')
        ]);
    }
}
