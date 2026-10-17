<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Book::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Book::uniqueSlug((string) $this->title),
        ]);
    }

    public function rules(): array
    {
        return [
            'no_hak' => ['nullable', 'string', 'max:50'],
            'no_su' => ['nullable', 'string', 'max:50'],
            'nama_pemilik' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:270', 'alpha_dash', Rule::unique('books', 'slug')],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            // Real MIME sniffed from file content, not the client-supplied extension (spec §22).
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
