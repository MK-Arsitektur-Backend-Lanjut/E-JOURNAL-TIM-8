<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExtendSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'days.required' => 'Jumlah hari wajib diisi.',
            'days.integer'  => 'Jumlah hari harus berupa angka.',
            'days.min'      => 'Minimal perpanjangan adalah 1 hari.',
            'days.max'      => 'Maksimal perpanjangan adalah 365 hari.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Input tidak valid.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
