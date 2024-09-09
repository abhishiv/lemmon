<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RestaurantJob extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'content' => 'array'
    ];

    // Statuses
    const UNPROCESSED = 'unprocessed'; // initial state
    const PROCESSED = 'processed'; // if the job has been processed and is waiting for an action from the user (e.g. download)
    const FINISHED = 'finished'; // if the job has been processed and all user actions have been finished

    /*
        For the `type` column there are the following values:
        - export-statistics - export the statistics for a specific restaurant (or all) for a given start date and end date
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logFillable()->logOnlyDirty();
    }

    // Relationships
    public function restaurant() {
        return $this->belongsTo(Restaurant::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
