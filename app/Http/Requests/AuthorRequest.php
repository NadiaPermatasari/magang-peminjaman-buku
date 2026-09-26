<?php

namespace App\Http\Requests;

use App\Models\Author;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $author = $this->route('author');

        return $author
            ? $this->user()->can('update', $author)
            : $this->user()->can('create', Author::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->slug ?: $this->name),
        ]);
    }

    public function rules(): array
    {
        $author = $this->route('author');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:170', 'alpha_dash', Rule::unique('authors', 'slug')->ignore($author?->id)],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
