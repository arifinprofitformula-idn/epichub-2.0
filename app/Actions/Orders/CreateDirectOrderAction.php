<?php

namespace App\Actions\Orders;

use App\Actions\Affiliates\AttachReferralToOrderAction;
use App\Actions\Event\CheckEventCheckoutEligibilityAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Support\OrderNumberGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class CreateDirectOrderAction
{
    public function __construct(
        protected CheckEventCheckoutEligibilityAction $checkEventCheckoutEligibility,
        protected AttachReferralToOrderAction $attachReferralToOrder,
    ) {
    }

    public function execute(User $user, Product $product, ?Request $request = null): Payment
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->published()
            ->visiblePublic()
            ->firstOrFail();

        $unitPrice = $this->normalizeAmount((float) $product->effective_price);

        if ((float) $unitPrice <= 0) {
            throw new RuntimeException('Produk belum tersedia untuk checkout.');
        }

        $type = $product->product_type instanceof ProductType ? $product->product_type->value : (string) $product->product_type;

        if ($type === ProductType::Event->value) {
            $this->checkEventCheckoutEligibility->execute($product);
        }

        $attempts = 0;

        while (true) {
            $attempts++;

            try {
                return DB::transaction(function () use ($user, $product, $unitPrice, $request): Payment {
                    $orderNumber = OrderNumberGenerator::nextOrderNumber();
                    $paymentNumber = OrderNumberGenerator::nextPaymentNumber();

                    $order = Order::query()->create([
                        'user_id' => $user->id,
                        'order_number' => $orderNumber,
                        'status' => OrderStatus::Unpaid,
                        'subtotal_amount' => $unitPrice,
                        'discount_amount' => $this->normalizeAmount(0),
                        'total_amount' => $unitPrice,
                        'currency' => 'IDR',
                        'customer_name' => $user->name,
                        'customer_email' => $user->email,
                    ]);

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_title' => $product->title,
                        'product_type' => $this->productTypeValue($product),
                        'quantity' => 1,
                        'unit_price' => $unitPrice,
                        'subtotal_amount' => $unitPrice,
                    ]);

                    $payment = $order->payments()->create([
                        'payment_number' => $paymentNumber,
                        'payment_method' => PaymentMethod::ManualBankTransfer,
                        'status' => PaymentStatus::Pending,
                        'amount' => $unitPrice,
                        'currency' => 'IDR',
                    ]);

                    if ($request) {
                        try {
                            $this->attachReferralToOrder->execute($request, $order);
                        } catch (\Throwable $e) {
                            Log::warning('Failed to attach referral to order.', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    return $payment;
                });
            } catch (QueryException $e) {
                if ($attempts >= 3 || ! $this->isUniqueConstraintViolation($e)) {
                    throw $e;
                }
            }
        }
    }

    protected function normalizeAmount(float|int|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    protected function isUniqueConstraintViolation(QueryException $e): bool
    {
        $code = (string) $e->getCode();

        if ($code === '23000') {
            return true;
        }

        $message = Str::lower($e->getMessage());

        return str_contains($message, 'duplicate') || str_contains($message, 'unique');
    }

    protected function productTypeValue(Product $product): string
    {
        $type = $product->product_type;

        if ($type instanceof \BackedEnum) {
            return $type->value;
        }

        return (string) $type;
    }
}

