<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\SaleController;
use App\Http\Controllers\User\InvoicesController;
use App\Http\Controllers\User\ReportsController;
use App\Http\Controllers\User\UserProductsController;
use App\Http\Controllers\User\UserDispatchController;
use App\Http\Controllers\User\UserPurchaseController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleItemController as ControllersSaleItemController;
use App\Http\Controllers\User\SaleItemController;
use App\Http\Controllers\User\SaleController as UserSaleController;
use App\Http\Controllers\User\UserCustomerController;

use Spatie\Permission\Middleware\RoleMiddleware;

Route::prefix('user')->middleware(['auth', RoleMiddleware::using('user')])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('user.userproducts.dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('user.dashboard.data');  

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('user.profile.edit');
    Route::patch('/editprofile', [ProfileController::class, 'update'])->name('user.profile.update');
    Route::put('/updatepassword', [ProfileController::class, 'updatePassword'])->name('user.profile.updatePassword');
    Route::delete('/deleteprofile', [ProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::post('/email/verification-notification', [ProfileController::class, 'sendEmailVerification'])
        ->name('user.verification.send');

   //sales

   Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/', [CartController::class, 'store'])->name('cart.store');
    Route::put('/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/count', [CartController::class, 'count'])->name('cart.count');
});



  
   Route::get('/sale/create', [SaleController::class, 'create'])->name('sales.create'); // Show create sale page
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
Route::post('sales/{sale}/return', [SaleController::class, 'return'])->name('sales.return');
Route::get('sales/{sale}', [SaleController::class, 'show'])->name('user.sales.show');
Route::get('products/ajax', [SaleController::class, 'ajaxList'])->name('user.products.ajaxList');

 Route::get('/saleitems', [SaleItemController::class, 'index'])->name('saleitems.index');
Route::post('/saleitems/return/{id}', [SaleItemController::class, 'returnItem'])->name('saleitems.return');
Route::get('user/saleitems/list', [SaleItemController::class, 'getSaleItems'])
    ->name('user.saleitems.list');


Route::get('/customers/search', [UserCustomerController::class, 'search'])->name('user.customers.search');
Route::post('/customers', [UserCustomerController::class, 'store'])->name('user.customers.store');
Route::get('/customers/{customer}', [UserCustomerController::class, 'show'])->name('user.customers.show');
Route::get('customers/ajax-search', [SaleController::class, 'getCustomers'])->name('customers.ajaxSearch');

   


   
    // Products & Purchase
    Route::get('/products', [UserProductsController::class, 'index'])->name('user.products.index');



    

    Route::get('/purchase', [UserPurchaseController::class, 'index'])->name('user.purchase.index');
    Route::post('/purchase', [UserPurchaseController::class, 'store'])->name('user.purchase.store');
    Route::get('categories', [UserPurchaseController::class, 'getCategories'])->name('user.purchase.categories');
    Route::get('suppliers', [UserPurchaseController::class, 'getSuppliers'])->name('user.purchase.suppliers');
  Route::get('/purchase/latest', [UserPurchaseController::class, 'latestPurchases'])->name('user.purchase.latest');

    Route::get('/latest', [UserPurchaseController::class, 'latestPurchases'])->name('user.purchase.latest');
Route::get('purchases/{purchase}/edit', [UserPurchaseController::class, 'edit'])->name('user.purchase.edit');

     Route::put('/purchase/{id}', [UserPurchaseController::class, 'update'])->name('purchase.update');  
    Route::delete('/purchase/{id}', [UserPurchaseController::class, 'destroy'])->name('purchase.destroy');

// Invoices
Route::prefix('invoices')->group(function () {
    Route::get('/', [InvoicesController::class, 'index'])->name('user.invoices.index');
    Route::get('/download-all', [InvoicesController::class, 'downloadAll'])->name('user.invoices.downloadAll');
    Route::get('/search', [InvoicesController::class, 'search'])->name('user.invoices.search');
    Route::get('/details/{id}', [InvoicesController::class, 'getInvoiceDetails'])->name('user.invoices.details');

    Route::get('/{id}/generate-pdf', [InvoicesController::class, 'generatePDF'])->name('user.invoices.generatePDF');
    Route::get('/{id}/print', [InvoicesController::class, 'print'])->name('user.invoices.print');
    Route::get('/{id}', [InvoicesController::class, 'show'])->name('user.invoices.show');
    Route::get('user/invoices/datatables', [InvoicesController::class, 'datatables'])->name('user.invoices.datatables');
   Route::get('invoices/{id}', [InvoicesController::class, 'print'])->name('user.invoices.show');

    Route::patch('/paymentstatus/{sale}', [InvoicesController::class, 'updatePaymentStatus']);

});

    // Dispatch
    Route::get('dispatch', [UserDispatchController::class, 'index'])->name('user.dispatch.index');
    Route::get('dispatch/server-side', [UserDispatchController::class, 'serverSideDispatch'])->name('user.dispatch.serverSide');
    Route::post('dispatch/confirm-receive', [UserDispatchController::class, 'confirmReceive'])->name('user.dispatch.confirmReceive');

});