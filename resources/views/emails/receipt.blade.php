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
            <p class="email-title" style="font-weight: 700; font-size: 15px; color: #000;"> @lang('labels.bonjour'),</p>
            <p class="email-text" style="color: #000;">@lang('labels.thank-you-for-order')
                <br/>@lang('labels.thank-you-for-order2')</p>
            @foreach($orders as $order)
                <div>
                    <p>@lang('labels.numero'): {{$order->display_id}}<br />
                    @lang('labels.date'): {{mb_convert_case($order->created_at->isoFormat('D MMMM YYYY, HH:mm'), MB_CASE_TITLE, "UTF-8")}}<br />
                    @lang('labels.paiement'): {{trans('labels.' . $order->payment_method)}}</p>
                </div>
            @endforeach

            <p style="color: #000; font-size: 15px;">@lang('labels.pdf-attachments')</p>
            <p class="email-text" style="color: #000;">@lang('labels.best-regards')<br/>@lang('labels.the-lemmon-team')
            </p>
        </div>
        <p class="copyright" style="color:#fff; text-align: center;">
            © {{ date('Y') }}  @lang('labels.all-rights-reserved')</p>
    </div>
@endsection
