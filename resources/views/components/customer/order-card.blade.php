@if(!empty($orders))
    <h2>Preparing your {{count($orders) > 1 ? 'orders' : 'order'}}!</h2>
    <img style="width: 300px" src="{{url('/') . '/images/cooking.gif'}}">
    <div class="orders-container">
        @foreach($orders as $order)
            <div class="item-list">
                <h3>Order: #{{$order->display_id}}</h3>
                @foreach($order->order_products as $key => $product)
                    <div class="item-container">
                        <div class="item-image">
                            <img src="{{ $product->featured_image }}">
                        </div>
                        <div class="item-details">
                            <p>{{$product->name}}</p>
                            <div>
                                <span class="price">&euro; {{$product->sale_price}}</span>
                            </div>
                        </div>
                        <div class="quantity">
                            {{$order->items[$key]->quantity}}
                        </div>
                    </div>
                @endforeach
                <div class="row">
                    <p>Total:</p>
                    <div>
                        <span>&euro;</span>
                        <span id="total">{{number_format($order->amount, 2)}}</span>
                    </div>
                </div>
                <div class="row">
                    <p>Order status:</p>
                    <p class="status  {{$order->status}}">@lang('labels.'.  $order->status)</p>
                </div>
            </div>
        @endforeach
    </div>
    <div class="receipt">
        <div class="row" style="padding: 0px; cursor: pointer">
            <p>Send me the receipt!</p>
            <span>v</span>
        </div>
        <div id="receipt-form">
            @csrf
            <input name="email" id="email" placeholder="email">
            <button class="btn" data-url="{{route('customer.order.receipt')}}">Submit</button>
        </div>
    </div>
    <div class="back">
        <a class="status btn" href="{{$order->table->menuUrl}}">Place another
            order!</a>
    </div>
@else
    <p style="text-align: center">You have no orders!</p>
    <div class="back">
        <a class="status btn" href="{{session('table.url')}}">Place an
            order!</a>
    </div>
@endif
