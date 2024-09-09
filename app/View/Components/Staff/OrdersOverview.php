<?php

namespace App\View\Components\Staff;

use App\Models\Order;
use App\Models\Restaurant;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Product;
use Illuminate\View\Component;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\ChiSquared;

class OrdersOverview extends Component
{
    public string $status;

    public array $overview = [];

    public int $count;

    public $barNewOrderCount = null;

    public $restaurantNewOrderCount = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($status)
    {
        $this->status = $status;

        $this->count = Order::where('status', Order::NEW)->whereNotNull('display_id')->count();

        // If set to old, show no summary, only return count
        if($this->status == 'closed') {
            return;
        }

        $orders = Order::with('items.products.categories')->whereIn('status', [Order::NEW]);       
 
        $this->barNewOrderCount = $this->getNewOrderCountByType(clone $orders, 'bar');
        $this->restaurantNewOrderCount = $this->getNewOrderCountByType(clone $orders, 'restaurant');

        foreach ($orders->get() as $order) {
            foreach ($order->items as $item) {
                if(!$item->products || $item->products->categories()->count() == 0) {
                    continue;
                }

                $category = $item->products->categories()->first()->name;

                if (array_key_exists($category, $this->overview) && array_key_exists($item->products->name, $this->overview[$category]['products'])){
                    $this->overview[$category]['products'][$item->products->name] += $item->quantity;
                }else{
                    $this->overview[$category]['type'] = $item->products->type;
                    $this->overview[$category]['products'][$item->products->name] = $item->quantity;
                }
            }
        }
    }

    private function getNewOrderCountByType($orders, $type)
    {
        $standaloneCount = (clone $orders)->where($type . '_status', Order::NEW)
            ->whereNull('parent_id')
            ->whereHas('items.products', function(Builder $query) use ($type) {
                $query->where('type', $type);
            })
            ->get()
            ->count();

        $childCount = $orders->where($type . '_status', Order::NEW)
            ->whereNull('parent_id')
            ->whereHas('children.items.products', function(Builder $query) use ($type) {
                $query->where('type', $type);
            })
            ->get()
            ->count();

        return $standaloneCount + $childCount;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render(): View|string|Closure
    {
        return view('components.staff.orders-overview', [
            'status' => $this->status,
            'barNewOrderCount' => $this->barNewOrderCount ?? null,
            'restaurantNewOrderCount' => $this->restaurantNewOrderCount ?? null,
        ]);
    }
}
