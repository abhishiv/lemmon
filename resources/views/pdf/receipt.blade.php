<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@lang('labels.receipt')</title>

    <style>
        * {
            font-family: "Inter", sans-serif;
        }

        body {
            position: relative;
        }

        .pdf-row {
            width: 100%;
        }

        .pdf-column {
            width: 50%;
        }

        .right {
            float: right;
            text-align: right;
            margin-top: -40px;
        }

        .left {
            margin-bottom: 0;
        }

        .title {
            font-size: 20px;
        }

        .sub-title {
            font-size: 15px;
        }

        .products {
            width: 100%;
            border-collapse: collapse;
        }

        .products th {
            padding: 10px 5px;
        }

        .products td {
            padding: 0 5px;
        }

        .products th:first-child,
        .products td:first-child {
            padding-left: 0;
        }

        .products th:last-child,
        .products td:last-child {
            padding-right: 0;
        }

        .products tbody tr:first-child td {
            padding-top: 15px
        }

        .products tbody tr:last-child td {
            padding-bottom: 15px
        }

        .totals {
            margin-top: 40px;
            text-align: right;
        }

        .totals__inner {
            display: inline-block;
            text-align: left;
        }

        .totals__table .totals__push td {
            padding-top: 25px;
        }

        .totals__table td + td {
            padding-left: 30px;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
        }
    </style>
</head>
<body>
    <p>@lang('labels.receipt-fr')</p>
    <p style="margin-bottom: 40px;">
        <span>@lang('labels.issued-by-fr')</span><br>
        <span>{{ $order->restaurant->name }}</span><br>
        <span>{{ $order->restaurant->address }}</span><br>
        <span>@lang('labels.phone-fr'): {{ $order->restaurant->receipt_phone }}</span><br>
        <span>@lang('labels.vat-number-fr') : {{ $order->restaurant->company_registration }}</span>
    </p>
    <p style="margin-bottom: 40px;">@lang('labels.date-fr'): {{ $order->created_at->format('M d Y') }}, {{ $order->created_at->format('H:i') }}</p>
    <table class="products">
        <thead>
            <tr>
                <th style="width: 45%; text-align: left;">@lang('labels.description')</th>
                <th style="width: 25%; text-align: left;">@lang('labels.price-per-unit')</th>
                <th style="width: 10%; text-align: left;">@lang('labels.qty')</th>
                <th style="width: 20%; text-align: right;">@lang('labels.amount-fr')</th>
            </tr>
        </thead>
        <tbody style="border-top: 1px solid; border-bottom: 1px solid;">
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ $item->products->name }}
                    </td>
                    <td>
                        @if ($item->itemBundles->count() > 0)
                            {{ priceFormat($item->products->sale_price + $item->itemBundles->sum('entity.price'), ',') }} @lang('labels.currency')
                        @else
                            {{ priceFormat($item->products->sale_price, ',') }} @lang('labels.currency')
                        @endif
                    </td>
                    <td>
                        {{ $item->quantity }}
                    </td>
                    <td style="text-align: right;">
                        @if ($item->itemBundles->count() > 0)
                            {{ priceFormat(($item->products->sale_price + $item->itemBundles->sum('entity.price')) * $item->quantity, ',') }} @lang('labels.currency')
                        @else
                            {{ priceFormat($item->products->sale_price * $item->quantity, ',') }} @lang('labels.currency')
                        @endif
                    </td>
                </tr>

                @if ($item->itemBundles->count() > 0)
                    <tr>
                        <td style="font-size:12px; line-height:12px; padding-left: 10px;">
                            @foreach ($item->itemBundles as $bundledItem)
                                <span>{{ $bundledItem->entity_type == \App\Models\Product::class ? $bundledItem->entity->name : $bundledItem->entity->title }}</span><br>
                            @endforeach
                        </td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right; font-size:12px; line-height:12px">
                            @foreach ($item->itemBundles as $bundledItem)
                                + {{ priceFormat($bundledItem->price, ',') }} @lang('labels.currency')<br>
                            @endforeach
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <div class="totals">
        <div class="totals__inner">
            <table class="totals__table">
                <tbody>
                    <tr>
                        <td>@lang('labels.vat-fr') {{ rtrim(rtrim(priceFormat($order->totals['vat']['amount'], ','), '0'), ',') }}%</td>
                        <td>{{ priceFormat($order->totals['vat']['net'], ',') }} @lang('labels.currency')</td>
                    </tr>
                    <tr>
                        <td>@lang('labels.net-fr') </td>
                        <td>{{ priceFormat($order->totals['net'], ',') }} @lang('labels.currency')</td>
                    </tr>
                    <tr class="totals__push">
                        <td>@lang('labels.subtotal-fr') </td>
                        <td>{{ priceFormat($order->totals['subtotal'], ',') }} @lang('labels.currency')</td>
                    </tr>
                    @if ($order->totals['discounts']['total'] > 0)
                        <tr>
                            <td>@lang('labels.discount-fr') </td>
                            <td>-{{ priceFormat($order->totals['discounts']['total'], ',') }} @lang('labels.currency')</td>
                        </tr>
                    @endif  
                    @if (isset($order->totals['tips']))
                        <tr>
                            <td>@lang('labels.tips-fr') </td>
                            <td>{{ priceFormat($order->totals['tips']['value'], ',') }} @lang('labels.currency')</td>
                        </tr>
                    @endif
                    @if (isset($order->totals['delivery_fee']))
                        <tr>
                            <td>@lang('labels.delivery-fr') </td>
                            <td>{{ priceFormat($order->totals['delivery_fee']['value'], ',') }} @lang('labels.currency')</td>
                        </tr>
                    @endif  
                    <tr style="font-weight: 700;">
                        <td>@lang('labels.total-fr') </td>
                        <td>{{ priceFormat($order->totals['amount_due'], ',') }} @lang('labels.currency')</td>
                    </tr>
                    <tr class="totals__push">
                        <td>@lang('labels.payment-mtd-fr') </td>
                        <td>{{ ucfirst($order->payment_method) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <footer class="footer">
        <p>
            {{ $order->restaurant->receipt_message }}
        </p>
    </footer>
</body>
</html>
