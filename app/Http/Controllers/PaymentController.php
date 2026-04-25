<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\UploadPaymentProofRequest;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['order.items.product', 'verifiedBy']);

        return view('payments.show', [
            'payment' => $payment,
        ]);
    }

    public function storeProof(UploadPaymentProofRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('uploadProof', $payment);

        $file = $request->file('proof');
        $path = $file->store('payment-proofs', 'public');

        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }

        $payment->update([
            'proof_of_payment' => $path,
        ]);

        return back();
    }
}

