<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
            'slug' => Str::slug($this->slug ?: $this->title),
        ]);
    }

    public function rules(): array
    {
        return [
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:270', 'alpha_dash', Rule::unique('books', 'slug')],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'rack_id' => ['nullable', Rule::exists('racks', 'id')],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'edition' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:40'],
            'page_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            // Real MIME sniffed from file content, not the client-supplied extension (spec §22).
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
