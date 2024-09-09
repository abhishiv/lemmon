<?php

namespace App\Http\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Product;
use http\Client\Request;
use Illuminate\Support\Facades\Storage;

class MenuService
{

    public function store($request): Menu
    {
        $menu = Menu::create([
            'title' => $request->title,
            'restaurant_id' => auth()->user()->restaurant_id,
        ]);

        return $menu->fresh();
    }

    public function update(Menu $menu, $request): Menu
    {
        $menu->productcategories()->sync($request->menu_items);

        return $menu;
    }

    public function destroy(Menu $menu): void
    {
        $menu->delete();
    }
}
