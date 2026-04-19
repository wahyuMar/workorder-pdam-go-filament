<?php

namespace App\Http\Requests\Api\V1\Complaint;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'no_sambungan' => [
                'required',
                'string',
                Rule::exists('customer_numbers', 'no_sambungan')
                    ->where('user_id', $this->user()->id)
                    ->whereNotNull('verified_at'),
            ],
            'complaint_type_id' => [
                'required',
                'integer',
                Rule::exists('complaint_types', 'id')->where('is_active', true),
            ],
            'judul_pengaduan' => ['required', 'string', 'max:255'],
            'isi_pengaduan' => ['required', 'string', 'max:65535'],
            'foto' => ['nullable', 'array', 'max:5'],
            'foto.*' => ['string', 'max:255', 'not_regex:/\.\.|\\\\|\x00/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
