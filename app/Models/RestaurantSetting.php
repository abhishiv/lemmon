<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RestaurantSetting extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    // Define default values for current defined settings
    const DEFAULT_VALUES = [
        'start_time' => '00:00',
        'end_time' => '23:59',

        'tip_recommended_amount_1' => null,
        'tip_recommended_amount_2' => null,
        'tip_recommended_amount_3' => null,

        'order_grouping_popup' => false,
        'group_order_delay' => null,

        'kitchen_start_time' => ['00:00'],
        'kitchen_end_time' => ['23:59'],

        'bar_start_time' => ['00:00'],
        'bar_end_time' => ['23:59'],

        'take_away' => null,
        'discount_takeaway' => null,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    // Relationships

    /**
     * Get the model that this setting belongs to
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    public function getValueAttribute($value)
    {
        if ($this->value_type == 'json') {
            return json_decode($value);
        }

        return $value;
    }

    /**
     * Update the value and value_type of this setting
     * @param $newValue
     * @return bool
     */
    public function setValue($newValue): bool
    {
        if (is_numeric($newValue) && strval((int)$newValue) == $newValue) {
            $this->update([
                'value' => $newValue,
                'value_type' => 'integer'
            ]);
            return true;
        }

        // Verify if value can be transformed to string value
        if (is_numeric($newValue) && strval((float)$newValue) == $newValue) {
            $this->update([
                'value' => $newValue,
                'value_type' => 'float'
            ]);
            return true;
        }

        if ($newValue === 'true' || $newValue === 'false') {
            $this->update([
                'value' => $newValue,
                'value_type' => 'bool',
            ]);
            return true;
        }

        if (is_array($newValue)) {
            $this->update([
                'value' => $newValue[0] != null ? json_encode($newValue) : '',
                'value_type' => 'json',
            ]);
            return true;
        }

        if (
            (!is_array($newValue)) &&
            ((!is_object($newValue) && settype($newValue, 'string') !== false) ||
                (is_object($newValue) && method_exists($newValue, '__toString')))
        ) {
            $this->update([
                'value' => $newValue,
                'value_type' => gettype($newValue)
            ]);
            return true;
        }
        return false;
    }

    /**
     * Get the value of the setting based on the type saved in the 'value_type' column
     * @return float|int|bool|mixed|void|null
     */
    public function getValue()
    {
        if (!$this->value) {
            return null;
        }

        switch ($this->value_type) {
            // Handle each type
            case 'integer':
            {
                return (int)$this->value;
            }
            case 'float':
            {
                return (float)$this->value;
            }
            case 'bool':
            {
                return $this->value === 'true';
            }
            case 'json':
            {
                return $this->value;
            }
        }

        return $this->value;
    }
}
