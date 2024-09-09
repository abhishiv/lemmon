<?php

namespace App\Jobs;

use App\Exports\Orders\OrdersExport;
use App\Models\RestaurantJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ExportRestaurantStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected RestaurantJob $restaurantJob)
    {
    
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $restaurantJob = $this->restaurantJob;
        $restaurant = $restaurantJob->restaurant;
        
        if($restaurantJob->status != RestaurantJob::UNPROCESSED) {
            return false;
        }

        if(!array_key_exists('startDate', $restaurantJob->content) || !array_key_exists('endDate', $restaurantJob->content)) {
            $restaurantJob->delete();
            return false;
        }

        $startDate = Carbon::createFromFormat('Y/m/d H:i', $restaurantJob->content['startDate']);
        $endDate = Carbon::createFromFormat('Y/m/d H:i', $restaurantJob->content['endDate']);
        
        // Process excel
        $export = new OrdersExport();
        $export->setDates($startDate, $endDate);
        // If the job is for a specific restaurant
        if($restaurant) {
            $export->setRestaurant($restaurant);
        }
        $export->setOrders();

        // Generate file name
        $name = $this->generateFilename($startDate, $endDate, $restaurantJob->id);

        Excel::store($export, 'order-exports/'.$name);
        
        // Mark the job as processed
        $restaurantJob->update([
            'status' => RestaurantJob::PROCESSED,
            'result' => $name
        ]);

        return true;
    }

    /**
     * Generate filename for this job's export
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int    $id
     * @return string
     */
    private function generateFilename(Carbon $startDate, Carbon $endDate, int $id) : string {
        $name = 'orders_';

        $name .= strval($startDate->year).'_'.strval($startDate->month).'_'.strval($startDate->day);
        $name .= '_';
        $name .= strval($endDate->year).'_'.strval($endDate->month).'_'.strval($endDate->day);
        $name .= '_';

        // Add the job id
        $name .= strval($id);

        // Add extension
        $name .= '.xlsx';
        return $name;
    }
}
