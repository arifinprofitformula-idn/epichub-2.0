<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\UploadPaymentProofRequest;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function proof(Payment $payment): View|RedirectResponse
    {
        $this->authorize('view', $payment);

        if (! $payment->proof_of_payment) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Bukti pembayaran belum tersedia.');
        }

        $path = (string) $payment->proof_of_payment;
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        $proofKind = match ($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'pdf' => 'pdf',
            default => 'file',
        };

        return view('payments.proof', [
            'payment' => $payment->load(['order', 'verifiedBy']),
            'proofUrl' => Storage::disk('public')->url($path),
            'proofKind' => $proofKind,
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

