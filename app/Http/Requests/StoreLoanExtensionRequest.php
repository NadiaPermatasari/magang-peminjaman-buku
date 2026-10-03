<?php

namespace App\Http\Requests;

use App\Models\LoanExtension;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanExtensionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [LoanExtension::class, $this->route('loan')]);
    }

    public function rules(): array
    {
        return [
            'days' => ['required', 'integer', 'min:1', 'max:'.(int) setting('loan_duration_days', 7)],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'days' => 'jumlah hari perpanjangan',
            'reason' => 'alasan',
        ];
    }
}
