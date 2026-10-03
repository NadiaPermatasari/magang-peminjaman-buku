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
            // Hanya dipakai bila petugas mengajukan atas nama anggota; untuk
            // anggota sendiri field ini diabaikan (lihat LoanController::store).
            'member_id' => [
                $this->user()->can('loans.view-all') && ! $this->user()->member ? 'required' : 'nullable',
                Rule::exists('members', 'id'),
            ],
            'book_ids' => ['required', 'array', 'min:1', 'max:'.$maxActiveLoans],
            'book_ids.*' => [Rule::exists('books', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'member_id' => 'anggota',
            'book_ids' => 'buku',
        ];
    }
}
