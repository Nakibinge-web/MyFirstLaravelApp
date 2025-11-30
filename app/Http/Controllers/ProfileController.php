<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = auth()->user();
        $user->update($request->validated());

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function destroy()
    {
        $user = auth()->user();
        $userId = $user->id;
        $userName = $user->name;
        
        try {
            // Use database transaction to ensure all-or-nothing deletion
            DB::transaction(function () use ($user) {
                // Delete in correct order to respect foreign key constraints
                // 1. Delete notifications (no dependencies)
                DB::table('notifications')->where('user_id', $user->id)->delete();
                
                // 2. Delete budgets (depends on categories, but categories have restrict)
                DB::table('budgets')->where('user_id', $user->id)->delete();
                
                // 3. Delete transactions (depends on categories, but categories have restrict)
                DB::table('transactions')->where('user_id', $user->id)->delete();
                
                // 4. Delete goals (no dependencies on other tables)
                DB::table('goals')->where('user_id', $user->id)->delete();
                
                // 5. Delete categories (now safe since transactions and budgets are gone)
                DB::table('categories')->where('user_id', $user->id)->delete();
                
                // 6. Finally, delete the user account from the users table
                DB::table('users')->where('id', $user->id)->delete();
            });
            
            // After successful deletion, logout the user
            auth()->logout();
            
            // Invalidate the session
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            
            Log::info('Account deleted successfully for user: ' . $userName . ' (ID: ' . $userId . ')');
            
            return redirect()->route('login')->with('success', 'Your account has been deleted successfully. We\'re sorry to see you go!');
            
        } catch (\Exception $e) {
            // If deletion fails, log the error and show message
            Log::error('Account deletion failed for user ' . $userId . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Failed to delete account. Please try again or contact support. Error: ' . $e->getMessage());
        }
    }
}
