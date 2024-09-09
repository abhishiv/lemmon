<?php

namespace App\Traits;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager as Image;

trait ImageTrait
{

    /**
     * @param $product
     * @param $images
     * @return bool|array|string
     */
    public function upload($product, $images): bool|array|string
    {
        if (empty($images) && $product->images->isEmpty()) {
            return false;
        }

        //Delete current images if not in request
        if ($product->images->isNotEmpty()) {
            foreach ($product->images as $img) {
                if (empty($images) || !in_array($img->filename, $images)) {
                    $image = ProductImage::where('filename', $img->filename)->first();
                    $path = $image->path();

                    if (Storage::disk('public_uploads')->exists($path)) {
                        Storage::disk('public_uploads')->delete($path);
                    }

                    $image->delete();
                }
            }
        }

        //Add new images and resize
        if (!empty($images)) {
            foreach ($images as $imageType => $baseImages) {
                if (is_array($baseImages)) {
                    foreach ($baseImages as $image) {
                        if (!str_contains($image, ';base64')) {
                            continue;
                        }

                        $data = substr($image, strpos($image, ',') + 1);
                        $data = base64_decode($data);
                        $filename = 'product_' . $product->id . '_' . Str::random(12) . ".png";

                        $path = '/' . auth()->user()->restaurant_id . '/products/images/' . $filename;

                        Storage::disk('public_uploads')->put($path, $data);

                        $img = (new Image())->make(Storage::disk('public_uploads')->path('') . $path);

                        switch ($imageType){
                            case ProductImage::LIST:
                                $img->fit(104, 104)->save();
                                break;
                            case ProductImage::SINGLE:
                                $img->save();
                                break;
                        }
                        ProductImage::create([
                            'filename' => $filename,
                            'product_id' => $product->id,
                            'type' => $imageType,
                        ]);
                    }
                }
            }
        }

        return true;
    }

    public function duplicate($image, $product): bool
    {           
        $newFile = preg_replace('/(product_\d+_)/', "product_" . $product->id . "_", $image->path());

        Storage::disk('public_uploads')->copy($image->path(), $newFile);

        $parts = explode('/', $newFile);
        $filename = end($parts);

        ProductImage::create([
            'filename' => $filename,
            'product_id' => $product->id,
            'type' => $image->type,
        ]);

        return true;
    }

    public function delete($product): bool
    {
        foreach ($product->images as $img) {
            $image = ProductImage::where('filename', $img->filename)->first();
            $path = $image->path();

            if (Storage::disk('public_uploads')->exists($path)) {
                Storage::disk('public_uploads')->delete($path);
            }

            $image->delete();
        }

        return true;
    }

}
