<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\PartialPayment;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\Table;

class RestaurantTableService
{
    public function store($request): RestaurantTable
    {
        $hash = $this->generateHash();

        $table = RestaurantTable::create([
            'name' => $request->name,
            'type' => $request->type,
            'hash' => $hash,
            'restaurant_id' => auth()->user()->restaurant_id,
            'room' => $request->room,
            'optional' => $request->optional,
            'status' => $request->status,
        ]);

        $this->generateQr($table);

        return $table;
    }

    public function update($table, $request): RestaurantTable|int
    {
        $table->update($request->validated());

        if (isset($table->getChanges()['name'])) {
            $this->regenerateQr($table);
        }

        if (isset($table->getChanges()['status'])) {
            if ($request['status'] == RestaurantTable::UNAVAILABLE) {
                return $table->orders()->whereIn('orders.status', Order::STAFFSTATUS)->count();
            }
        }

        return $table;
    }

    public function destroy(RestaurantTable $table): bool
    {
        // Verify if the table has any orders
        if ($table->orders()->count()) {
            return false;
        }

        $this->destroyQr($table);
        $table->delete();

        return true;
    }

    public function generateQr(RestaurantTable $table): void
    {
        $qrPath = Storage::disk('public_uploads')->path('') . $table->restaurant_id . '/codes/' . $table->id . '/';

        File::ensureDirectoryExists($qrPath);

        $imageSize = 1000;
        $textPadding = 200;
        $fontSize = 75;
        $font = public_path('dist/fonts/arial.ttf');
        $text = $table->name;

        QrCode::format('png')
            ->size($imageSize)
            ->errorCorrection('M')
            ->generate($table->menuUrl, $table->codePath);

        $srcImage = imagecreatefrompng($table->codePath);

        $dstImage = imagecreatetruecolor($imageSize, $imageSize + $textPadding);

        $color = imagecolorallocate($dstImage, 255, 255, 255);

        $textColor = imagecolorallocate($dstImage, 0, 0, 0);

        imagefill($dstImage, 0, 0, $color);

        list($left, $bottom, $right, , , $top) = imageftbbox($fontSize, 0, $font, $text);

        $xOffset = ($right - $left) / 2;
        $yOffset = ($bottom - $top) / 2;

        imagettftext($dstImage, $fontSize, 0, ($imageSize / 2) - $xOffset, ($textPadding / 2) + ($yOffset / 2),
            $textColor, $font, $text);

        imagecopymerge($dstImage, $srcImage, 0, $textPadding, 0, 0, $imageSize, $imageSize, 100);

        imagepng($dstImage, $table->codePath);
    }

    public function regenerateQr($table): void
    {
        $this->destroyQr($table);

        $this->generateQr($table);
    }

    public function destroyQr($table): bool
    {
        File::delete($table->codePath);

        return true;
    }

    public function createZip()
    {
        $zip = new \ZipArchive();
        $fileFormat = 'png';
        $tables = RestaurantTable::all();

        if ($tables->isEmpty()) {
            return false;
        }

        $zipName = Storage::disk('public_uploads')->path(auth()->user()->restaurant_id . '/codes.zip');
        $zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($tables as $table) {
            $zip->addFile($table->codePath, "$table->name.$fileFormat");
        }

        return $zipName;
    }

    public function checkOrderTable($newTableId, $oldTableId): bool
    {
        $orders = Order::where("table_id", $oldTableId)->whereIn('status',
            [Order::NEW, Order::PREPARING, Order::READY, Order::GROUP])->get();

        foreach ($orders as $order) {
            $order->update(['table_id' => $newTableId]);

            if ($order->partialPayments()->count()) {
                foreach ($order->partialPayments() as $partialPayment) {
                    if($partialPayment){
                        $partialPayment->update(['table_id' => $newTableId]);
                    }
                }
            }
        }

        //the new table must become busy and the old table must be free
        $newTable = RestaurantTable::find($newTableId);
        $newTable->update(['is_busy' => 1]);

        $oldTable = RestaurantTable::find($oldTableId);
        $oldTable->update(['is_busy' => 0]);

        return true;
    }

    private function generateHash(): int
    {
        return rand(100000, 9999999) . Carbon::now()->timestamp;
    }
}
