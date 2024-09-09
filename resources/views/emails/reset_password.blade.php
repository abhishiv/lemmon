@extends('layouts.email')
@section('content')
    <div class="email"
         style="height: 100%; background: #16212E; font-family: sans-serif; font-size: 18px; line-height: 1.5; width: 100%; padding: 30px 0;">
        <div class="email-logo" style="margin-bottom: 40px; text-align: center !important;">
            <img src="{{$message->embed(public_path() . "/dist/img/Lemmon.png")}}">
        </div>
        <div class="email-body"
             style="background: #fff; padding: 50px; width: 412px; text-align: center; margin-left: auto; margin-right: auto;">
            <span class="email-title"
                  style="color: #000; font-weight: 700; font-size: 17px;">@lang('labels.hello')!</span>
            <p class="email-text" style="color: #000;">@lang('labels.reset-your-password')</p>
            <a href="{{url('reset-password')}}/{{ $data['token'] }}?email={{ $data['email'] }}" class="email-button"
               style="font-size: 12px; color: #fff; margin: 35px 0; padding: 15px 30px; background: #006EF5; text-decoration: none; ">
                @lang('labels.reset-password')</a>
            <hr style="color: #f2f2f2; margin: 30px 0;"/>
            <div class="email-text" style="color: #000; word-break: break-word;"> @lang('labels.below-link-reset') <a
                    href="{{url('reset-password')}}/{{ $data['token'] }}?email={{ $data['email'] }}">{{url('reset-password')}}
                    /{{ $data['token'] }}?email={{ $data['email'] }}</a>
            </div>
            <p class="email-text" style="color: #000;">@lang('labels.recent-account-access')</p>
            <span style="color: #000; font-weight: 700; font-size: 15px;"> @lang('labels.the-lemmon-team')</span>
        </div>
        <p class="copyright" style="color:#fff; text-align: center;">
            © {{ date('Y') }} @lang('labels.all-rights-reserved')</p>
    </div>
@endsection
