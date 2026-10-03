<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HandoverLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('handover', $this->route('loan'));
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:60'],
            // Bukti foto serah terima. MIME dibaca dari isi file, bukan dari
            // ekstensi yang dikirim klien (spec §22).
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'photo' => 'bukti foto',
            'barcode' => 'eksemplar',
        ];
    }
}
