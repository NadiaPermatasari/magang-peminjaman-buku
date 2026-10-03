<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadHandoverPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('uploadHandoverPhoto', $this->route('loan'));
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return ['photo' => 'bukti foto'];
    }
}
