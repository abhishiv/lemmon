@extends('layouts.customer')
@push('scripts')
    <script type="text/javascript" src="{{ mix('/dist/js/customer/cart.js', '..') }}"></script>
@endpush
@section('content')
    <div class="checkout-container">
        <div class="cart-header">
            <h2>Checkout</h2>
            <h2><a href="{{route('customer.cart')}}">< Back</a></h2>
        </div>
        <form action="{{ route('customer.order.store') }}" method="POST">
            @csrf
            <div class="item-list">
                @if($products)
                    @foreach($products as $product)
                        <input type="hidden" name="items[{{$product->id}}]" value="{{$product->id}}">
                        <input type="hidden" name="quantity[{{$product->id}}]" value="{{$product->quantity}}">
                        <div class="item-container" data-product-id="{{$product->id}}">
                            <div class="item-image">
                                <img src="{{ $product->featured_image }}">
                            </div>
                            <div class="item-details">
                                <p>{{$product->name}}</p>
                                @if($product->special_price)
                                    <div>
                                        <span style="text-decoration: line-through; ">&euro; {{priceFormat($product->price)}}</span>
                                    </div>
                                    <span class="price" style="color: #CC0000FF; "
                                          data-item-price="{{$product->price}}">&euro; {{priceFormat($product->special_price)}}</span>
                                @else
                                    <div>
                                        <span class="price"
                                              data-item-price="{{$product->price}}">&euro; {{priceFormat($product->price)}}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="quantity">
                                {{$product->quantity}}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="cart-footer">
                <div class="total-price">
                    <p>Total:</p>
                    <div>
                        <span>&euro;</span>
                        <span id="total">{{$total}}</span>
                    </div>
                </div>
                @if($notes)
                    <div class="kitchen-notes">
                        <label for="notes">Notes To kitchen</label>
                        <input readonly id="notes" name="notes" value="{{$notes}}" class="text">
                    </div>
                @endif
                <div class="payment-container">
                    <h4>Choose payment method:</h4>
                    <input type="radio" id="online" name="payment_method" value="{{\App\Models\Order::ONLINE}}" required>
                    <label for="online">Online</label><br>
                    <input type="radio" id="cash" name="payment_method" value="{{\App\Models\Order::CASH}}">
                    <label for="cash">Cash</label><br>
                </div>
                <button class="btn order" type="submit">Pay</button>

            </div>
        </form>
    </div>
@endsection

