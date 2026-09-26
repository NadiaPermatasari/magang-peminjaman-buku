<?php

namespace App\Http\Requests;

use App\Models\Publisher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublisherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $publisher = $this->route('publisher');

        return $publisher
            ? $this->user()->can('update', $publisher)
            : $this->user()->can('create', Publisher::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->slug ?: $this->name),
        ]);
    }

    public function rules(): array
    {
        $publisher = $this->route('publisher');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:170', 'alpha_dash', Rule::unique('publishers', 'slug')->ignore($publisher?->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
