<?php

namespace App\Http\Requests;

use App\Models\Rack;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RackRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rack = $this->route('rack');

        return $rack
            ? $this->user()->can('update', $rack)
            : $this->user()->can('create', Rack::class);
    }

    public function rules(): array
    {
        $rack = $this->route('rack');

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('racks', 'code')->ignore($rack?->id)],
            'name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
        ];
    }
}
