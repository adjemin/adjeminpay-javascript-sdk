<?php


// fichier routes/api.php

/** Routes Transaction */
Route::group(['prefix' => 'transaction'], function () {
    // Notification de paiement
    Route::post('notify', [App\Http\Controllers\API\TransactionAPIController::class, 'notifyAdjeminPay'])->name('transaction.notify');

});