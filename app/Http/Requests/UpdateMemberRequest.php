<?php

namespace App\Http\Requests;

use App\Enums\MemberStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('member'));
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
        ];
    }
}
