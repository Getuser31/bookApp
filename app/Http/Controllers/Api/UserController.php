<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateUserDataRequest;
use App\Http\Requests\UpdateUserIndexPreferenceRequest;
use App\Http\Requests\UpdateUserLanguagePreference;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\DefaultLanguage;
use App\Models\IndexPreference;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class UserController
{

    public function login(Request $request): JsonResponse
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
            return response()->json([
                'success' => 'login successful',
                'token' => $token], 200);
        } else {
            return Response()->json(['error' => 'Wrong username or password'], 401);
        }
    }

    /**
     * Retrieves the authenticated user's profile details along with related data such as books,
     * average book ranking, and user preferences.
     *
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing user profile details,
     *                                       average book ranking, books started, books not started,
     *                                       index preferences, user preferences, default languages,
     *                                       and books finished.
     */
    public function userProfile()
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
        return Response()->json([
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

    public function UpdateUserData(UpdateUserDataRequest $request): JsonResponse
    {
        $user = Auth::user();
        $user->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Personal data successfully updated!']) ;
    }
}
