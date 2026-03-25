<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function create()
    {
        $barangays = Barangay::select('id', 'barangay_name')
            ->orderBy('barangay_name')
            ->get();

        // Add a dd() temporarily to confirm data exists
        // dd($barangays);

        return Inertia::render('auth/Register', [
            'barangays' => $barangays,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'             => 'required|string|max:255|unique:users',
            'password'             => ['required', 'confirmed', Rules\Password::defaults()],
            'first_name'           => 'nullable|string|max:255',
            'middle_name'          => 'nullable|string|max:255',
            'last_name'            => 'nullable|string|max:255',
            'email'                => 'required|email|max:255|unique:users',
            'mobile_no'            => 'nullable|string|max:20',
            'birth_date'           => 'nullable|date',
            'address'              => 'nullable|string|max:500',
            'barangay_id'          => 'nullable|exists:barangays,id',
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
            'email'       => $validated['email'],
            'mobile_no'   => $validated['mobile_no']   ?? null,
            'birth_date'  => $validated['birth_date']  ?? null,
            'address'     => $validated['address']     ?? null,
            'barangay_id' => $validated['barangay_id'] ?? null,
            'role'        => 'citizen',
        ]);

        $permissionId = Permission::where('slug', 'citizen_access')->pluck('id')->first();
        $user->permissions()->sync([$permissionId]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('incident-report.index'));
    }
}
