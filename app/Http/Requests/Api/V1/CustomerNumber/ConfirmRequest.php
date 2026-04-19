<?php

namespace App\Http\Requests\Api\V1\CustomerNumber;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRequest extends FormRequest
{
    protected $hidden = ['nik'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'no_sambungan' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'nik' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
        ];
    }
}
