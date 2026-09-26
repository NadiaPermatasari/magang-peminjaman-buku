<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.manage');
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required', 'string', 'max:60', 'alpha_dash',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::exists((new Permission)->getTable(), 'name')],
        ];
    }
}
