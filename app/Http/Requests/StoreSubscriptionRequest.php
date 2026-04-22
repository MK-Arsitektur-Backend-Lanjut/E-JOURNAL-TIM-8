<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSubscriptionRequest extends FormRequest
{
    /**
     * Hanya user yang sudah login yang boleh membuat langganan.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Aturan validasi input untuk membuat langganan baru.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', 'in:' . implode(',', config('plans.available'))],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plan.required' => 'Paket langganan wajib dipilih.',
            'plan.in'       => 'Paket langganan tidak valid. Pilih: trial, monthly, atau yearly.',
        ];
    }

    /**
     * Override: Selalu kembalikan JSON saat validasi gagal.
     * Tanpa ini, Laravel mengembalikan redirect HTML yang tidak terbaca di Postman.
     */
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

