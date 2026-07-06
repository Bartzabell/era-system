<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DutyLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AccountApiController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:255',
            'password' => 'required|min:6',
            'full_name' => 'required|max:255',
            'first_name' => 'nullable|max:255',
            'middle_name' => 'nullable|max:255',
            'last_name' => 'nullable|max:255',
            'email' => 'required|email|unique:users',
            'mobile_no' => 'nullable|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'barangay_id' => 'nullable|exists:barangays,id',
            'role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_no' => $request->mobile_no,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'barangay_id' => $request->barangay_id,
            'role' => $request->role,
        ]);
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            // Delete the user if email fails so they can retry
            $user->delete();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ]
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email not verified',
                'email_verified' => false,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ], 403);
        }

        if ($user->role === 'responder') {
            DutyLog::firstOrCreate(
                ['user_id' => $user->id, 'duty_date' => today()],
                ['checked_in_at' => now()]
            );
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }

    // Get authenticated user
    public function getAccount(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|max:255',
            'first_name' => 'nullable|max:255',
            'middle_name' => 'nullable|max:255',
            'last_name' => 'nullable|max:255',
            'email' => 'nullable|email|unique:users,email,' . $request->user()->id,
            'mobile_no' => 'nullable|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'barangay_id' => 'nullable|exists:barangays,id',
            'role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update([
            'full_name' => $request->full_name,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_no' => $request->mobile_no,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'barangay_id' => $request->barangay_id,
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Update user profile picture
     */
    public function updateProfilePicture(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:5120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Delete old picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store in PUBLIC disk
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
            $user->save();

            // Generate full URL manually
            $appUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
            $profilePictureUrl = $appUrl . '/storage/' . $path;

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'mobile_no' => $user->mobile_no,
                    'role' => $user->role,
                    'profile_picture' => $path,
                    'profile_picture_url' => $profilePictureUrl
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Profile picture upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resendVerification(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email resent'
        ]);
    }

    public function checkVerification(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email not yet verified',
                'email_verified' => false,
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'email_verified' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    public function submitValidId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'valid_id' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Delete old if exists
        if ($user->valid_id && Storage::disk('public')->exists($user->valid_id)) {
            Storage::disk('public')->delete($user->valid_id);
        }

        $path = $request->file('valid_id')->store('valid_ids', 'public');

        $user->update([
            'valid_id' => $path,
            'admin_verified' => null  // reset on re-upload
        ]);

        return response()->json(['success' => true, 'message' => 'ID submitted for review']);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Delete old token first (since email is primary key)
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();
        
        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => $code,
            'created_at' => now()
        ]);

        // Send email
        Mail::raw(
            "Your password reset code is: $code\n\nThis code will expire in 15 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset Code - GEARS');
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Reset code sent to your email'
        ]);
    }

    public function verifyResetToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$reset) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code'
            ], 400);
        }

        // Check if expired (15 minutes)
        if (now()->diffInMinutes($reset->created_at) > 15) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Code expired'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code verified'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$reset || now()->diffInMinutes($reset->created_at) > 15) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
}