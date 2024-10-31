<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PAGTUR_API{
    private static $token;
    private static $apiURI;
    private static $client;

    /**
     * Class Construct
     */
    public function __construct() {
        try{
            $credentials = WC_PAGTUR_DB::GetSettingsDB();
            if (!empty($credentials)){

                foreach ($credentials as $item){
                    $is_sandbox = $item->is_sandbox;
                    $username = $item->pagtur_username;
                    $password = $item->pagtur_password;
                    $companyName = $item->pagtur_companyName;
                    self::$token = $item->pagtur_token;
                }
                
                self::$client = new GuzzleHttp\Client(['verify' => false]);
                
                if ($is_sandbox){
                    self::$apiURI = PAGTUR_SANDBOX_URI;
                } else {
                    self::$apiURI = PAGTUR_PRODUCTION_URI;
                }
                
                if (empty(self::$token)){
                    self::GetToken();
                }
            }
        }
        catch (Exception $ex){
            //Exception To Do
        }
    //end function
    }

    /**
     * GetToken
     */
    public static function GetToken(){
        try{
            $credentials = WC_PAGTUR_DB::GetSettingsDB();
            if (!empty($credentials)){
                foreach ($credentials as $item){
                    $username = $item->pagtur_username;
                    $password = $item->pagtur_password;
                    $companyName = $item->pagtur_companyName;
                }
                
                $res = self::$client->request('POST',self::$apiURI . '/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'username'=>$username,
                        'password'=>$password,
                        'companyName'=>$companyName,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                ]);
            
                $jsonContent = json_decode($res->getBody()->getContents());
                $getToken = new GetTokenResponse();
                $responseAPI = $getToken::Deserialize($jsonContent);
                self::$token = $responseAPI->access_token;
                WC_PAGTUR_DB::SaveTokenDB(self::$token);
            }
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //end function
    }

    /**
     * GetInstallments
     */
    public static function GetInstallments(float $amount, int $transactionType, $rateToken){
        try{
            $pagturAPI = new self();
            if (empty($rateToken)){
                $queryString = sprintf(
                    '/v1/installment?amount=%s&transactionType=%s',
                    number_format($amount,2,'.',''),
                    $transactionType
                );
            }
            else{
                $queryString = sprintf(
                    '/v2/installment?amount=%s&transactionType=%s&rateToken=%s',
                    number_format($amount,2,'.',''),
                    $transactionType,
                    $rateToken
                );
            }
            

            $res = self::$client->request('GET',self::$apiURI . $queryString, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . self::$token 
                ],
            ]);

            if ($res->getStatusCode() == 401){
                self::GetToken();
                $res = self::$client->request('GET',self::$apiURI . $queryString, [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . self::$token 
                    ],
                ]); 
            }
            $getInstallments = new GetInstallmentsResponse();
            $json = json_decode($res->getBody()->getContents(),true);
            $responseAPI = $getInstallments::DeserializeArray($json["installment-plans"]);
            return $responseAPI;
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //end function
    }

    /**
     * GetCurrencyList
     */
    public static function GetCurrencyList(){
        try{
            $currencyList = WC_PAGTUR_DB::GetCurrencyListDB();
            if (empty($currencyList)){
                $responseAPI = '';
                $pagturAPI = new self();
                if (isset(self::$apiURI)){
                    $res = self::$client->request('GET',self::$apiURI . '/v1/currency', [
                        'headers' => [
                            'Content-Type'  => 'application/json',
                            'Authorization' => 'Bearer ' . self::$token 
                        ],
                    ]);
                    if ($res->getStatusCode() == 401){
                        self::GetToken();
                        $res = self::$client->request('GET',self::$apiURI . '/v1/currency', [
                            'headers' => [
                                'Content-Type'  => 'application/json',
                                'Authorization' => 'Bearer ' . self::$token 
                            ],
                        ]); 
                    }
                    $getCurrencyList = new GetCurrencyListResponse();
                    $json = json_decode($res->getBody()->getContents(),true);
                    $responseAPI = $getCurrencyList::DeserializeArray($json);

                    WC_PAGTUR_DB::SaveCurrencyDB($responseAPI);
                }
                return $responseAPI;
            }
            else {
                return $currencyList;
            }
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //end function
    }

    /**
     * GetCurrencyRate
     */
    public static function GetCurrencyRate(int $currencyCodeID){
        try{
            $pagturAPI = new self();
            $queryString = sprintf('/v1/currency?currencyCode=%s',$currencyCodeID);
            $res = self::$client->request('GET',self::$apiURI . $queryString, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . self::$token 
                ],
            ]);
            if ($res->getStatusCode() == 401){
                self::GetToken();
                $res = self::$client->request('GET',self::$apiURI . $queryString, [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . self::$token 
                    ],
                ]);
            }
            $getCurrencyList = new GetCurrencyRateResponse();
            $json = json_decode($res->getBody()->getContents(),true);
            $responseAPI = $getCurrencyList::Deserialize($json);
            return $responseAPI;
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //end function
    }

    /**
     * Payment Request
     */
    public static function CreatePayment(WC_Order $order, $postData){
        try{
            $referrerURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            
            global $wp_version;
            $apiSettings = WC_PAGTUR_DB::GetSettingsDB();
            $pagturAPI = new self();
            if (!empty($apiSettings)){
                foreach ($apiSettings as $item){
                    $travelagency_name  = $item->travelagency_name;
                    $travelagency_emai  = $item->travelagency_email;
                    $travelagency_phone = $item->travelagency_phone;
                    $softdescriptor     = $item->softDescriptor;
                }
            }
            
            
            $postJson = json_encode($postData);
            $postJsonDecode = json_decode($postJson, true);
            
            $cardHolderName = $postJsonDecode['pagtur_cardholdername'];

            


            //CPF
            $customer_cpf = $postJsonDecode['pagtur_cpf'];
            $pattern = '/[^\d]/';
            $replacement = '';
            $cpf = preg_replace($pattern, $replacement, $customer_cpf);
            $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
            $customer_cpf = $cpf;

            //DOB
            $customer_dob = $postJsonDecode['pagtur_birthdate'];
            list ($dia, $mes, $ano) = preg_split('/[\/.-]/', $customer_dob);
            $date = sprintf('%s-%s-%s',$ano, $mes, $dia);
            $customer_dob = $date;
            
            //CardNumber
            $cardNumber = $postJsonDecode['pagtur_creditcardnumber'];
            $pattern = '/\D/';
            $replacement = '';
            $cardNumber = preg_replace($pattern, $replacement, $cardNumber);

            //ExpirationMonthYear
            list ($mes, $ano) = preg_split('/[\/.-]/', $postJsonDecode['pagtur_expirationMonthYear']);
            $expirationMonth = intval($mes);
            $expirationYear = intval($ano);

            $cardCVV = $postJsonDecode['pagtur_cvv'];

            $installments = $postJsonDecode['pagtur_installments'];

            $guest_first_name = $postJsonDecode['billing_first_name'];
            $guest_last_name = $postJsonDecode['billing_last_name'];
            
            $orderData = $order->get_data();
            $orderJson = json_encode($orderData);
            
            $order_id = $order->get_id();
            $amountTotal = $order->get_total();

            $orderItems = $order->get_items();
            foreach ($orderItems as $item_key => $item_value){
                $product_name = $item_value['name'];
                break;
            }
            
            
            
            $transactionType = 'sale';
            $guestCount = 1;
            $embarkation_date = date("Y-m-d");
            $customer_fullname = $cardHolderName;
            $guest_cpf = $customer_cpf;
            $guest_product_name = $product_name;
            $guest_dob = $customer_dob;
            $guest_order_id = $order_id;
            $guest_embarkation_date = $embarkation_date;
            $guest_amount = $amountTotal;
            
            $pluginData = get_plugin_data( WC_PAGTUR_PLUGIN_BASE );

            $guest_extrafield01 = '';
            $guest_extrafield01 =  sprintf("{'pagtur_woocommerce_plugin_version':'%s', 'woocommerce_version':'%s','wordpress_version':'%s'}",
                $pluginData['Version'],
                $order->get_version(),
                $wp_version
            );
            
            $guest_extrafield02 = str_replace('"',"'",$orderJson);

            $guest_extrafield03 = '';
            $guest_extrafield04 = '';

            $paymentRequest = '
                {
                    "transaction-type":"'. $transactionType .'",
                    "order-id":"'.$order_id.'",
                    "card":{
                        "Number":"'. $cardNumber .'",
                        "ExpirationMonth": '. $expirationMonth .',
                        "ExpirationYear": '. $expirationYear .',
                        "CVV":"'. $cardCVV .'",
                        "HolderName": "'. $cardHolderName .'"
                    },';

            //NEW FIELDS
            //$pagtur_currency_id = $_POST['pagtur_currency_id'];
            //$pagtur_rate_token = $_POST['pagtur_rate_token'];
            
            $pagtur_currencyID = $postJsonDecode['pagtur_currency_id'];
            if ($pagtur_currencyID = 0){
                //Add Amount
                $pagtur_amountBRL = $amountTotal;
            }
            else{
                //Add AmountME
                $pagtur_amountME = $amountTotal;
                $paymentRequest .= '"amount-me":'. $pagtur_amountME .',';
                
                //Add RateToken
                $pagtur_rate_token = $postJsonDecode['pagtur_rate_token'];
                $paymentRequest .= '"rate-token":"' . $pagtur_rate_token . '",';
                
                //Calc Amount with Installment
                $pagtur_installments_json = $postJsonDecode['pagtur_installments_json'];
                $installmentContent = json_decode(str_replace('\\','',$pagtur_installments_json));
                foreach ($installmentContent as $item){
                    if ($item->installment == $installments){
                        $pagtur_amountBRL = $installments * $item->amount;
                    }
                }
            }
            //Add Amount
            $paymentRequest .= '"amount": '. $pagtur_amountBRL .',';
        
            $paymentRequest .= '
                    "installment": '. $installments .',
                    "guestcount":'. $guestCount .',
                    "product-name":"'. $product_name .'",
                    "embarkation-date":"'. $embarkation_date . '",
                    "travelagency-name":"'. $travelagency_name . '",
                    "travelagency-email":"'. $travelagency_emai .'",
                    "travelagency-phone":"'. $travelagency_phone . '",
                    "customer":{
                        "cpf":"'. $customer_cpf .'",
                        "fullname": "'. $customer_fullname .'",
                        "dob":"'. $customer_dob .'"
                    },
                    "guests":[{
                        "firstname":"'. $guest_first_name .'",
                        "lastname":"'. $guest_last_name .'",
                        "cpf":"'. $guest_cpf .'",
                        "product-name":"'. $guest_product_name .'",
                        "dob":"'. $guest_dob .'",
                        "order-id":"'. $guest_order_id .'",
                        "embarkation-date":"'. $guest_embarkation_date .'",
                        "amount": '. $guest_amount .',
                        "extrafield01":"'. $guest_extrafield01 .'",
                        "extrafield02":"'. $guest_extrafield02 .'",
                        "extrafield03":"'. $guest_extrafield03 .'",
                        "extrafield04":"'. $guest_extrafield04 .'"
                        
                    }],
                    "referrer-url":"'. $referrerURL .'",
                    "softdescriptor":"'. $softdescriptor .'"
                }';

                
            $res = self::$client->request('POST',self::$apiURI . '/v1/transaction', [
                'headers'  => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . self::$token 
                ],
                'body' => $paymentRequest
            ]);
            if ($res->getStatusCode() == 401){
                self::GetToken();
                $res = self::$client->request('GET',self::$apiURI . '/v1/transaction', [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . self::$token 
                    ],
                    'body' => $paymentRequest
                ]); 
            }
            
            $json = json_decode($res->getBody(),true);
            return $json;
        }
        catch (Exception $ex){
            $error = $ex;
            return;
        }
    //end function
    }
//end class    
}
?>