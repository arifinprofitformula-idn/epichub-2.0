<?php

namespace App\Http\Controllers;

use App\Actions\Affiliates\ResolveCurrentReferralAction;
use App\Actions\Checkout\CreateGuestCheckoutUserAction;
use App\Actions\Event\CheckEventCheckoutEligibilityAction;
use App\Actions\Orders\CreateDirectOrderAction;
use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function show(
        Request $request,
        Product $product,
        CheckEventCheckoutEligibilityAction $checkEventCheckoutEligibility,
        ResolveCurrentReferralAction $resolveCurrentReferral,
    ): View
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
            'referralInfo' => $resolveCurrentReferral->execute($request),
        ]);
    }

    public function store(
        Request $request,
        Product $product,
        CreateDirectOrderAction $action,
        CreateGuestCheckoutUserAction $createGuestCheckoutUser,
    ): RedirectResponse
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->published()
            ->visiblePublic()
            ->firstOrFail();

        try {
            $guestUserCreated = false;

            $payment = DB::transaction(function () use ($request, $product, $action, $createGuestCheckoutUser, &$guestUserCreated) {
                $user = $request->user();

                if (! $user) {
                    $user = $createGuestCheckoutUser->execute($request->validate([
                        'name' => ['required', 'string', 'max:255'],
                        'email' => ['required', 'string', 'email', 'max:255'],
                        'whatsapp_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s\(\)]+$/'],
                        'password' => ['required'],
                        'password_confirmation' => ['required'],
                    ]), $request);

                    $guestUserCreated = true;
                }

                return [
                    'payment' => $action->execute($user, $product, $request),
                    'user' => $user,
                ];
            });
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'checkout' => $e->getMessage(),
            ])->withInput($request->except(['password', 'password_confirmation']));
        } catch (ValidationException $e) {
            throw $e;
        }

        if ($guestUserCreated) {
            Auth::login($payment['user']);
            $request->session()->regenerate();
        }

        return redirect()->route('payments.show', $payment['payment']);
    }
}

