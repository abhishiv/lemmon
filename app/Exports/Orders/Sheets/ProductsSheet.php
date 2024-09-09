<?php

namespace App\Exports\Orders\Sheets;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductsSheet implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(private $orders)
    {

    }

    public function title(): string
    {
        return 'Products';
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
            $row->restaurant,
            $row->id,
            $row->order_id,
            $row->table,
            $row->type,
            $row->item,
            $row->qty,
            $row->price,
            $row->extras,
            $row->received_date,
            $row->received_time,
            $row->completed_date,
            $row->completed_time,
            $row->service_method,
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
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }


    /**
     * Set the headings for the Excel export
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            __('labels.restaurant'),
            __('labels.order-id'),
            __('labels.internal-id'),
            __('labels.table'),
            __('labels.product-type'),
            __('labels.product-name'),
            __('labels.quantity'),
            __('labels.price'),
            __('labels.extra-items'),
            __('labels.received-date'),
            __('labels.received-time'),
            __('labels.completed-date'),
            __('labels.completed-time'),
            __('labels.service_method'),
        ];
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->items();
    }

    private function items(): Collection
    {
        $items = new Collection();

        $this->orders->each(function ($order) use ($items) {
            $completedDateTime = $this->getOrderCompletionDateTime($order);

            $order->items->each(function ($item) use ($order, $items, $completedDateTime) {
                $newItem = (object)[
                    'restaurant' => $order->restaurant->name,
                    'id' => $item->order_id,
                    'order_id' => $this->getOrderDisplayId($order),
                    'table' => $order->table_id,
                    'type' => $item->products->type,
                    'item' => $item->products->name,
                    'qty' => (int)$item->quantity,
                    'price' => $this->calculateItemTotalPrice($item),
                    'extras' => $this->concatenateBundleItems($item),
                    'received_date' => $order->created_at->format('Y-m-d'),
                    'received_time' => $order->created_at->format('H:i'),
                    'completed_date' => $completedDateTime ? $completedDateTime['date'] : '',
                    'completed_time' => $completedDateTime ? $completedDateTime['time'] : '',
                    'service_method' => Order::SERVICEMETHODS[$order->service_method],
                ];

                $items->push($newItem);
            });

        });

        return $items;
    }

    private function getOrderDisplayId($order)
    {
        $display_id = 0;

        if ($order->display_id) {
            $display_id = $order->display_id;
        } elseif ($order->parent_id) {
            $display_id = Order::where('id', $order->parent_id)->get()->pluck('display_id')->first();
        }

        return $display_id;
    }

    private function getOrderCompletionDateTime($order): ?array
    {
        $completionDateTime = $order->events()->where('status', Order::CLOSED)->first()?->created_at;

        if ($completionDateTime) {
            return [
                'date' => Carbon::parse($completionDateTime)->format('Y-m-d'),
                'time' => Carbon::parse($completionDateTime)->format('H:i')
            ];
        }

        return null;
    }

    private function calculateItemTotalPrice($item)
    {
        $total = $item->price;

        if ($item->itemBundles->count()) {
            foreach ($item->itemBundles as $bundleItem) {
                $total += (float)$bundleItem->price;
            }
        }

        return $total;
    }

    public function concatenateBundleItems($item): string
    {
        $bundleItems = '';

        if ($item->itemBundles->count() == 0) {
            return $bundleItems;
        }

        foreach ($item->itemBundles as $bundleItem) {
            $bundleItems .= ($bundleItem->entity->name ?? $bundleItem->entity->title) . ' [price=' . $bundleItem->price . ']';
            $bundleItems .= ', ';
        }

        return rtrim($bundleItems, ', ');
    }
}
