@extends('layouts.email')
@section('content')
    @php
        \Carbon\Carbon::setLocale('fr');
    @endphp
    <div class="email"
         style="height: 100%; background: #16212E; font-family: sans-serif; font-size: 15px; line-height: 1.5; width: 100%; padding: 30px 0;">
        <div class="email-logo" style="margin-bottom: 40px; text-align: center !important;">
            <img src="{{$message->embed(public_path() . "/dist/img/Lemmon.png")}}">
        </div>
        <div class="email-body"
             style="background: #fff; padding: 50px; width: 712px;  margin-left: auto; margin-right: auto;">
            <p class="email-text" style="color: #000;">Chère équipe,</p>
            <p class="email-text" style="color: #000;">Nous venons de recevoir une nouvelle commande via l'application Lemmon !</p>
            <p class="email-text" style="color: #000;">Voici les détails :</p>
            <p class="email-text" style="color: #000;">Numéro de commande : {{ $order->display_id }}</p>
            <p class="email-text" style="color: #000;">Nom du client : {{ $order->deliveryInformation->first_name }} {{ $order->deliveryInformation->last_name }}</p>
            <p class="email-text" style="color: #000;">Numéro de contact : {{ $order->deliveryInformation->phone }}</p>
            <p class="email-text" style="color: #000;">Type de commande : Livraison</p>
            
            <p class="email-text" style="color: #000; font-weight: 600;">Détails de la commande :</p>

            <table>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td style="color: #000;">
                                {{ $item->quantity }}x {{ $item->products->name }}
                            </td>
                            <td style="padding-left: 20px; color: #000;">
                                @if ($item->itemBundles->count() > 0)
                                @lang('labels.currency') {{ priceFormat(($item->products->sale_price + $item->itemBundles->sum('entity.price')) * $item->quantity, ',') }}
                                @else
                                    @lang('labels.currency') {{ priceFormat($item->products->sale_price * $item->quantity, ',') }}
                                @endif
                            </td>
                        </tr>
        
                        @if ($item->itemBundles->count() > 0)
                            <tr>
                                <td style="font-size:12px; line-height:12px; padding-left: 10px; color: #000;">
                                    @foreach ($item->itemBundles as $bundledItem)
                                        <span>{{ $bundledItem->entity_type == \App\Models\Product::class ? $bundledItem->entity->name : $bundledItem->entity->title }}</span><br>
                                    @endforeach
                                </td>
                                <td></td>
                            </tr>
                        @endif
                       
                    @endforeach

                    <tr>
                        <td style="padding-top: 20px; color: #000;">@lang('labels.subtotal-fr') </td>
                        <td style="padding-left: 20px; padding-top: 20px; color: #000;">@lang('labels.currency') {{ priceFormat($totals['subtotal'], ',') }}</td>
                    </tr>
                    @if ($totals['discounts']['total'] > 0)
                        <tr>
                            <td style="color: #000;">@lang('labels.discount-fr') </td>
                            <td style="padding-left: 20px; color: #000;">@lang('labels.currency') -{{ priceFormat($totals['discounts']['total'], ',') }}</td>
                        </tr>
                    @endif  
                    @if (isset($totals['tips']))
                        <tr>
                            <td style="color: #000;">@lang('labels.tips-fr') </td>
                            <td style="padding-left: 20px; color: #000;">@lang('labels.currency') {{ priceFormat($totals['tips']['value'], ',') }}</td>
                        </tr>
                    @endif  
                    <tr style="font-weight: 700;">
                        <td style="color: #000;">@lang('labels.total-fr') :</td>
                        <td style="padding-left: 20px; color: #000;">@lang('labels.currency') {{ priceFormat($totals['gross'], ',') }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="email-text" style="color: #000;">Adresse de livraison : <br>{{ $order->deliveryInformation->street }} <br>{{ $order->deliveryInformation->postal_code }}, {{ $order->deliveryInformation->city }}</p>

            <p class="email-text" style="color: #000;">Cordialement, <br>
                The Lemmon Team
            </p>
        </div>
        <p class="copyright" style="color:#fff; text-align: center;">
            © {{ date('Y') }}  @lang('labels.all-rights-reserved')</p>
    </div>
@endsection
