<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Services\MenuService;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(protected MenuService $menuService)
    {
    }

    public function create(): Factory|View|Application
    {
        $menus = Menu::all();
        $categories = ProductCategory::all();
        $products = Product::all();
        $services = Service::all();

        return view('manager.menus.create', compact('menus', 'categories', 'products', 'services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->request->add(['restaurant_id' => auth()->user()->restaurant_id]);
        $menu = $this->menuService->store($request);

        return redirect()->route('manager.menu.edit', $menu->id)->with(['success' => 'Menu has been saved']);
    }


    public function edit(Menu $menu): Factory|View|Application
    {
        $categories = ProductCategory::all();

        return view('manager.menus.edit', compact('menu', 'categories'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $this->menuService->update($menu, $request);

        return redirect()->route('manager.menu.edit', $menu)->with(['success' => 'Menu has been updated']);
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->menuService->destroy($menu);

        return redirect()->route('manager.menu.create')->with(['success' => 'Menu has been removed']);
    }
}
