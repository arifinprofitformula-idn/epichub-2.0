<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Access\ResolveProductDeliveryAction;
use App\Enums\AccessLogAction;
use App\Enums\ProductType;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MyProductController extends Controller
{
    public function __construct(
        protected ResolveProductDeliveryAction $resolveProductDelivery,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function index(Request $request): View
    {
        $userProducts = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->with(['product', 'order', 'sourceProduct'])
            ->latest('granted_at')
            ->paginate(12);

        return view('my-products.index', [
            'userProducts' => $userProducts,
        ]);
    }

    public function show(Request $request, UserProduct $userProduct): View
    {
        if (Gate::denies('view', $userProduct)) {
            $this->logAccess->execute(
                action: AccessLogAction::AccessDenied,
                user: $request->user(),
                userProduct: $userProduct,
                actor: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                metadata: [
                    'reason' => 'policy_denied',
                ],
            );

            abort(403);
        }

        $delivery = $this->resolveProductDelivery->execute($userProduct);

        $type = $delivery['type'];

        $logAction = $type === ProductType::Bundle->value
            ? AccessLogAction::BundleAccessed
            : AccessLogAction::AccessViewed;

        $this->logAccess->execute(
            action: $logAction,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return view('my-products.show', [
            'userProduct' => $userProduct,
            'delivery' => $delivery,
        ]);
    }
}

