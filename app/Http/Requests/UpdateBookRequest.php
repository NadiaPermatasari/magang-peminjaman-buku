<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('book'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Book::uniqueSlug((string) $this->title, $this->route('book')->id),
        ]);
    }

    public function rules(): array
    {
        $book = $this->route('book');

        return [
            'no_hak' => ['nullable', 'string', 'max:50'],
            'no_su' => ['nullable', 'string', 'max:50'],
            'nama_pemilik' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:270', 'alpha_dash', Rule::unique('books', 'slug')->ignore($book->id)],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
