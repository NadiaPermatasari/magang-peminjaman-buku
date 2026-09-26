<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('loans.create');
    }

    public function rules(): array
    {
        $maxActiveLoans = (int) setting('max_active_loans', 3);

        return [
            'book_ids' => ['required', 'array', 'min:1', 'max:'.$maxActiveLoans],
            'book_ids.*' => [Rule::exists('books', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
