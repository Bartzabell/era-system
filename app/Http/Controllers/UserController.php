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
        $perPage = $request->input('per_page', 10);
        $tab     = $request->input('tab', 'all'); // all | verified | pending

        $query = User::with(['barangay:id,barangay_name', 'permissions', 'responder'])
            ->latest();

        if ($tab === 'verified') {
            $query->where('admin_verified', 'yes');
        } elseif ($tab === 'pending') {
            $query->whereNull('admin_verified');
        }

        $users = $query->paginate($perPage)->withQueryString();

        $barangays   = Barangay::select('id', 'barangay_name')->orderBy('barangay_name')->get();
        $roles       = Role::orderBy('role_name')->get();
        $permissions = Permission::orderBy('name')->get();

        return Inertia::render('User/Index', [
            'users'       => $users,
            'barangays'   => $barangays,
            'roles'       => $roles,
            'permissions' => $permissions,
            'filters'     => $request->only('per_page', 'tab'),
        ]);
    }

    public function edit(User $user)
    {
        return response()->json([
            'user' => $user->load(['barangay', 'permissions', 'responder']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'       => 'required|string|max:255|unique:users',
            'password'       => ['required', 'confirmed', Rules\Password::defaults()],
            'first_name'     => 'nullable|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'last_name'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255|unique:users',
            'mobile_no'      => 'nullable|string|max:255',
            'birth_date'     => 'nullable|date',
            'barangay_id'    => 'nullable|exists:barangays,id',
            'role'           => 'required|string|max:255',
            'permissions'    => 'nullable|array',
            'permissions.*'  => 'exists:permissions,id',
            'is_responder'   => 'boolean',
        ]);

        $user = User::create([
            'username'    => $validated['username'],
            'password'    => Hash::make($validated['password']),
            'full_name'   => trim(implode(' ', array_filter([
                $validated['first_name']  ?? null,
                $validated['middle_name'] ?? null,
                $validated['last_name']   ?? null,
            ]))),
            'first_name'  => $validated['first_name']  ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name'   => $validated['last_name']   ?? null,
            'email'       => $validated['email']       ?? null,
            'mobile_no'   => $validated['mobile_no']   ?? null,
            'birth_date'  => $validated['birth_date']  ?? null,
            'barangay_id' => $validated['barangay_id'] ?? null,
            'role'        => $validated['role'],
        ]);

        if (!empty($validated['permissions'])) {
            $user->permissions()->attach($validated['permissions']);
        }

        if ($request->boolean('is_responder')) {
            Responder::create([
                'user_id'    => $user->id,
                'is_active'  => true,
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username'       => 'required|string|max:255|unique:users,username,' . $user->id,
            'password'       => ['nullable', 'confirmed', Rules\Password::defaults()],
            'first_name'     => 'nullable|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'last_name'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'mobile_no'      => 'nullable|string|max:255',
            'birth_date'     => 'nullable|date',
            'barangay_id'    => 'nullable|exists:barangays,id',
            'role'           => 'required|string|max:255',
            'permissions'    => 'nullable|array',
            'permissions.*'  => 'exists:permissions,id',
            'is_responder'   => 'boolean',
        ]);

        $user->update([
            'username'    => $validated['username'],
            'full_name'   => trim(implode(' ', array_filter([
                $validated['first_name']  ?? null,
                $validated['middle_name'] ?? null,
                $validated['last_name']   ?? null,
            ]))),
            'first_name'  => $validated['first_name']  ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name'   => $validated['last_name']   ?? null,
            'email'       => $validated['email']       ?? null,
            'mobile_no'   => $validated['mobile_no']   ?? null,
            'birth_date'  => $validated['birth_date']  ?? null,
            'barangay_id' => $validated['barangay_id'] ?? null,
            'role'        => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        if (isset($validated['permissions'])) {
            $user->permissions()->sync($validated['permissions']);
        } else {
            $user->permissions()->detach();
        }

        if ($request->boolean('is_responder')) {
            if (!$user->responder) {
                Responder::create([
                    'user_id'    => $user->id,
                    'is_active'  => true,
                    'created_by' => auth()->id(),
                ]);
            }
        } else {
            $user->responder?->delete();
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Verify a user account (set admin_verified = 'yes').
     */
    public function verify(User $user)
    {
        $user->update(['admin_verified' => 'yes']);

        return redirect()->route('users.index')
            ->with('success', 'User account verified successfully.');
    }

    /**
     * Reject / delete a user account pending verification.
     */
    public function rejectVerification(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User account rejected and deleted.');
    }
}
