<?php

namespace App\Http\Requests;

use App\Enums\BookCondition;
use App\Enums\LoanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ReturnLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('returns.process');
    }

    public function rules(): array
    {
        return [
            // Eksemplar dipilih dari daftar peminjaman aktif — barcode scanner
            // diganti bukti foto (lihat ReturnController).
            'loan_item_id' => [
                'required',
                Rule::exists('loan_items', 'id')->whereIn('status', [LoanStatus::BORROWED->value, LoanStatus::OVERDUE->value]),
            ],
            'condition' => ['required', new Enum(BookCondition::class)],
            // Bukti foto wajib: inilah pengganti verifikasi barcode.
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'loan_item_id' => 'eksemplar yang dikembalikan',
            'photo' => 'bukti foto',
            'condition' => 'kondisi buku',
        ];
    }
}
