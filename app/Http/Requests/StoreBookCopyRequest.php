<?php

namespace App\Http\Requests;

use App\Enums\BookCondition;
use App\Enums\BookCopyStatus;
use App\Models\BookCopy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreBookCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', BookCopy::class);
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required', Rule::exists('books', 'id')],
            'barcode' => ['required', 'string', 'max:60', Rule::unique('book_copies', 'barcode')],
            'inventory_code' => ['nullable', 'string', 'max:60'],
            'acquisition_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:150'],
            'condition' => ['required', new Enum(BookCondition::class)],
            'status' => ['required', new Enum(BookCopyStatus::class)],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
