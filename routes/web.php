<?php

use App\Http\Controllers\Manager\ServiceController;
use Illuminate\Support\Facades\Route;

//Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\SettingController;

//Customer
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\RestaurantController as CustomerController;
use App\Http\Controllers\Customer\PaymentController;

//Manager
use App\Http\Controllers\Manager\DashboardController as ManagerDashboard;
use App\Http\Controllers\Manager\ExtraController;
use App\Http\Controllers\Manager\MenuController;
use App\Http\Controllers\Manager\ProductCategoryController;
use App\Http\Controllers\Manager\ProductController;
use App\Http\Controllers\Manager\RestaurantController as ManagerRestaurant;
use App\Http\Controllers\Manager\RestaurantTableController;
use App\Http\Controllers\Manager\StaffController;
use App\Http\Controllers\Manager\OrderController as ManagerOrder;
use App\Http\Controllers\Manager\RestaurantSettingsController;
use App\Http\Controllers\Manager\FoodTypeController;

//Staff
use App\Http\Controllers\Staff\DashboardController as StaffDashboard;
use App\Http\Controllers\Staff\OrderController as StaffOrder;
use App\Http\Controllers\Staff\CartController as StaffCartController;

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        $role = auth()->user()->roles->pluck('name')->first();

        if ($role === 'staff') {
            return redirect(route($role . '.menu'));
        } else {
            return redirect(route($role . '.dashboard'));
        }
    });
});

Route::get('/set-password', [AdminDashboard::class, 'index'])->name('admin.dashboard');

Route::get('/resize-images', [ProductController::class, 'resize'])->name('product.resize');
Route::group(['group' => 'CUSTOMER', 'prefix' => ''], function () {
    Route::prefix('restaurant')->middleware('validate.slug')->controller(CustomerController::class)->group(function () {
        Route::get('{restaurantSlug}/table/{tableHash}', 'index')->name('customer.menu');
        Route::get('/{restaurantSlug}/product/{productSlug}', 'show')->name('customer.product.show');
        Route::get('/{restaurantSlug}/table/{tableHash}/unavailable',
            'unavailable')->name('customer.restaurant.unavailable');
    });
    Route::post('/onesignal/store', [CustomerController::class, 'onesignal'])->name('customer.onesignal.store');
    Route::prefix('cart')->controller(CartController::class)->group(function () {
        Route::get('/', 'get')->name('customer.cart');
        Route::post('/add', 'add')->name('customer.cart.add');
        Route::post('/update', 'update')->name('customer.cart.update');
        Route::post('/delete', 'delete')->name('customer.cart.delete');
        Route::post('/dine', 'dine')->name('customer.cart.dine');
        Route::post('/refresh', 'refresh')->name('customer.cart.refresh');
        Route::delete('/destroy', 'destroy')->name('customer.cart.destroy');
        Route::get('checkout', [CheckoutController::class, 'list'])->name('customer.cart.checkout');
    });
    Route::prefix('order')->controller(OrderController::class)->group(function () {
        Route::post('/store', 'store')->name('customer.order.store');
        Route::get('/list', 'list')->name('customer.order.list');
        Route::post('/get', 'get')->name('customer.order.get');
        Route::post('/receipt', 'receipt')->name('customer.order.receipt');
        Route::post('/orders-summary', 'summary')->name('customer.orders.summary');
        Route::get('{order}/new-payment', 'tryNewPayment')->name('customer.order.newpayment');
        Route::get('{order}/pay-cash', 'payCash')->name('customer.order.paycash');
    });
    Route::prefix('payment')->controller(PaymentController::class)->group(function () {
        Route::get('/success/{payment}', 'success')->name('customer.payment.success');
        Route::get('/failed/{payment}', 'failed')->name('customer.payment.failed');
        Route::get('/cancel/{payment}', 'cancel')->name('customer.payment.cancel');
    });

    Route::put('group-order', [CustomerController::class, 'groupOrder'])->name('customer.group-order');
});

Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    // Dashboard
    Route::prefix('dashboard')->controller(AdminDashboard::class)->group(function () {
        Route::get('/', 'overview')->name('admin.dashboard');

        // Statistics
        Route::post('statistics', [AdminDashboard::class, 'statistics'])->name('admin.statistics');

        // Export statistics
        Route::post('statistics-export', 'exportStatistics')->name('admin.statistics.export');
        Route::post('statistics-export/get', 'getExportStatistics')->name('admin.statistics.get');
        Route::get('statistics-export/download/{restaurantJob}',
            'downloadExportStatistics')->name('admin.statistics.download');
    });

    Route::prefix('settings')->controller(SettingController::class)->group(function () {
        Route::get('/', 'list')->name('admin.settings.list');
        Route::patch('/update', 'update')->name('admin.settings.update');
    });

    Route::prefix('restaurants')->controller(RestaurantController::class)->group(function () {
        Route::get('/', 'list')->name('admin.restaurant.list');
        Route::get('/data-table', 'dataTable')->name('admin.restaurant.data.table');
        Route::get('/create', 'create')->name('admin.restaurant.create');
        Route::post('/store', 'store')->name('admin.restaurant.store');
        Route::get('/show/{restaurant}', 'show')->name('admin.restaurant.show');
        Route::get('/edit/{restaurant}', 'edit')->name('admin.restaurant.edit');
        Route::put('/update/{restaurant}', 'update')->name('admin.restaurant.update');
        Route::put('/updateStatus/{restaurant}', 'updateStatus')->name('admin.restaurant.update.status');
        Route::delete('/destroy/{restaurant}', 'destroy')->name('admin.restaurant.destroy');
        Route::get('/logo/{restaurant}', 'getLogo')->name('admin.restaurant.logo');
        Route::post('/verify-logo', 'verifyLogo')->name('admin.restaurant.verify.logo');
    });
});

Route::prefix('manager')->middleware([
    'auth',
    'verified',
    'role:manager|staff',
    'restaurant.scope',
    'active'
])->group(function () {
    Route::prefix('dashboard')->controller(ManagerDashboard::class)->group(function () {
        Route::get('/', 'overview')->name('manager.dashboard');
        Route::post('statistics', 'statistics')->name('manager.statistics');
        Route::post('statistics-export', 'exportStatistics')->name('manager.statistics.export');
        Route::post('statistics-export/get', 'getExportStatistics')->name('manager.statistics.get');
        Route::get('statistics-export/download/{restaurantJob}',
            'downloadExportStatistics')->name('manager.statistics.download');
    });

});

Route::prefix('manager')->middleware([
    'auth',
    'verified',
    'role:manager',
    'restaurant.scope',
    'active'
])->group(function () {


    Route::prefix('/restaurant')->controller(ManagerRestaurant::class)->group(function () {
        Route::get('/edit/{restaurant}', 'edit')->name('manager.restaurant.edit');
        Route::put('/update/{restaurant}', 'update')->name('manager.restaurant.update');
        Route::get('/logo/{restaurant}', 'getLogo')->name('manager.restaurant.logo');
        Route::post('/verify-logo', 'verifyLogo')->name('manager.restaurant.verify.logo');
        Route::get('/welcome-screen-image/{restaurant}',
            'getAppWelcomeScreenImage')->name('manager.restaurant.welcome-screen-image');
        Route::post('/verify-welcome-screen-image',
            'verifyAppWelcomeScreenImage')->name('manager.restaurant.verify.welcome-screen-image');
    });

    Route::prefix('products')->controller(ProductController::class)->group(function () {
        Route::get('/', 'list')->name('manager.product.list');
        Route::get('/data-table', 'dataTable')->name('manager.product.data.table');
        Route::get('/create', 'create')->name('manager.product.create');
        Route::get('/create-bundle', 'createBundleItem')->name('manager.product.create.bundle');
        Route::post('/store', 'store')->name('manager.product.store');
        Route::get('/edit/{product}', 'edit')->name('manager.product.edit');
        Route::get('/copy/{product}', 'copy')->name('manager.product.copy');
        Route::put('/update/{product}', 'update')->name('manager.product.update');
        Route::delete('/destroy/{product}', 'destroy')->name('manager.product.destroy');
        Route::get('/images/{product}', 'getImages')->name('manager.product.images');
        Route::post('/verify-image', 'verifyImage')->name('manager.product.verify.image');
    });

    Route::prefix('extras')->controller(ExtraController::class)->group(function () {
        Route::get('/', 'list')->name('manager.extra.list');
        Route::get('/data-table', 'dataTable')->name('manager.extra.data.table');
        Route::get('/create', 'create')->name('manager.extra.create');
        Route::post('/store', 'store')->name('manager.extra.store');
        Route::get('/edit/{extra}', 'edit')->name('manager.extra.edit');
        Route::put('/update/{extra}', 'update')->name('manager.extra.update');
        Route::delete('/destroy/{extra}', 'destroy')->name('manager.extra.destroy');
        Route::get('/images/{extra}', 'getImages')->name('manager.extra.images');
        Route::post('/verify-image', 'verifyImage')->name('manager.extra.verify.image');
    });

    Route::prefix('course')->name('manager.course.')->controller(FoodTypeController::class)->group(function () {
        Route::resource('', FoodTypeController::class)->except(['edit', 'update', 'delete']);

        Route::get('{foodType}/edit', 'edit')->name('edit');
        Route::put('{foodType}/edit', 'update')->name('update');
        Route::delete('{foodType}', 'destroy')->name('destroy');

        Route::get('data-table', 'dataTable')->name('data.table');
        Route::put('reorder', 'reorder')->name('data.table.reorder');
    });
    Route::prefix('product-categories')->controller(ProductCategoryController::class)->group(function () {
        Route::get('/', 'list')->name('manager.product.category.list');
        Route::get('/data-table', 'dataTable')->name('manager.product.category.data.table');
        Route::get('/create', 'create')->name('manager.product.category.create');
        Route::post('/store', 'store')->name('manager.product.category.store');
        Route::get('/edit/{productCategory}', 'edit')->name('manager.product.category.edit');
        Route::put('/update/{productCategory}', 'update')->name('manager.product.category.update');
        Route::put('/update-order', 'updateOrder')->name('manager.product.category.update.order');
        Route::delete('/destroy/{productCategory}', 'destroy')->name('manager.product.category.destroy');
    });
    Route::prefix('tables')->controller(RestaurantTableController::class)->group(function () {
        Route::get('/', 'list')->name('manager.table.list');
        Route::get('/data-table', 'dataTable')->name('manager.table.data.table');
        Route::get('/create', 'create')->name('manager.table.create');
        Route::post('/store', 'store')->name('manager.table.store');
        Route::get('/edit/{table}', 'edit')->name('manager.table.edit');
        Route::put('/update/{table}', 'update')->name('manager.table.update');
        Route::put('/regenerate-qr/{table}', 'regenerateQr')->name('manager.table.regenerate.qr');
        Route::get('/download-zip', 'exportZip')->name('manager.table.export.zip');
        Route::delete('/destroy/{table}', 'destroy')->name('manager.table.destroy');
    });
    Route::prefix('staff')->controller(StaffController::class)->group(function () {
        Route::get('/', 'list')->name('manager.staff.list');
        Route::get('/data-table', 'dataTable')->name('manager.staff.data.table');
        Route::get('/create', 'create')->name('manager.staff.create');
        Route::post('/store', 'store')->name('manager.staff.store');
        Route::get('/edit/{user}', 'edit')->name('manager.staff.edit');
        Route::put('/change-status/{user}', 'changeStatus')->name('manager.staff.change.status');
        Route::put('/update/{user}', 'update')->name('manager.staff.update');
        Route::delete('/destroy/{user}', 'destroy')->name('manager.staff.destroy');
    });
    Route::prefix('menus')->controller(MenuController::class)->group(function () {
        Route::get('/create', 'create')->name('manager.menu.create');
//        Route::post('/store', 'store')->name('manager.menu.store');
        Route::get('/edit/{menu}', 'edit')->name('manager.menu.edit');
        Route::put('/update/{menu}', 'update')->name('manager.menu.update');
        Route::put('/store-item/{menu}', 'storeItems')->name('manager.menu.store.item');
//        Route::delete('/destroy/{menu}', 'destroy')->name('manager.menu.destroy');
    });
    Route::prefix('orders')->controller(ManagerOrder::class)->group(function () {
        Route::get('/', 'list')->name('manager.order.list');
        Route::get('/data-table', 'dataTable')->name('manager.order.data.table');
        Route::get('/show/{order}', 'show')->name('manager.order.show');
    });
    Route::prefix('services')->controller(ServiceController::class)->group(function () {
        Route::get('/', 'list')->name('manager.service.list');
        Route::get('/data-table', 'dataTable')->name('manager.service.data.table');
        Route::get('/create', 'create')->name('manager.service.create');
        Route::post('/store', 'store')->name('manager.service.store');
        Route::get('/edit/{service}', 'edit')->name('manager.service.edit');
        Route::get('/copy/{service}', 'copy')->name('manager.service.copy');
        Route::put('/update/{service}', 'update')->name('manager.service.update');
        Route::put('/update-order', 'updateOrder')->name('manager.service.update.order');
        Route::delete('/destroy/{service}', 'destroy')->name('manager.service.destroy');
    });
    Route::prefix('settings')->controller(RestaurantSettingsController::class)->group(function () {
        Route::get('/', 'list')->name('manager.settings.list');
        Route::patch('/update', 'update')->name('manager.settings.update');
    });
});

Route::prefix('staff')->middleware(['auth', 'verified', 'role:staff', 'restaurant.scope', 'active'])->group(function (
) {
    Route::prefix('tables')->controller(StaffDashboard::class)->group(function () {
        Route::get('/', [StaffDashboard::class, 'tables'])->name('staff.tables');
        Route::post('/list', [StaffDashboard::class, 'getTableList'])->name('staff.tables.list');
        Route::post('/update-status', [StaffDashboard::class, 'updateTableStatus'])->name('staff.tables.update-status');
        Route::post('/remove-order-item', [StaffDashboard::class, 'removeOrderItem'])->name('staff.tables.remove-order-item');
        Route::post('/change-table', [StaffDashboard::class, 'changeTable'])->name('staff.tables.change-table');
        Route::post('/summary', [StaffDashboard::class, 'tableSummary'])->name('staff.tables.summary');
        Route::get('/payment-totals',
            [StaffDashboard::class, 'getTotalsForTable'])->name('staff.tables.payment-totals');
        Route::post('/payment-options',
            [StaffDashboard::class, 'setPaymentOptionsForTable'])->name('staff.tables.payment-options');
        Route::post('/pay', [StaffDashboard::class, 'updatePaymentForTable'])->name('staff.tables.update-payment');
        Route::post('/close-orders', [StaffDashboard::class, 'closeOrdersForTable'])->name('staff.tables.close-orders');
    });

    Route::match(['get', 'post'], '/menu', [StaffDashboard::class, 'menu'])->name('staff.menu');

    Route::prefix('dashboard')->controller(StaffDashboard::class)->group(function () {
        Route::match(['get', 'post'], '/', 'index')->name('staff.dashboard');
        Route::get('/completed', 'completed')->name('staff.dashboard.completed');
        Route::post('/onesignal/store', [StaffDashboard::class, 'onesignal'])->name('staff.onesignal.store');
        Route::post('/payment-options',
            [StaffDashboard::class, 'setPaymentOptions'])->name('staff.dashboard.payment-options');
        Route::get('/payment-totals',
            [StaffDashboard::class, 'getTotalsForPayment'])->name('staff.dashboard.payment-totals');
        Route::post('/pay', [StaffDashboard::class, 'updatePayment'])->name('staff.dashboard.update-payment');
    });

    Route::get('/products', [StaffDashboard::class, 'products'])->name('staff.products');
    Route::post('/products/update', [StaffDashboard::class, 'updateProduct'])->name('staff.products.update');

    Route::prefix('cart')->controller(StaffCartController::class)->group(function () {
        Route::post('/add', 'add')->name('staff.cart.add');
        Route::post('/update', 'update')->name('staff.cart.update');
        Route::post('/store', 'store')->name('staff.cart.store');
        Route::post('/empty', 'empty')->name('staff.cart.empty');
        Route::post('/options', 'setOptions')->name('staff.cart.options');
        Route::post('/notes', 'addNotes')->name('staff.cart.notes');
        Route::get('/totals', 'getTotals')->name('staff.cart.totals');
    });


    Route::get('/tip-list', [StaffDashboard::class, 'viewTipList'])->name('staff.tip.list');
    Route::get('/printer-list', [StaffDashboard::class, 'getPrinters'])->name('staff.printers.list');
    Route::post('/tip-list', [StaffDashboard::class, 'getTipList'])->name('staff.tip.get');
    Route::group(['prefix' => '', 'group' => 'Orders'], function () {
        Route::get('/list', [StaffOrder::class, 'get'])->name('staff.order.get');
        Route::get('/table/list', [StaffOrder::class, 'list'])->name('staff.order.table.list');
        Route::put('/update', [StaffOrder::class, 'update'])->name('staff.order.update');
        Route::post('/receipt', [StaffOrder::class, 'receipt'])->name('staff.order.receipt');
        Route::get('/overview', [StaffOrder::class, 'ordersOverview'])->name('staff.order.overview');
        Route::delete('/cancel/{order}', [StaffOrder::class, 'cancel'])->name('staff.order.cancel');
        Route::get('/orders-to-print', [StaffOrder::class, 'getOrdersToPrint'])->name('staff.order.to-print');
        Route::post('/print-receipt', [StaffOrder::class, 'printReceipt'])->name('staff.order.print.receipt');
        Route::post('/update-print-status/{order}',
            [StaffOrder::class, 'updatePrintStatus'])->name('staff.order.print.update');
    });
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';
