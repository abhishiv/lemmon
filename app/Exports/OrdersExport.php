<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdersExport implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    use Exportable;

    private $restaurant = null;
    private $startDate;
    private $endDate;

    /**
     * Set the internal start and end dates
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public function setDates(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Set the internal restaurant id for the excel process
     *
     * @param Restaurant $restaurant
     * @return void
     */
    public function setRestaurant(Restaurant $restaurant)
    {
        $this->restaurant = $restaurant;
    }


    /**
     * Map the output for each row
     *
     * @param [Order] $order
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $this->outputDisplayId($row),
            ($row->take_away ? 'Takeaway' : ($row->table?->name ?? '')),
            $this->outputItemNames($row, Product::RESTAURANT),
            $this->outputItemNames($row, Product::BAR),
            $this->outputExtraNames($row),
            $row->amount,
            ($row->take_away ? 'Yes' : ''),
            ($row->take_away ? $row->discount_takeaway : ''),
            $row->tips,
            $this->outputTransactionType($row),
            $row->created_at->format('Y-m-d'),
            $row->created_at->format('H:i'),
            $this->outputFinishedDate($row),
            $this->outputFinishedTime($row),
        ];
    }

    /**
     * Set the format for each column
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function outputExtraNames($order)
    {
        $bundledItems = '';

        foreach ($order->items as $item) {
            if($item->itemBundles->count() == 0) {
                continue;
            }
            foreach ($item->itemBundles as $bundle) {
                // class_basename($bundledItem) == 'Product' ? $bundledItem->name : $bundledItem->title
                $bundledItems .= ($bundle->entity->name ?? $bundle->entity->title) . ' [price=' . $bundle->price . ']';
                $bundledItems .= ', '; // Add a comma and space for separation
            }
        }

        // Remove the trailing comma and space, if any
        $bundledItems = rtrim($bundledItems, ', ');
        return $bundledItems;
    }


    /**
     * Get all items from an order and implode their products' names into a single string
     *
     * @param $order
     * @param $type
     * @return string
     */
    private function outputItemNames($order, $type): string
    {
        return $order->items()->whereRelation('products', 'type', $type)->get()->map(function ($item) {
            return $item->products->name . ' [qty= ' . $item->quantity . ']';
        })->implode(', ');
    }

    public function outputFinishedDate($order): ?string
    {
        $createdAt = $order->events()->where('status', Order::CLOSED)->first()?->created_at;

        if ($createdAt) {
            return Carbon::parse($createdAt)->format('Y-m-d');
        }

        return null;
    }

    public function outputFinishedTime($order): ?string
    {
        $createdAt = $order->events()->where('status', Order::CLOSED)->first()?->created_at;

        if ($createdAt) {
            return Carbon::parse($createdAt)->format('H:i');
        }

        return null;
    }

    private function outputDisplayId($order)
    {
        $display_id = 0;

        if ($order->display_id) {
            $display_id = $order->display_id;
        } elseif ($order->parent_id) {
            $display_id = Order::where('id', $order->parent_id)->get()->pluck('display_id')->first();
        }

        return $display_id;
    }

    /**
     * Get the transaction type for an order
     *
     * @param [Order] $order
     * @return string
     */
    private function outputTransactionType($order): string
    {
        return $order->payment_method == Order::CASH ? 'Cash' : 'Card';
    }

    /**
     * Set the headings for the Excel export
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            __('labels.id'),
            __('labels.internal-id'),
            __('labels.table'),
            __('labels.restaurant-items'),
            __('labels.bar-items'),
            __('labels.extra-items'),
            __('labels.total-amount'),
            __('labels.takeaway'),
            __('labels.takeaway-value-percentage-label'),
            __('labels.tips'),
            __('labels.payment-method'),
            __('labels.received-date'),
            __('labels.received-time'),
            __('labels.completed-date'),
            __('labels.completed-time'),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $orders = Order::query();

        if ($this->restaurant) {
            $orders = $orders->where('restaurant_id', $this->restaurant->id);
        }

        $orders = $orders->whereNotIn('status', [Order::INITIAL, Order::FAILED, Order::CANCELED])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])->where(function ($query) {
                $query->where(function ($query2) {
                    $query2->where('parent_id', null);
                    $query2->where('is_grouped', null);
                })->orWhere(function ($query3) {
                    $query3->whereNotNull('parent_id');
                    $query3->whereNotNull('is_grouped');
                });
            });

        return $orders->get();
    }
}
