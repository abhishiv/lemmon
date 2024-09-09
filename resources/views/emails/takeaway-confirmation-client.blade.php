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
            <p class="email-title" style="font-weight: 700; font-size: 15px; color: #000;">@lang('labels.bonjour'), {{ $order->pickupDetails->first_name }} {{ $order->pickupDetails->last_name }}</p>
            <p class="email-text" style="color: #000;">Bonne nouvelle ! Nous avons reçu votre commande et nous avons hâte de vous voir !</p>
            <p class="email-text" style="color: #000; font-weight: 600;">Détails de la commande :</p>
            <p class="email-text" style="color: #000;">Numéro de commande : {{ $order->display_id }}</p>
            <p class="email-text" style="color: #000;">Date et heure de retrait : {{ $order->pickupDetails->day }} / {{ $order->pickupDetails->time }}</p>
            <p class="email-text" style="color: #000;">Lieu de retrait : {{ $order->restaurant->address }}</p>
            <p class="email-text" style="color: #000; font-weight: 600;">Voici ce que vous avez commandé :</p>

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
            <p class="email-text" style="color: #000;">Méthode de paiement : Payé en ligne - {{ $order->payments->isNotEmpty() ? $order->payments->last()->transaction_code : '' }}</p>
            <p class="email-text" style="color: #000;">Vous trouverez le reçu joint à cet e-mail en format PDF</p>
            <p class="email-text" style="color: #000;">Si vous avez des questions ou si vous voulez juste discuter, n'hésitez pas à nous appeler au {{ $order->restaurant->phone }} ou à nous envoyer un e-mail à {{ $order->restaurant->email }}.</p>
            <p class="email-text" style="color: #000;">À bientôt!</p>
        </div>
        <p class="copyright" style="color:#fff; text-align: center;">
            © {{ date('Y') }}  @lang('labels.all-rights-reserved')</p>
    </div>
@endsection
