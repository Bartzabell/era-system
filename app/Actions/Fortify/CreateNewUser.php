<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'          => 'required|string|max:255',
            'first_name'    => 'nullable|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'username'      => 'required|string|max:191|unique:users',
            'email'         => 'nullable|email|unique:users',
            'mobile_no'     => 'nullable|string|max:20',
            'birth_date'    => 'nullable|date',
            'address'       => 'nullable|string',
            'password'      => 'required|string|min:8|confirmed',
        ])->validate();

        return User::create([
            'full_name'   => $input['name'],
            'first_name'  => $input['first_name']  ?? null,
            'middle_name' => $input['middle_name'] ?? null,
            'last_name'   => $input['last_name']   ?? null,
            'username'    => $input['username'],
            'email'       => $input['email']       ?? null,
            'mobile_no'   => $input['mobile_no']   ?? null,
            'birth_date'  => $input['birth_date']  ?? null,
            'address'     => $input['address']     ?? null,
            'barangay_id' => $input['barangay_id'] ?? null,
            'role'        => $input['role']        ?? null,
            'password'    => Hash::make($input['password']),
        ]);
    }
}
