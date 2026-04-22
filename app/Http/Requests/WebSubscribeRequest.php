<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * WebSubscribeRequest — Validasi berlangganan dari form web (Blade).
 *
 * Berbeda dari StoreSubscriptionRequest (API) yang memaksa return JSON,
 * request ini menggunakan perilaku standar Laravel yaitu redirect back
 * dengan pesan error ketika validasi gagal.
 */
class WebSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', 'in:' . implode(',', config('plans.available'))],
        ];
    }

    public function messages(): array
    {
        return [
            'plan.required' => 'Paket langganan wajib dipilih.',
            'plan.in'       => 'Paket tidak valid. Pilih: trial, monthly, atau yearly.',
        ];
    }
}
