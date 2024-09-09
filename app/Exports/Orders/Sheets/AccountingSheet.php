<?php

namespace App\Exports\Orders\Sheets;

use App\Http\Services\SettingService;
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

class AccountingSheet implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(private $orders)
    {

    }

    public function title(): string
    {
        return 'Accounting';
    }

    /**
     * Map the output for each row
     *
     * @param [Order] $order
     * @return array
     */
    public function map($row): array
    {
        $settings = (new SettingService)->getSettings();

        $vat = in_array($row->service_method,
            [Order::TAKEAWAY, Order::DELIVERY]) ? (float)$settings['takeaway_vat'] : (float)$settings['vat'];

        return [
            $row->restaurant->name,
            $row->created_at->format('F'),
            $row->created_at->format('d'),
            number_format($row->amount ?? 0.00, 2, '.', ''),
            number_format($row->tips ?? 0.00, 2, '.', ''),
            Order::SERVICEMETHODS[$row->service_method],
            $this->outputTransactionType($row),
            $vat,
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
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
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
            __('labels.month'),
            __('labels.day'),
            __('labels.total-amount'),
            __('labels.tips'),
            __('labels.service_method'),
            __('labels.payment_method'),
            __('labels.vat'),
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
