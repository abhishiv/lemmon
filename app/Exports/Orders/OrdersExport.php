<?php

namespace App\Exports\Orders;

use App\Models\Order;
use App\Models\Restaurant;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Orders\Sheets\OrdersSheet;
use App\Exports\Orders\Sheets\ProductsSheet;
use App\Exports\Orders\Sheets\AccountingSheet;

class OrdersExport implements WithMultipleSheets
{
    use Exportable;

    private $restaurant = null;
    private $startDate;
    private $endDate;
    private $orders;

    /**
     * Set the internal start and end dates
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public function setDates(Carbon $startDate, Carbon $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Set the internal restaurant id for the Excel process
     *
     * @param Restaurant $restaurant
     * @return void
     */
    public function setRestaurant(Restaurant $restaurant): void
    {
        $this->restaurant = $restaurant;
    }

    public function setOrders(): void
    {
        $orders = Order::query();

        if ($this->restaurant) {
            $orders = $orders->where('restaurant_id', $this->restaurant->id);
        }

        $orders = $orders->whereIn('status', [Order::CLOSED, Order::CANCELED])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])->where(function ($query) {
                $query->where(function ($query2) {
                    $query2->where('parent_id', null);
                    $query2->where('is_grouped', null);
                })->orWhere(function ($query3) {
                    $query3->whereNotNull('parent_id');
                    $query3->whereNotNull('is_grouped');
                });
            });

        $this->orders = $orders->get();
    }

    public function sheets(): array
    {
        return [
            new OrdersSheet($this->orders),
            new ProductsSheet($this->orders),
            new AccountingSheet($this->orders)
        ];
    }
}
