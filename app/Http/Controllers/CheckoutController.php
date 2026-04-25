<?php

namespace App\Http\Controllers;

use App\Actions\Affiliates\AttachReferralToOrderAction;
use App\Actions\Event\CheckEventCheckoutEligibilityAction;
use App\Actions\Orders\CreateDirectOrderAction;
use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function show(Product $product, CheckEventCheckoutEligibilityAction $checkEventCheckoutEligibility): View
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->published()
            ->visiblePublic()
            ->firstOrFail();

        $unitPrice = (float) $product->effective_price;
        $isEligible = $unitPrice > 0;
        $eligibilityMessage = $isEligible ? null : 'Produk ini belum tersedia untuk checkout saat ini.';

        $type = $product->product_type instanceof ProductType ? $product->product_type->value : (string) $product->product_type;

        if ($isEligible && $type === ProductType::Event->value) {
            try {
                $checkEventCheckoutEligibility->execute($product);
            } catch (RuntimeException $e) {
                $isEligible = false;
                $eligibilityMessage = $e->getMessage();
            }
        }

        return view('checkout.show', [
            'product' => $product,
            'isEligible' => $isEligible,
            'eligibilityMessage' => $eligibilityMessage,
        ]);
    }

    public function store(Request $request, Product $product, CreateDirectOrderAction $action, AttachReferralToOrderAction $attachReferralToOrder): RedirectResponse
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->published()
            ->visiblePublic()
            ->firstOrFail();

        try {
            $payment = $action->execute($request->user(), $product);
            $attachReferralToOrder->execute($request, $payment->order);
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'checkout' => $e->getMessage(),
            ]);
        }

        return redirect()->route('payments.show', $payment);
    }
}

