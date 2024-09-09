<?php

namespace App\Http\Controllers\Manager;

use App\Models\Extra;
use App\Models\Product;
use App\Models\Service;
use App\Models\FoodType;
use App\Traits\ImageTrait;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Support\Carbon;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Http\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\ImageFormRequest;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\ProductFormRequest;
use Illuminate\Contracts\Foundation\Application;
use Intervention\Image\ImageManagerStatic as Image;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    public function list(): Factory|View|Application
    {
        $products = Product::select(['id', 'name', 'price', 'special_price', 'description', 'status'])->with('categories')->get();

        return view('manager.products.list', compact('products'));
    }

    public function create(): Factory|View|Application
    {
        $categories = ProductCategory::all();

        $statuses = Product::STATUSES;

        $latestProduct = Product::latest('id')->first();

        $foodTypes = FoodType::all();

        $extras = null;

        // Preselect last selected food type
        $selectedFoodType = Product::where('restaurant_id', auth()->user()->restaurant_id)
                                    ->whereNotNull('food_type_id')
                                    ->orderBy('updated_at', 'DESC')
                                    ->first()->foodType ?? null;

        return view('manager.products.create', compact('categories', 'statuses', 'latestProduct', 'foodTypes', 'selectedFoodType', 'extras'));
    }

    public function createBundleItem(): Factory|View|Application
    {
        $categories = ProductCategory::all();

        $statuses = Product::STATUSES;

        $relatedProducts = Product::all();

        $latestProduct = Product::latest('id')->first();

        $foodTypes = FoodType::all();

        $extras = Extra::all();

        $removables = $extras;

        $selectedFoodType = Product::where('restaurant_id', auth()->user()->restaurant_id)
                                            ->whereNotNull('food_type_id')
                                            ->orderBy('updated_at', 'DESC')
                                            ->first()->foodType ?? null;

        return view('manager.products.create', compact('categories', 'statuses', 'relatedProducts', 'latestProduct', 'foodTypes', 'selectedFoodType', 'extras', 'removables'));
    }

    public function verifyImage(ImageFormRequest $request): JsonResponse
    {
        return response()->json(['success' => 'ok'], 200);
    }

    public function getImages(Product $product): bool|string
    {
        $images = $product->getImages();

        return json_encode($images);
    }

    public function store(ProductFormRequest $request): RedirectResponse
    {
        $this->productService->store($request);

        return redirect()->route('manager.product.list')->with(['success' => 'Product has been saved']);
    }

    public function edit(Product $product): Factory|View|Application
    {
        $categories = ProductCategory::all();

        $statuses = Product::STATUSES;

        $relatedProducts = Product::whereNotIn('id', [$product->id])->get();

        $foodTypes = FoodType::all();

        $extras = Extra::all();

        $extraProducts = $product->extras()->withPivot(['order', 'price'])->get();

        $productsExtra = $product->extraProducts()->withPivot(['order', 'price'])->get();

        $removables = $product->removables()->withPivot(['order'])->get() ?? [];

        $product = $product->where('id', $product->id)->with('bundle')->with('bundle.extraProducts')->first();

        return view('manager.products.edit', compact('product', 'categories', 'statuses', 'relatedProducts', 'foodTypes', 'extras', 'extraProducts', 'productsExtra', 'removables'));
    }

    public function update(ProductFormRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update($product, $request);
        if(strlen($product->name) >= 25)  return redirect()->route('manager.product.list')->with(['success' => 'Product has been updated. WARNING: Product\'s name length is over 24 characters long, so the description won\'t show up in the client app.']);

        return redirect()->route('manager.product.list')->with(['success' => 'Product has been updated']);
    }

    public function copy(Product $product)
    {
        $newProduct = $this->productService->copy($product);

        return redirect()->route('manager.product.edit', ['product' => $newProduct->id])->with(['success' => __('manager/products.product-copied')]);
    }

    public function destroy(Product $product): RedirectResponse
    {
        return redirect()->route('manager.product.list')->with($this->productService->destroy($product) ? ['success' => 'Product has been removed'] : ['error' => 'Product exists in an active order!']);
    }

    public function dataTable(): JsonResponse
    {
        $products = Product::select(['id', 'name', 'price', 'special_price', 'description', 'status', 'food_type_id'])->with('categories')->get();

        $foodTypes = FoodType::all()->pluck('name', 'id')->all();

        return DataTables::of($products)
            ->addColumn('category', function ($product){
                return $product->categories()->first()->name ?? '(no category)';
            })
            ->addColumn('action', function ($product){
                $editRoute = route('manager.product.edit', $product->id);
                $deleteRoute = route('manager.product.destroy', $product->id);
                $copyRoute = route('manager.product.copy', $product->id);

                return \view('components.data-tables.action', compact('editRoute', 'deleteRoute', 'copyRoute'))->render();
            })
            ->editColumn('food_type_id', function ($product) use ($foodTypes) {
                return $foodTypes[$product->food_type_id] ?? '';
            })
            ->editColumn('price', function ($table){
                return priceFormat($table->price) . ' CHF';
            })
            ->editColumn('special_price', function ($table){
                return $table->special_price ? $table->special_price . ' CHF' : '';
            })
            ->editColumn('status', function ($product){
                return "<span class='status-{$product->status}'>".trans('labels.' .$product->status)."</span>";
            })
         /*   ->editColumn('description', function ($product){
                return Str::limit($product->description, 50, '');
            })*/
            ->addColumn('service', function ($product){
                $services = '';
                if ($product->services(false)->get()->isNotEmpty()){
                    foreach ($product->services(false)->get() as $key => $service){
                        if ($key == 0){
                            $services .= $service->name;
                        } else{
                            $services .= " | $service->name";
                        }
                    }
                }
                return $services;
            })
            ->removeColumn('id')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function resize()
    {
        $images = ProductImage::where('type', 'list')->get();

        foreach ($images as $image) {
            $filename = $image["filename"];
            $path = '/' . auth()->user()->restaurant_id . '/products/images/' . $filename;
            if (Storage::disk('public_uploads')->exists($path)) {
                $img = Image::make(Storage::disk('public_uploads')->path($path));
                $img->fit(104, 104)->save();
            }
        }

        return "Resize successfully!";

    }

}
