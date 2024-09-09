<?php

namespace App\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class PaypalTransaction extends Model
{

    const TO_INITIAL = 'initial';   # The request is not yet created to Paypal and only exists locally
    const TO_CREATED = 'created';   # Request was sent to Paypal and a transcation ID was generated, user was redirected
    const TO_RETURNED = 'returned';   # After redirect back to the app, we are able to get PayerID
    const TO_CAPTURED = 'captured';  # If the account checks passed, we have captured the payment.
    const TO_CANCELLED = 'cancelled';
    const TO_FAILED = 'failed';

    protected $guarded = ['id'];

    public function getResponseAttribute($value) {
        return json_decode($value, True);
    }
}
