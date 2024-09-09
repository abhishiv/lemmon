<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Services\OrderService;
use App\Http\Services\StaffCartService;
use App\View\Components\Staff\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class CartController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function add(Request $request): string
    {
        $service = new StaffCartService();
        $service->add($request);

        return Blade::renderComponent(new Cart());
    }

    public function store(Request $request): JsonResponse
    {
        $order = $this->orderService->storeStaffOrder($request);

        $service = new StaffCartService();
        $service->empty();

        return response()->json([
            'success' => true,
            'order' => $order?->id ?? null,
        ]);
    }

    public function update(Request $request): string
    {
        $service = new StaffCartService();
        $service->update($request);

        return Blade::renderComponent(new Cart());
    }

    public function empty(Request $request): string
    {
        $service = new StaffCartService();
        $service->empty();

        return Blade::renderComponent(new Cart());
    }

    public function setOptions(Request $request)
    {
        $service = new StaffCartService();

        if (isset($request->clear_all)) {
            $service->clearOptions();

            return response()->json(['success' => true]);
        }

        if (isset($request->table)) {
            $service->setTable($request->table);

            return response()->json(['success' => true]);
        }

        if (isset($request->tips)) {
            $service->setTips($request->tips);

            return response()->json([
                'success' => true,
                'totals' => $service->getTotals(),
            ]);
        }

        if (isset($request->discount)) {
            $service->setDiscount($request->discount);

            return response()->json([
                'success' => true,
                'totals' => $service->getTotals(),
            ]);
        }
    }

    public function addNotes(Request $request)
    {

        if (!$request->notes) {
            return response()->json([
                'success' => false,
            ]);
        }

        $service = new StaffCartService();
        $service->addNotes($request->notes);

        return Blade::renderComponent(new Cart());
    }

    public function getTotals()
    {
        $service = new StaffCartService();

        return response()->json([
            'success' => true,
            'totals' => $service->getTotals(),
        ]);
    }
}
