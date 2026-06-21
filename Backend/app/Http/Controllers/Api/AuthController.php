<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntrepreneurDetail;
use App\Models\InvestorDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:entrepreneur,investor',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($validated['name']) . '&background=random',
                'bio' => '',
                'is_online' => true,
            ]);

            if ($validated['role'] === 'entrepreneur') {
                EntrepreneurDetail::create(['user_id' => $user->id, 'startup_name' => '']);
            } else {
                InvestorDetail::create(['user_id' => $user->id]);
            }

            return $user;
        });

        Auth::login($user);

        // Using the session() helper instead of $request->session() —
        // it resolves the session store from the container, which is
        // always bound once StartSession has run, regardless of which
        // $request instance the controller method received.
        session()->regenerate();

        return response()->json(['user' => $user->load(['entrepreneurDetails', 'investorDetails'])->toFrontendArray()]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|in:entrepreneur,investor',
        ]);

        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or user not found'],
            ]);
        }

        $user = Auth::user();

        if ($user->role !== $credentials['role']) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ["This account is registered as a(n) {$user->role}, not {$credentials['role']}."],
            ]);
        }

        session()->regenerate();
        $user->update(['is_online' => true]);

        return response()->json(['user' => $user->load(['entrepreneurDetails', 'investorDetails'])->toFrontendArray()]);
    }

    public function logout(Request $request)
    {
        $request->user()?->update(['is_online' => false]);

        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['entrepreneurDetails', 'investorDetails'])->toFrontendArray(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['currentPassword'], $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($validated['newPassword'])]);

        return response()->json(['message' => 'Password updated successfully']);
    }
}