<?php
// Fichier routes/web.php

use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

// Ceci pourait être votre route vers un checkout
Route::group(['prefix' => 'transaction'], function(){
    Route::get('/payment', function(){
        // Le code de votre controller
        $transaction = Transaction::create([
            'status' => 'PENDING',
            'reference' => 'ADJEMINPAY_'.Carbon::now()->toString(),
            'amount' => 100,
            'designation' => "Achat de produits de ma boutique",
            'custom' => "Custom data"
        ]);

        $application_id = "XXXXXX";
        $apikey = "XXXXXXXXXXXXXXX";

        return view('adjeminpay.payment', compact('transaction', 'application_id', 'apikey'));
    });
});