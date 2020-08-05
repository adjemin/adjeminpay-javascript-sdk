<?php

namespace AdjeminPay;

use GuzzleHttp\Client;
use AdjeminPay\Transaction;
use AdjeminPay\Exception\AdjeminPayException;
use AdjeminPay\Exception\AdjeminPayBadRequest;
use AdjeminPay\Exception\AdjeminPayHTTPException;
use AdjeminPay\Exception\AdjeminPayConnexionException;

/**
 * AdjeminPay Class
 * 
 * @version 1.0.0
 */
class AdjeminPay{

    
    /**
     * @var string $application_id Application identifier 
     */
    private $application_id;

    /**
     * @var string $apikey Key for API access
     */
    private $apikey;

    /**
     * @var array $data All information about the application or transaction
     */
    public $data;
    
    /**
     * @var string $token Access token
     */
    private $token;

    /**
     * @var array $response Transaction reponse data
     */
    private $response;



    /**
     * Class constructor
     * Initialize some private value and check if they are available
     * 
     * @param string $application_id
     * @param string $apikey
     * 
     * @throws AdjeminPayException
     * @throws AdjeminPayBadRequest
     * @throws AdjeminPayHTTPException
     * @throws AdjeminPayConnexionException 
     */
    public function __construct($application_id, $apikey){
        $this->application_id = $application_id;
        $this->apikey = $apikey;
        $this->checkAvailable();
        $this->retrieveData();
    }


    /**
     * Cheick is ApplicationID and Apikey are available
     * 
     * @throws AdjeminPayException
     * @throws AdjeminPayHTTPException
     * @throws AdjeminPayConnexionException
     */
    private function checkAvailable(){
        $request = new Client();
        $url = "https://dev.adjeminpay.adjemincloud.com/v1/checkCredential";
        $body = [
            'application_id'    =>  $this->application_id,
            'apikey'    =>  $this->apikey
        ];

        $response = $request->post($url, ["form_params" => $body]);

        if ($response->getStatusCode() == 200){
            $body = $response->getBody()->getContents();
            $json = (array) json_decode($body, true);
            try {
                $this->data = $json['data']['data'];
                $this->token  = $json['data']['token'];
            } catch (\Exception $exception) {
               throw new AdjeminPayConnexionException("Access denied", 404);
            }

            if(empty($this->data)){
                throw new AdjeminPayException("Access denied", 404);
            }
        }else{
            throw new AdjeminPayHTTPException("Unauthorized", 401);
        }
    }


    /**
     * Get application data and all setters data
     * 
     * @return array $data
     * 
     * @throws AdjeminPayException
     * @throws AdjeminPayBadRequest 
     */
    public function retrieveData(){
        try {
            $this->checkAvailable();
        } catch (\Exception $exception) {
            throw new AdjeminPayException($exception->getMessage(), $exception->getCode());
        }

        if(!empty($this->data['application'])){
            return $this->data;
        }else{
            throw new AdjeminPayBadRequest("Sorry, No data found", 500);
        }
    }


    /**
     * Get Amount
     * 
     * @return int Amount of the transaction
     */
    public function getAmount(){
        return $this->response['amount'];
    }



    /**
     * Get items
     * 
     * @return string reference of the transaction
     */
    public function getReference(){
        return $this->response['reference'];
    }


    /**
     * Get designation
     * 
     * @return string designation of the transaction
     */
    public function getDesignation(){
        return $this->response['designation'];
    }


    /**
     * Get client_reference
     * 
     * @return string client reference of the transaction
     */
    public function getClientReference(){
        return $this->response['client_reference'];
    }


    /**
     * Get transaction_type
     * 
     * @return string transaction type of the transaction
     */
    public function getTransactionType(){
        return $this->response['transaction_type'];
    }

    /**
     * Get transaction_type
     * 
     * @return string currency code of the transaction
     */
    public function getCurrencyCode(){
        return $this->response['currency_code'];
    }


    /**
     * Get status
     * 
     * @return string status transaction
     */
    public function getStatus(){
        return $this->response['status'];
    }

    /**
     * Get success_meta_data
     * 
     * @return bool isPending transaction
     */
    public function isPending(){
        return $this->response['is_pending'] == 0 ? false : true;
    }


    /**
     * Get success_meta_data
     * 
     * @return bool isBlocked transaction
     */
    public function isBlocked(){
        return $this->response['is_blocked'] == 0 ? false : true;
    }


    /**
     * Get success_meta_data
     * 
     * @return bool isCanceled transaction
     */
    public function isCanceled(){
        return $this->response['is_canceled'] == 0 ? false : true;
    }

    
    /**
     * Get success_meta_data
     * 
     * @return bool isSuccessfull transaction
     */
    public function isSuccessfull(){
        return $this->response['is_successfull'] == 0 ? false : true;
    }



    /**
     * Get paid_at
     * 
     * @return bool paid_at transaction
     */
    public function paidAt(){
        return $this->response['paid_at'];
    }

    /**
     * Get canceled_at
     * 
     * @return string canceledAt transaction
     */
    public function canceledAt(){
        return $this->response['canceled_at'];
    }


    /**
     * Get transaction data by reference
     * 
     * @param string $reference
     * 
     * @return array Transaction
     * 
     * @throws AdjeminPayException
     * @throws AdjeminPayBadRequest
     * @throws AdjeminPayHTTPException
     */
    public function getTransanctionByReference(string $reference){
        
        try {
            $this->checkAvailable();
        } catch (\Exception $exception) {
            throw new AdjeminPayException($exception->getMessage(), $exception->getCode());
        }

        if(empty($reference)){
            throw new AdjeminPayBadRequest("Bad parameter pass to function ", 400);
        }
        
        $request = new Client();
        $url = "https://dev.adjeminpay.adjemincloud.com/v1/transactionRetrieve";
        
        $options = [
            'json' =>    [
                'reference'    =>  $reference
            ],
            'headers'   => [
                'Authorization' =>  'Bearer '.$this->token['access_token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ];

        $response = $request->post($url, $options);
        if ($response->getStatusCode() == 200){
            try {
                $body = $response->getBody()->getContents();
                
                $this->response = (array) json_decode($body, true);

                return new Transaction($this->response);
            } catch (\Exception $exception) {
                throw new AdjeminPayException($exception->getMessage(), $exception->getCode());
            }
        }else{
            throw new AdjeminPayHTTPException($exception->getMessage(), $exception->getCode());
        }
    }
}