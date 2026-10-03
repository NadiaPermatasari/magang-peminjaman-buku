<?php

namespace App\Http\Requests;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Member::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'identity_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', new Enum(MemberStatus::class)],
            'joined_at' => ['required', 'date'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:joined_at'],
            'notes' => ['nullable', 'string', 'max:500'],
            'create_login' => ['nullable', 'boolean'],
            // Password diisi langsung oleh petugas: server email belum tentu
            // tersedia, jadi anggota harus bisa langsung login dengan
            // kredensial yang diserahkan petugas.
            'password' => [$this->boolean('create_login') ? 'required' : 'nullable', 'string', Password::default(), 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'create_login' => 'akun login',
            'password' => 'kata sandi',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->boolean('create_login')) {
                return;
            }

            if (! $this->filled('email')) {
                $validator->errors()->add('email', 'Email wajib diisi untuk membuat akun login.');
            } elseif (User::withTrashed()->where('email', $this->input('email'))->exists()) {
                $validator->errors()->add('email', 'Email ini sudah digunakan oleh akun lain.');
            }
        });
    }
}
