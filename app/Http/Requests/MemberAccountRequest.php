<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Membuat akun login untuk anggota yang belum punya, atau mengatur ulang
 * kata sandinya. Dipakai dari halaman Ubah Anggota.
 */
class MemberAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('member'));
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return ['password' => 'kata sandi'];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $member = $this->route('member');

            if ($member->user) {
                return;
            }

            // Akun baru butuh email, dan email itu belum boleh dipakai user lain.
            if (! $member->email) {
                $validator->errors()->add('password', 'Lengkapi dulu email anggota ini sebelum membuat akun login.');
            } elseif (User::withTrashed()->where('email', $member->email)->exists()) {
                $validator->errors()->add('password', 'Email anggota ini sudah digunakan oleh akun lain.');
            }
        });
    }
}
