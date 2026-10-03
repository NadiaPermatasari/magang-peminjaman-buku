<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Dipakai untuk setujui maupun tolak. Catatan opsional saat menyetujui,
 * wajib saat menolak — aturannya ditentukan oleh route yang dipanggil.
 */
class DecideLoanExtensionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('decide', $this->route('extension'));
    }

    public function rules(): array
    {
        $isRejection = $this->routeIs('loan-extensions.reject');

        return [
            'note' => [$isRejection ? 'required' : 'nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'note' => $this->routeIs('loan-extensions.reject') ? 'alasan penolakan' : 'catatan',
        ];
    }
}
