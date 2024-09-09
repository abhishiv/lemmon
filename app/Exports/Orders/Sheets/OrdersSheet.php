<?php

namespace App\Exports\Orders\Sheets;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdersSheet implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(private $orders)
    {

    }

    public function title(): string
    {
        return 'Orders';
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
            $row->restaurant->name,
            $row->id,
            $this->outputDisplayId($row),
            __('labels.' . $row->status),
            ($row->take_away ? 'Takeaway' : ($row->table?->name ?? '')),
            $this->outputItemNames($row, Product::RESTAURANT),
            $this->outputItemNames($row, Product::BAR),
            $this->outputExtraNames($row),
            number_format($row->amount ?? 0.00, 2, '.', ''),
            Order::SERVICEMETHODS[$row->service_method],
            ($row->take_away ? $row->discount_takeaway : ''),
            number_format($row->tips ?? 0.00, 2, '.', ''),
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
            if ($item->itemBundles->count() == 0) {
                continue;
            }
            foreach ($item->itemBundles as $bundle) {
                // class_basename($bundledItem) == 'Product' ? $bundledItem->name : $bundledItem->title
                $bundledItems .= ($bundle->entity?->name ?? $bundle->entity?->title) . ' [price=' . $bundle->price . ']';
                $bundledItems .= ', '; // Add a comma and space for separation
            }
        }

        // Remove the trailing comma and space, if any
        return rtrim($bundledItems, ', ');
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
        $createdAt = $order->events()->where('status', Order::READY)->first()?->created_at;

        if ($createdAt) {
            return Carbon::parse($createdAt)->format('Y-m-d');
        }

        return null;
    }

    public function outputFinishedTime($order): ?string
    {
        $createdAt = $order->events()->where('status', Order::READY)->first()?->created_at;

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
        return match ($order->payment_method) {
            Order::CARD => 'Terminal',
            Order::ONLINE => 'Online',
            default => 'Cash',
        };
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
            __('labels.order-status'),
            __('labels.table'),
            __('labels.restaurant-items'),
            __('labels.bar-items'),
            __('labels.extra-items'),
            __('labels.total-amount'),
            __('labels.service_method'),
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
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->orders;
    }
}
