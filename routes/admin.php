<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RawMaterialCategoryController;
use App\Http\Controllers\Admin\RawMaterialController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\ProductionMaterialController;
use App\Http\Controllers\Admin\UsedMaterialController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdminDispatchController;
use App\Http\Controllers\Admin\BatchCategoryController;
use App\Http\Controllers\Admin\AdminInvoicesController;
use App\Http\Middleware\BlockUntilApproved;
use Spatie\Permission\Middleware\RoleMiddleware;

use App\Models\User;
use Illuminate\Support\Facades\Auth;



        // Start impersonation
  Route::prefix('admin')->middleware(['auth'])->group(function () {    
   Route::post('/leave-impersonation', function () {

            abort_unless(session()->has('impersonator_id'), 403);

            Auth::loginUsingId(session('impersonator_id'));
            session()->forget('impersonator_id');

            return redirect('/admin/dashboard');
        })->name('admin.leave-impersonation');
});
Route::prefix('admin')->middleware(['auth',RoleMiddleware::using('admin')])->group(function () {


  Route::post('/impersonate/{user}', function (User $user) {

    if ($user->hasRole('admin')) {
        abort(403, 'Cannot impersonate admin');
    }

            if (!$user->hasRole('user')) {
        abort(403, 'Cannot impersonate this user');
    }

            session([
                'impersonator_id' => Auth::id()
            ]);

            Auth::login($user);

            return redirect('/user/dashboard');
        })->name('admin.impersonate');

        // Stop impersonation
     
   

 

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/stock-alerts', [AdminDashboardController::class, 'stockAlerts']
)->name('admin.dashboard.stock-alerts');

    Route::get('/dispatch', [AdminDispatchController::class, 'index']);
    Route::get('/rawmaterials', [RawMaterialController::class, 'index']);
    Route::get('/production', [BatchController::class, 'index']);
    Route::get('/employees', [AdminController::class, 'employees']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/customers', [AdminController::class, 'customers']);
    Route::get('/reports', [AdminController::class, 'reports']);
    Route::get('/settings', [AdminController::class, 'settings']);
    Route::get('/account', [AccountController::class, 'account']);
    Route::get('/stock', [AdminController::class, 'stock']);
    
    // Account Management
     Route::get('/viewaccount', [AccountController::class, 'index'])->name('accounts');

    // Add new user
    Route::post('/accounts/add', [AccountController::class, 'store'])->name('admin.account.add');

    // Update existing user
    Route::put('/accounts/update/{user}', [AccountController::class, 'update'])->name('account.update');

    // Delete user
    Route::delete('/accounts/delete/{user}', [AccountController::class, 'destroy'])->name('account.delete');

    // Approve pending user
    Route::post('/users/approve/{user}', [AccountController::class, 'approve'])->name('users.approve');


    // Raw Materials CRUD
    Route::post('/addrawmaterials', [RawMaterialController::class, 'insert']);
    Route::put('/updaterawmaterials/{id}', [RawMaterialController::class, 'update']);
    Route::delete('/deleterawmaterials/{id}', [RawMaterialController::class, 'destroy']);
    Route::post('/restock-material/{id}', [RawMaterialController::class, 'restock']);
    Route::get('/viewmaterialhistory/{id}', [RawMaterialController::class, 'view']);
    Route::get('/rawmaterials/{id}/export-history', [RawMaterialController::class, 'exportHistory'])
         ->name('admin.rawmaterials.export');


        //categories for raw materials
    // CATEGORY
Route::get('/listcategory', [RawMaterialCategoryController::class, 'listmaterialcategory']);
Route::post('/addcategory', [RawMaterialCategoryController::class, 'addmaterialcategory']);
Route::get('/listsupplier', [RawMaterialCategoryController::class, 'listsupplier']);
Route::post('/addsupplier', [RawMaterialCategoryController::class, 'addsupplier']);
Route::get('/listunit', [RawMaterialCategoryController::class, 'listunit']);
Route::post('/addunit', [RawMaterialCategoryController::class, 'addunit']);
Route::delete('/deletecategory/{id}', [RawMaterialCategoryController::class, 'deletecategory']);
Route::delete('/deletesupplier/{id}', [RawMaterialCategoryController::class, 'deletesupplier']);
Route::delete('/deleteunit/{id}', [RawMaterialCategoryController::class, 'deleteunit']);

    // Production Batch CRUD
   

    // Production Materials
    Route::get('/selectbatch/{id}', [ProductionMaterialController::class, 'index']);
    Route::get('/getcategorymaterials', [ProductionMaterialController::class, 'getmaterials']);
    Route::post('/usedmaterial', [ProductionMaterialController::class, 'useMaterial']);
    Route::get('/showusedmaterials/{id}', [UsedMaterialController::class, 'showUsedMaterials']);
    Route::delete('/deleteusedmaterial/{id}', [UsedMaterialController::class, 'deleteUsedMaterial']);
    Route::get('/restoreallusedmaterials', [UsedMaterialController::class, 'restoreAllUsedMaterials']);
    Route::post('/confirmbatch/{batch_id}', [UsedMaterialController::class, 'confirmBatch']);
    
    //dispatch routes
    Route::get('/dispatchtableajax', [AdminDispatchController::class, 'tableDispatches']);
    Route::post('/sendDispatch', [AdminDispatchController::class, 'sendDispatch']);
    Route::post('/distributeBatch', [AdminDispatchController::class, 'distributeBatch']);
    Route::post('/cancelDispatch', [AdminDispatchController::class, 'cancelDispatch']);
     Route::get('/dispatchcompleted', [AdminDispatchController::class, 'completed']);
     Route::get('/completedDispatchesajax', [AdminDispatchController::class, 'ajaxCompletedDispatches']);

//admin invoices routes
Route::get('/invoice', [AdminInvoicesController::class, 'index']);    
Route::get('/allinvoices', [AdminInvoicesController::class, 'getSalesData']);
Route::get('/download-all', [AdminInvoicesController::class, 'downloadAll']);
Route::get('/viewinvoice/{id}', [AdminInvoicesController::class, 'view'])->name('admin.invoices.show');


    //product
     Route::get('/productlist', [ProductController::class, 'list']);


    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/editprofile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/updatepassword', [AdminProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::delete('/deleteprofile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/email/verification-notification', [AdminProfileController::class, 'sendEmailVerification'])
        ->name('verification.send');


});


Route::prefix('admin')->middleware(['auth', RoleMiddleware::using('admin')])->group(function () {
    Route::get('selectbatch/{id}', [ProductionMaterialController::class, 'index']);
    Route::get('/raw-materials/by-category/{category_id}', [ProductionMaterialController::class, 'getMaterialsByCategory']);
    Route::post('/allocate-material', [ProductionMaterialController::class, 'allocateMaterial']);
    Route::delete('delete-allocation/{id}', [ProductionMaterialController::class, 'deleteAllocation']);
    Route::post('confirmBatchProduct/{id}', [ProductionMaterialController::class, 'confirmBatchProduct']);
    Route::get('/check-allocation/{batchId}/{materialId}', [ProductionMaterialController::class, 'checkAllocation']);
     Route::get('/show-used-materials/{id}', [UsedMaterialController::class, 'showUsedMaterials']);    
    Route::delete('/deleteusedmaterial/{id}', [UsedMaterialController::class, 'deleteUsedMaterial']);
    Route::get('/restoreallusedmaterials', [UsedMaterialController::class, 'restoreAllUsedMaterials']);
});

Route::prefix('admin')->middleware(['auth', RoleMiddleware::using('admin')])->group(function () {
    // Production Management
    Route::get('/production', [BatchController::class, 'index']);
    Route::get('/distribution-users', [BatchController::class, 'getUsersForDistribution']);
    // Batch routes
    Route::get('/batch-data/{id}', [BatchController::class, 'getBatchData']);
    Route::post('/addbatches', [BatchController::class, 'store']);
    Route::put('/updatebatches/{id}', [BatchController::class, 'update']);
    Route::delete('/deletebatches/{id}', [BatchController::class, 'destroy']);
    Route::post('/completebatch/{id}', [BatchController::class, 'completebatch']);

    //completion code
    Route::post('/distributebatch', [BatchController::class, 'distributebatch']);
    Route::get('/allocated-quantity/{batchId}', [BatchController::class, 'getAllocatedQuantity']);
    Route::get('/viewcompletedbatches', [BatchController::class, 'viewcompletebatches']);

  
    
    // Product routes
    Route::get('/batchproducts', [BatchCategoryController::class, 'listbatchproduct']);
    Route::post('/batchproducts', [BatchCategoryController::class, 'addbatchproduct']);
    Route::delete('/deletebatchproduct/{id}', [BatchCategoryController::class, 'destroyproduct']);
    
    // Category routes
    Route::get('/productcategories', [BatchCategoryController::class, 'listProductcategory']);
    Route::post('/addproductcategory', [BatchCategoryController::class, 'addproductcategory']);
    Route::delete('/deletecategory/{id}', [BatchCategoryController::class, 'deletecategory']);
    
    // Quality routes
    Route::get('/productqualities', [BatchCategoryController::class, 'listquality']);
    Route::post('/addquality', [BatchCategoryController::class, 'addquality']);
    Route::delete('/deletequality/{id}', [BatchCategoryController::class, 'deletequality']);
    
    // Other existing routes
    Route::get('/productionmaterial/{id}', [BatchController::class, 'materials']);
});
