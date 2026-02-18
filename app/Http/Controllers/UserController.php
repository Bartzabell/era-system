<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barangay;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Responder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['barangay', 'permissions', 'responder'])
            ->orderBy('created_at', 'desc')
            ->get();

        $barangays = Barangay::orderBy('barangay_name')->get();
        $roles = Role::orderBy('role_name')->get();
        $permissions = Permission::orderBy('name')->get();

        return Inertia::render('User/Index', [
            'users' => $users,
            'barangays' => $barangays,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function edit(User $user)
    {
        return response()->json([
            'user' => $user->load(['barangay', 'permissions', 'responder'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'full_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users',
            'mobile_no' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'barangay_id' => 'nullable|exists:barangays,id',
            'role' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'is_responder' => 'boolean',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'full_name' => $validated['full_name'] ?? null,
            'first_name' => $validated['first_name'] ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'mobile_no' => $validated['mobile_no'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'barangay_id' => $validated['barangay_id'] ?? null,
            'role' => $validated['role'],
        ]);

        // Attach permissions
        if (!empty($validated['permissions'])) {
            $user->permissions()->attach($validated['permissions']);
        }

        // Create responder record if needed
        if ($request->boolean('is_responder')) {
            Responder::create([
                'user_id' => $user->id,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'full_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'mobile_no' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'barangay_id' => 'nullable|exists:barangays,id',
            'role' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'is_responder' => 'boolean',
        ]);

        $user->update([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'] ?? null,
            'first_name' => $validated['first_name'] ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'mobile_no' => $validated['mobile_no'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'barangay_id' => $validated['barangay_id'] ?? null,
            'role' => $validated['role'],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // Sync permissions
        if (isset($validated['permissions'])) {
            $user->permissions()->sync($validated['permissions']);
        } else {
            $user->permissions()->detach();
        }

        // Handle responder status
        if ($request->boolean('is_responder')) {
            if (!$user->responder) {
                Responder::create([
                    'user_id' => $user->id,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
            }
        } else {
            if ($user->responder) {
                $user->responder->delete();
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Optionally prevent deletion of current user
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
