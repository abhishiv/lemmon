<?php

namespace App\Http\Services;

use App\Jobs\ExportRestaurantStatistics;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManagerDashboardService
{
    public function getRestaurantMessages()
    {
        $restaurant = Restaurant::find(request()->user()->restaurant_id);

        $message = null;

        if ($restaurant->settings()->where('key', 'start_time')->count() == 0 ||
            $restaurant->settings()->where('key', 'start_time')->count() == 0
            && $restaurant->productCategories()->has('products')->count()) {
            $message = [
                'type' => 'error',
                'message' => __('labels.working-hours-not-set-up')
            ];
        }

        return $message;
    }

    public function getRestaurantStatistics(Request $request = null): array
    {

        $statistics = [
            'order_cash_count' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED, Order::FAILED]),
            'order_card_count' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED, Order::FAILED]),
            'order_terminal_count' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED, Order::FAILED]),
            'order_cash_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED, Order::FAILED]),
            'order_card_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED, Order::FAILED]),
            'order_terminal_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED, Order::FAILED]),
            'order_tip_money' => Order::whereNotIn('status', [Order::INITIAL, Order::CANCELED, Order::FAILED]),
        ];


        if (!$request) {
            // If the stats requested are for all the restaurants
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now();
        } else {
            // If the stats requested are for a specfic restaurant
            $startDate = Carbon::createFromFormat('Y/m/d H:i', $request->input('startDate'));
            $endDate = Carbon::createFromFormat('Y/m/d H:i', $request->input('endDate'));
        }

        $statistics['order_cash_count'] = $statistics['order_cash_count']->where('payment_method',
            Order::CASH)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', null);
            })->orWhere(function ($query3) {
                $query3->whereNotNull('parent_id');
                $query3->whereNotNull('is_grouped');
            });
        })->count();

        $statistics['order_card_count'] = $statistics['order_card_count']->where('payment_method',
            Order::ONLINE)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', null);
            })->orWhere(function ($query3) {
                $query3->whereNotNull('parent_id');
                $query3->whereNotNull('is_grouped');
            });
        })->count();

        $statistics['order_terminal_count'] = $statistics['order_terminal_count']->where('payment_method',
            Order::CARD)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', null);
            })->orWhere(function ($query3) {
                $query3->whereNotNull('parent_id');
                $query3->whereNotNull('is_grouped');
            });
        })->count();

        // Statistics
        $statistics['order_total_count'] = $statistics['order_terminal_count'] + $statistics['order_card_count'] + $statistics['order_cash_count'];

        $cardMoney = $statistics['order_terminal_money']->where('payment_method',
            Order::CARD)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', null);
            })->orWhere(function ($query3) {
                $query3->whereNotNull('parent_id');
                $query3->whereNotNull('is_grouped');
            });
        })->with('partialPayments')->get();

        $cashPartialMoney = [];

        foreach ($cardMoney as $order) {
            if ($order->partialPayments->isNotEmpty()) {
                foreach ($order->partialPayments as $payment) {
                    if ($payment->method == Order::CASH) {
                        $cashPartialMoney[$payment->id] = $payment->amount;
                    }
                }
            }
        }

        $cashMoney = $statistics['order_cash_money']->where('payment_method',
            Order::CASH)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', null);
            })->orWhere(function ($query3) {
                $query3->whereNotNull('parent_id');
                $query3->whereNotNull('is_grouped');
            });
        })->with('partialPayments')->get();

        $cardPartialMoney = [];

        foreach ($cashMoney as $order) {
            if ($order->partialPayments->isNotEmpty()) {
                foreach ($order->partialPayments as $payment) {
                    if ($payment->method == Order::CARD) {
                        $cardPartialMoney[$payment->id] = $payment->amount;
                    }
                }
            }
        }

        $statistics['order_cash_money'] = $statistics['order_cash_money']->where('payment_method',
                Order::CASH)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
                $query->where(function ($query2) {
                    $query2->where('parent_id', null);
                    $query2->where('is_grouped', null);
                })->orWhere(function ($query3) {
                    $query3->whereNotNull('parent_id');
                    $query3->whereNotNull('is_grouped');
                });
            })->sum('amount') + array_sum($cashPartialMoney) - array_sum($cardPartialMoney);

        $statistics['order_card_money'] = $statistics['order_card_money']->where('payment_method',
            Order::ONLINE)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', null);
            })->orWhere(function ($query3) {
                $query3->whereNotNull('parent_id');
                $query3->whereNotNull('is_grouped');
            });
        })->sum('amount');

        $statistics['order_terminal_money'] = $statistics['order_terminal_money']->where('payment_method',
                Order::CARD)->whereBetween('created_at', [$startDate, $endDate])->where(function ($query) {
                $query->where(function ($query2) {
                    $query2->where('parent_id', null);
                    $query2->where('is_grouped', null);
                })->orWhere(function ($query3) {
                    $query3->whereNotNull('parent_id');
                    $query3->whereNotNull('is_grouped');
                });
            })->sum('amount') - array_sum($cashPartialMoney) + array_sum($cardPartialMoney);

        // Order total (all, cash, card)
        $statistics['order_total_money'] = $statistics['order_terminal_money'] + $statistics['order_card_money'] + $statistics['order_cash_money'];

        $statistics['order_tip_money'] = $statistics['order_tip_money']->whereBetween('created_at',
            [$startDate, $endDate])->where(function ($query) {
            $query->where(function ($query2) {
                $query2->where('parent_id', null);
                $query2->where('is_grouped', null);
            })->orWhere(function ($query3) {
                $query3->whereNotNull('parent_id');
                $query3->whereNotNull('is_grouped');
            });
        })->sum('tips');

        $statistics['date_from'] = $startDate->isoFormat('MMM Do YYYY');
        $statistics['date_to'] = $endDate->isoFormat('MMM Do YYYY');

        $statistics['restaurant_name'] = Restaurant::find(request()->user()->restaurant_id)->name;

        return $statistics;
    }

    public function exportStatistics($request)
    {
        // Create a new job
        $restaurantJob = new RestaurantJob;
        $restaurantJob->restaurant_id = request()->user()->restaurant_id;
        $restaurantJob->user_id = request()->user()->id;
        $restaurantJob->type = 'export-statistics';
        $restaurantJob->status = RestaurantJob::UNPROCESSED;
        $restaurantJob->content = [
            'startDate' => $request->input('startDate'),
            'endDate' => $request->input('endDate')
        ];
        $restaurantJob->save();
        // Dispatch job
        ExportRestaurantStatistics::dispatch($restaurantJob);

        return $restaurantJob->id;
    }

    public function getExportStatistics($request)
    {
        $restaurantJob = RestaurantJob::find($request->input('job_id'));

        // Validation
        if (!$restaurantJob) {
            return 'invalid';
        }

        if ($restaurantJob->user_id != request()->user()->id) {
            return 'invalid';
        }

        if ($restaurantJob->status == RestaurantJob::FINISHED) {
            // job has been finished and the file has been downloaded
            return 'invalid';
        }

        // If the job still hasn't been processed
        if ($restaurantJob->status == RestaurantJob::UNPROCESSED) {
            return [
                'status' => 'processing'
            ];
        }

        // This means the job has been processed

        // Validation if the result is null
        if ($restaurantJob->result == null) {
            return 'invalid';
        }

        return [
            'status' => 'processed',
            'result' => route('manager.statistics.download', ['restaurantJob' => $restaurantJob->id])
        ];
    }

    public function downloadExportStatistics(RestaurantJob $restaurantJob)
    {
        // Verify if the file has been downloaded or if the user isn't the same
        if ($restaurantJob->status != RestaurantJob::PROCESSED || $restaurantJob->user_id != request()->user()->id) {
            return new JsonResponse([
                'response' => 'Unavailable request.'
            ]);
        }

        // Mark the restaurant job as finished
        $restaurantJob->update([
            'status' => RestaurantJob::FINISHED
        ]);

        if (!Storage::exists("/order-exports/" . $restaurantJob->result)) {
            return abort(404);
        }

        return response()->download(Storage::path("/order-exports/" . $restaurantJob->result));
    }
}
