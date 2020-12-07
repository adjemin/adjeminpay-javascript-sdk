<?php

// fichier App\Http\Controllers\API\TransactionAPIController.php


    /**
     * Intercepter notification de paiement AdjeminPay
     * et mettre à jour les données de transaction de ma bd
     * (commandes, vider panier, créer facture etc)
     */

    public function notifyAdjeminPay(Request $request){
         $input = $request->all();
        //

        $transaction_reference = $input['transaction_id'];
        $status = $input['status'];

        if(empty($transaction_reference)){
            return response()->json([
                'error' => [
                    'message' => "Transaction not found",
                    'transaction_reference' => $transaction_reference
                ],
            ]);
        }

         // Recuperation de la ligne de la transaction dans votre base de données
        $transaction = Transaction::where(['reference' =>  $transaction_reference])->first();

        if(!$transaction){
            return response()->json([
                'status' => "OK",
                'message' => "Transaction Not found",

            ]);
        }

        if ($transaction != null) {

            switch ($status) {
                case 'SUCCESSFUL' :
                    $transaction->status = 'SUCCESSFUL';
                    $transaction->is_paid = true;
                    $transaction->paid_at = Carbon::now();
                    $transaction->save();
                    // Do some more stuff
                break;
                case 'FAILED' :
                    $transaction->status = 'FAILED';
                    $transaction->save();
                    // Do some more stuff
                    break;
                case 'CANCELLED' :
                    $transaction->status = 'CANCELLED';
                    $transaction->save();
                    break;
                case 'EXPIRED' :
                    $transaction->status = 'EXPIRED';
                    $transaction->save();
                    // Do some more stuff
                break;
                default:
                    return response()->json([
                        'error' => [
                            'message' => 'MISSING_TRANSACTION_STATUS'
                        ],
                    ]);
                break;
            }
        }

        // Do Some more stuff

        // operation
        return response()->json([
            'status'=> "OK",
            'messsage' => "received",
            'transaction' => $transaction,
        ]);

    }
