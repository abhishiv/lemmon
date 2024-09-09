<?php

namespace App\Payment\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    const INITIAL = 'initial';
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const CONFIRMED = 'confirmed';
    const CANCELLED = 'cancelled';
    const ERROR = 'error';

    const PROVIDER_PAYPAL = 'Paypal';
    const PROVIDER_STRIPE = 'Stripe';
    const PROVIDER_PAYREXX = 'Payrexx';

    const PROVIDERS = [
        'Paypal' => self::PROVIDER_PAYPAL,
        'Stripe' => self::PROVIDER_STRIPE,
        'Payrexx' => self::PROVIDER_PAYREXX,
    ];

    const STATUSES = [
        self::INITIAL => 'initial',
        self::PENDING => 'pending',
        self::CONFIRMED => 'success',
        self::CANCELLED => 'cancelled',
        self::ERROR => 'error',
    ];

    protected $fillable = [
        'uuid',
        'original_amount',
        'amount',
        'provider',
        'status',
        'order_id',
        'transaction_code',
    ];

    public function paypal(): HasMany
    {
        return $this->hasMany(PaypalTransaction::class, 'payment_id', 'id');
    }

    public function stripe(): HasMany
    {
        return $this->hasMany(PaypalTransaction::class, 'payment_id', 'id');
    }

    public function payrexx(): HasMany
    {
        return $this->hasMany(PayrexxTransaction::class, 'payment_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function getProvider(string $name): ?string {
        return constant('self::PROVIDER_'. strtoupper($name));
    }
}
