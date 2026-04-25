<?php

namespace App\Http\Requests\Payments;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Payment|null $payment */
        $payment = $this->route('payment');

        return $payment !== null && $this->user()?->can('uploadProof', $payment) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proof.required' => 'Silakan pilih file bukti pembayaran.',
            'proof.file' => 'Bukti pembayaran harus berupa file.',
            'proof.mimes' => 'Format file harus JPG, JPEG, PNG, atau PDF.',
            'proof.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}

