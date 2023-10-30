<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BkashPaymentController;
use App\Http\Controllers\BkashRefundController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Payment Routes for bKash
Route::controller(BkashPaymentController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/get-token', 'getToken')->name('bkash.get.token');
        Route::get('/make-payment', 'createPayment')->name('bkash.make.payment');
        Route::post('/execute-payment', 'executePayment')->name('bkash.execute.payment');
        Route::get('/query-payment', 'queryPayment')->name('bkash.query.payment');
        Route::post('/success', 'bkashSuccess')->name('bkash.success');
    });

// Refund Routes for bKash
Route::controller(BkashRefundController::class)->group(function () {
    Route::post('/refund', 'refund');
});
