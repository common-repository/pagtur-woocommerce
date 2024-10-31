<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function WC_PagTur_Gateway_Class(){

	class WC_PagTur_Payment_Gateway extends WC_Payment_Gateway  {
		public static $defaults = array(
			'sandbox'					=> 'yes',
			'username'             		=> '',
			'password'             		=> '',
			'companyName' 	            => '',
			'travelagency_name'			=> '',
			'travelagency_email'		=> '',
			'travelagency_phone'		=> ''
		);
		
		/**
		 * Class constructor
		 */
		public function __construct(){
			try{				
				// Load plugin text domain
				$this->pagtur_load_textdomain();

				$this->pagturIncludes();
				WC_PAGTUR_DB::CreateDB();

				
				$this->id                 	= 'wc_pagtur_payment_gateway';
				$this->has_fields			= true;
				$this->method_title       	= __('PagTur for WooCommerce','woocommerce-pagtur' );
				$this->method_description 	= __( 'PagTur Payment Plugin for WooCommerce', 'woocommerce-pagtur' );
				$this->merchant_currency 	= strtoupper( get_woocommerce_currency() );
				
				$this->supports = array(
					'products'
				);

				$this->init_form_fields();

				//Load Settings
				$this->init_settings();
				$this->title			 	= __('Credit Card','woocommerce-pagtur');
				$this->icon					= plugins_url( 'assets/images/pagturcards-175x29.png', plugin_dir_path( __DIR__ ) ) ;
				$this->description			= __('Credit Card','woocommerce-pagtur');
				
				$this->enabled				= $this->get_option('enabled');
				$this->testmode				= 'yes' === $this->get_option('testmode');

				//This action hook saves the settings
				add_action(
					'woocommerce_update_options_payment_gateways_' . $this->id, 
					array($this, 'process_admin_options')
				);

				//We need custom JS to obtain a token
				add_action(
					'wp_enqueue_scripts',
					array($this, 'payment_scripts')
				);

				//If need register a webhook
				//add_action('woocommerce_api_{webhook name}',array($this,'webhook'));
				add_action('woocommerce_api_'.$this->id,array($this, 'webhook'));

				//Call ThankYou
				add_filter('woocommerce_thankyou',array($this,'pagtur_thankyou'));
				
				//Show Additional Info on View Order
				add_action( 'woocommerce_view_order', array($this,'pagtur_thankyou'), 20 );
				
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function			
		}

		

		/**
 		* pagtur_load_textdomain (Translation)
 		*/
		public function pagtur_load_textdomain() {
			try{
				load_plugin_textdomain( 'woocommerce-pagtur', false, WC_PAGTUR_LANGUAGES_DIR );
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		
		/**
		 * PROCESS ADMIN OPTIONS
		 */
		public function process_admin_options(){
			try{
				parent::process_admin_options();

				$sandbox = 1;
				$result = $this->get_option('sandbox');
				if ($result == "yes"){
					$sandbox = 1;
				}
				else {
					$sandbox = 0;
				};
				WC_PAGTUR_DB::SaveSettingsDB(
					$sandbox,
					$this->get_option('username'),
					$this->get_option('password'),
					$this->get_option('companyName'),
					$this->get_option('currencyList'),
					$this->get_option('softDescriptor'),
					$this->get_option('travelagency_name'),
					$this->get_option('travelagency_email'),
					$this->get_option('travelagency_phone')
				);
				//WC_PAGTUR_DB::DeleteCurrencyDB();
			
			}
			catch(Exception $ex){
				//Exception To Do
			}	
		//End Function	
		}


		/**
		 * PagturIncludes
		 */
		private function pagturIncludes(){
			try{
				include_once WC_PAGTUR_INCLUDES_DIR . '/lib/vendor/autoload.php';
				include_once WC_PAGTUR_CONTROLLERS_DIR . 'WC_PAGTUR_API.php';
				include_once WC_PAGTUR_CONTROLLERS_DIR . 'WC_PAGTUR_DB.php'; 
				include_once WC_PAGTUR_MODELS_DIR . '/pagtur_basemodel.php';
				
				include_once WC_PAGTUR_MODELS_DIR . '/Response/GetTokenResponse.php';
				include_once WC_PAGTUR_MODELS_DIR . '/Response/GetInstallmentsResponse.php';
				include_once WC_PAGTUR_MODELS_DIR . '/Response/GetCurrencyListResponse.php';
				include_once WC_PAGTUR_MODELS_DIR . '/Response/GetCurrencyRateResponse.php';
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		
		/**
		 * Plugin options
		 */
		public function init_form_fields(){
			try{
				$fields = array(
					
					//TravelAgency Title
					'travelagency_title'         => array(
						'title'       => __( 'Travel Agency', 'woocommerce-pagtur' ),
						'type'        => 'title',
						'description' => sprintf( __("Fill your Travel Agency Data", 'woocommerce-pagtur' ) ),
					),

					//TravelAgency_Name
					'travelagency_name'			=> array(
						'title'			=> __('Travel Agency Name','woocommerce-pagtur'),
						'label'			=> __('Travel Agency Name','woocommerce-pagtur'),
						'type'			=> 'text'
					),

					//TravelAgency_Email
					'travelagency_email'		=> array(
						'title'			=> __('Travel Agency Email','woocommerce-pagtur'),
						'label'			=> __('Travel Agency Email','woocommerce-pagtur'),
						'type'			=> 'text'
					),

					//TravelAgency_Phone
					'travelagency_phone'		=> array(
						'title'			=> __('Travel Agency Phone','woocommerce-pagtur'),
						'label'			=> __('Travel Agency Phone','woocommerce-pagtur'),
						'type'			=> 'text'
					),

					//Integration Title
					'integration_title'         => array(
						'title'       => __( 'Integration', 'woocommerce-pagtur' ),
						'type'        => 'title',
						'description' => sprintf( __("Contact our team to obtain your integration key. Visit <a href='https://www.pagtur.com.br/'>PagTur</a>", 'woocommerce-pagtur' ) ),
					),
		
					// 'sandbox'					=> 'yes',
					'sandbox' 			=> array(
						'title'			=> __('Sandbox Mode','woocommerce-pagtur'),
						'label'			=> __('Enable Sandbox Mode', 'woocommerce-pagtur'),
						'description'	=> __('With this option, all processed transactions are not be valid','woocommerce-pagtur'),
						//'type'			=> 'checkbox',
						'type'			=> 'checkbox',
						'desc_tip'		=> true,
					),
		
					// 'username'             		=> '',
					'username'			=> array(
						'title'			=> __('Integration UserName','woocommerce-pagtur'),
						'type'			=> 'text',
					),
		
					// 'password'             		=> '',
					'password'			=> array(
						'title'			=> __('Integration Password','woocommerce-pagtur'),
						'type'			=> 'password',
					),
		
					// 'companyName' 	            => '',
					'companyName'		=> array(
						'title'			=> __('Integration Company','woocommerce-pagtur'),
						'type'			=> 'text',
					),

					//Advanced Title
					'advanced_title'         => array(
						'title'       => __( 'Advanced Options', 'woocommerce-pagtur' ),
						'type'        => 'title',
					),
					
					//SoftDescriptor
					'softDescriptor'			=> array(
						'title'			=> __('SoftDescriptor (Max 10 chars)','woocommerce-pagtur'),
						'type'			=> 'text',
						'placeholder'	=> __('Define how show your company into Credit Card bills','woocommerce-pagtur'),
					),

					'WooCommerceCurrency'		=> array(
						'title'			=> sprintf(__('Your WooCommerce Currency is %s','woocommerce-pagtur'),get_woocommerce_currency()),
						'type'			=> 'title',
					),
					//CurrencyList
					'currencyList'				=> array(
						'title'			=>	__('Company Currency','woocommerce-pagtur'),
						'type'			=> 'select',
						'placeholder'	=>	__('Your available exchange currencies','woocommerce_pagtur'),
						'options'		=> $this->LoadCurrencyList(),
						'description'	=> __("You must 'Save Changes' with your valid credentials before get your available currencies list",'woocommerce-pagtur'),
					),

				);
				


				$this->form_fields = apply_filters( 'pagtur_settings_form_fields', $fields );
				$this->inject_defaults();
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function
		}
		
		/**
		 * LoadCurrencyList
		 */
		private function LoadCurrencyList(){
			try{
				$pagturAPI = WC_Pagtur_API::GetCurrencyList();
				$currencyList['0'] = 'Real (R$)';
				if (!empty($pagturAPI)){
					foreach ($pagturAPI as $item){
						$currencyList[$item->currency_id] = sprintf(
							'%s (%s)',
							$item->currency_name, 
							$item->currency_code
						);
					}
				}
				return $currencyList;
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		/**
		 * Inject Default Values
		 */
		private function inject_defaults() {
			try{
				foreach ( $this->form_fields as $field => &$properties ) {
					if ( ! isset( self::$defaults[ $field ] ) ) {
						continue;
					}
		
					$properties['default'] = self::$defaults[ $field ];
				}
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		/**
		 * Used to custom credit card form
		 */
		public function payment_fields(){
			try{
				global $woocommerce, $post, $order_id;
				if (is_checkout()){
					wc_get_template(
						'pagtur-gateway-payment-form.php',
						array(
							'order_id'    						=> $order_id,
							'ajax_url'    						=> admin_url( 'admin-ajax.php' ),
							// 'card_option'                       => $this->credit_card,
							'pagtur_cardholdername'             => __('Cardholder Name','woocommerce-pagtur' ),
							'pagtur_cpf'						=> __('CPF','woocommerce-pagtur'),
							'pagtur_birthdate'					=> __('BirthDate','woocommerce-pagtur'),
							'pagtur_creditcardnumber'			=> __('CreditCard Number','woocommerce-pagtur'),
							'pagtur_expirationMonthYear'		=> __('Expiration Month Year','woocommerce-pagtur'),
							'pagtur_cvv'						=> __('CVV','woocommerce-pagtur'),
							'pagtur_installments'				=> __('Installments','woocommerce-pagtur'),
							'pagtur_total_amount'				=> __('Total Amount','woocommerce-pagtur'),
							
						),
						'',
						WC_PAGTUR_VIEWS_DIR
					);


					// I will echo() the form, but you can close PHP tags and print it directly in HTML
					echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style=background:#F3F3F3;">';
					
					// Add this action hook if you want your custom payment gateway to support it
					do_action('woocommerce_credit_card_form_start',$this->id);

					// I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
					
					do_action('woocommerce_credit_card_form_end',$this->id);
					echo '<div class="clear"></div></fieldset>';
				}
			}
		
			catch (Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		/**
		 * Custom CSS and JS
		 */
		public function payment_scripts(){
			try{
				//CSS
				wp_enqueue_style(
					'wc_pagtur_css',
					plugins_url( 'assets/css/wc_pagtur.css', plugin_dir_path( __DIR__ ) )
				);

				//SCRIPT

				wp_enqueue_script(
					'jquery_mask',
					plugins_url( 'assets/js/jquery.mask.min.js', plugin_dir_path( __DIR__ ) )
				);
				
				wp_enqueue_script(
					'wc_pagtur_validators',
					plugins_url( 'assets/js/wc_pagtur_validators.js', plugin_dir_path( __DIR__ ) )
				);
			}
			catch(Exception $ex){
				//Exception To Do
			}	
		//End Function
		}

		/**
		 * Fields validation
		 */
		public function validate_fields(){
			try{
				if (
					empty($_POST['pagtur_woocommerce_plugin'] 
					|| $_POST['payment_method'] != "wc_pagtur_payment_gateway")
				){
					return;	
				}
				//PagTur Checkout Fields
				
				//pagtur_cardholdername
				$pagtur_cardholdername = empty($_POST['pagtur_cardholdername']) ? '' : $_POST['pagtur_cardholdername'];
				// if ($(this).val() == "" || $(this).val().length < 5) {
				if (empty($pagtur_cardholdername) || strlen($pagtur_cardholdername) < 5 ){
					wc_add_notice(__('Card Holder Name is required','woocommerce-pagtur'),'error');
					return false;
				}
				
				//pagtur_cpf
				$pagtur_cpf = empty($_POST['pagtur_cpf']) ? '' : $_POST['pagtur_cpf'];
				// if ($(this).val() == "" || $(this).val().length < 14 || !verifyCPF($(this).val())) {
				if (empty($pagtur_cpf) || strlen($pagtur_cpf) < 14 || ! $this->verifyCPF($pagtur_cpf) ){
					wc_add_notice(__('CPF is required','woocommerce-pagtur'),'error');
					return false;
				}

				//pagtur_birthdate
				$pagtur_birthDate = empty($_POST['pagtur_birthdate']) ? '' : $_POST['pagtur_birthdate'];
				// if ($(this).val() == "" || $(this).val().length < 10 || !validateBirth($(this).val())) {
				if (empty($pagtur_birthDate) || strlen($pagtur_birthDate) < 10 || ! $this->validateBirth($pagtur_birthDate) ){
					wc_add_notice(__('Birth Date is required','woocommerce-pagtur'),'error');
					return false;
				}

				//pagtur_creditcardnumber
				$pagtur_creditcardnumber = empty($_POST['pagtur_creditcardnumber']) ? '' : $_POST['pagtur_creditcardnumber'];
				// if ($(this).val() == "" || !validateCreditCard($(this).val())) {
				if (empty($pagtur_creditcardnumber) || ! $this->validateCreditCard($pagtur_creditcardnumber) ){
					wc_add_notice(__('Credit Card Number is required','woocommerce-pagtur'),'error');
					return false;
				}

				//pagtur_expirationMonthYear
				$pagtur_expirationMonthYear = empty($_POST['pagtur_expirationMonthYear']) ? '' : $_POST['pagtur_expirationMonthYear'];
				if (empty($pagtur_expirationMonthYear) ){
					wc_add_notice(__('Expiration Month and Year is required','woocommerce-pagtur'),'error');
					return false;
				}

				//pagtur_installments
				$pagtur_installments = empty($_POST['pagtur_installments']) ? '' : $_POST['pagtur_installments'];
				// if ($(this).val() == "" || $(this).val().length < 5) {
				if (empty($pagtur_installments) || $pagtur_installments == "0" ){
					wc_add_notice(__('Installments is required','woocommerce-pagtur'),'error');
					return false;
				}
				else {
					// var isValid = moment($(this).val(), "MM-YY").isValid();
					// $isValid = validateDate($pagtur_expirationMonthYear,'m-y');
					list ($mes, $ano) = preg_split('/[\/.-]/', $pagtur_expirationMonthYear);
					$isValid = checkdate($mes, '01', $ano);
					// var diffMonth = 0;
					$diffMonth = 0;
					// 	if (isValid) diffMonth = moment($(this).val(), "MM-YY").diff(moment(), 'month');
					if ($isValid){
						// diffMonth = moment($(this).val(), "MM-YY").diff(moment(), 'month');
						$dateNow = date_create(date('y-m-d'));
						$expirationDate = sprintf('%s-%s-%s',$ano, $mes, '01');	
						$expirationDate = date_create($expirationDate);
						$dateDiff = date_diff($expirationDate, $dateNow);
						$diffMonth = $dateDiff->format('%m'); 
					}
					// 	if ($(this).val() == "" || !isValid || diffMonth < 0) {
					if (empty($pagtur_expirationMonthYear) || ! $isValid || $diffMonth < 0  ){
						wc_add_notice(__('Expiration Month and Year is required','woocommerce-pagtur'),'error');
						return false;
					}
				}
				

				//pagtur_cvv     
				$pagtur_cvv = empty($_POST['pagtur_cvv']) ? '' : $_POST['pagtur_cvv'];
				// if ($(this).val() == "" || $(this).val().length < 3 || $(this).val().length > 4) {
				if (empty($pagtur_cvv) || strlen($pagtur_cvv) < 3 || strlen($pagtur_cvv) > 4 ){
					wc_add_notice(__('CVV is required','woocommerce-pagtur'),'error');
					return false;
				}

				//WooCommerce Default Fields
				if( empty( $_POST[ 'billing_first_name' ]) ) {
					wc_add_notice(  'First name is required!', 'error' );
					return false;
				}
		
				return true;
			}
			catch (Exception $ex){
				//Exception To Do
				wc_add_notice(  'Something go wrong!', 'error' );
				return false;
			}
		//End Function
		}

		/**
		 * VerifyCPF
		 */
		private function verifyCPF($pagtur_cpf){
			try{
				if (empty($pagtur_cpf)){
					wc_add_notice('CPF is required!','error');
					return false;
				}
				else {
					// 	cpf = cpf.replace(/[^\d]+/g, '');
					$cpf = $pagtur_cpf;
					$pattern = '/[^\d]/';
					$replacement = '';
					$cpf = preg_replace($pattern, $replacement, $cpf);
					$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
		

					// 	if (cpf == '' || cpf.length != 11) return false;
					if (empty($cpf) || strlen($cpf) != 11){
						wc_add_notice('A valid CPF is required!','error');
						return false;
					}


					// 	var resto;
					$resto = null;

					// 	var soma = 0;
					$soma = 0;
		
					// 	if (cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999" || cpf == "12345678909") return false;
					if ($cpf == "00000000000" || $cpf == "11111111111" || $cpf == "22222222222" || $cpf == "33333333333" || $cpf == "44444444444" || $cpf == "55555555555" || $cpf == "66666666666" || $cpf == "77777777777" || $cpf == "88888888888" || $cpf == "99999999999" || $cpf == "12345678909") {
						wc_add_notice('A valid CPF is required!','error');
						return false;
					}
		
					for ($pos = 9; $pos < 11; $pos++) {
						
						for ($resto = 0, $i = 0; $i < $pos; $i++) {
							$resto += $cpf{$i} * (($pos + 1) - $i);
						}
						$resto = ((10 * $resto) % 11) % 10;

						if ($cpf{$i} != $resto) {
							wc_add_notice('A valid CPF is required!','error');
							return false;
						}
					}

					return true;
				}
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		/**
		 * validateCreditCard
		 */
		private function validateCreditCard($pagtur_creditcardnumber){
			try{
				$pagtur_creditcardnumber = str_replace(' ', '', $pagtur_creditcardnumber);
				settype($pagtur_creditcardnumber, 'string');
				$sumTable = array(
				array(0,1,2,3,4,5,6,7,8,9),
				array(0,2,4,6,8,1,3,5,7,9));
				$sum = 0;
				$flip = 0;
				for ($i = strlen($pagtur_creditcardnumber) - 1; $i >= 0; $i--) {
					$sum += $sumTable[$flip++ & 0x1][$pagtur_creditcardnumber[$i]];
				}
				return $sum % 10 === 0;
			}
			catch (Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		/**
		 * validateBirth
		 */
		private function validateBirth($pagtur_birthDate){
			try{
				if (empty($pagtur_birthDate)){
					wc_add_notice('Birth Date is required!','error');
					return false;
				}
				else {
					$birth = $pagtur_birthDate;
					$pattern = '/^[12][0-9]{3}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12][0-9]|3[01])$/';
					
					list ($dia, $mes, $ano) = preg_split('/[\/.-]/', $birth);
					$date = sprintf('%s-%s-%s',$ano, $mes, $dia);
					return preg_match($pattern,$date);
					
				}
			}
			catch(Exception $ex){
				//Exception To Do
			}	
		//End Function
		}

		/**
		 * Processing payment here
		 */
		public function process_payment($order_id){
			try{
				$order = wc_get_order( $order_id );
				if ($order->payment_method == "wc_pagtur_payment_gateway"){
					$postData = $_POST;
					$jsonResult = '';
					$pagturAPI = WC_PAGTUR_API::CreatePayment($order, $postData);
					$jsonResult = json_encode($pagturAPI);
					$jsonDecode = json_decode($jsonResult,true);
					$responseCode = $jsonDecode['status']['response-code'];
					
					if (empty($jsonResult)){
						wc_add_notice(  __('Sorry. Try again','woocommerce-pagtur'), 'error' );
						return;
					}
					if ($responseCode != 1){
						wc_add_notice(
							sprintf(
								__("Ops! Transaction not approved",'woocommerce-pagtur'),
								$responseCode
							),
							'error'
						);
						return;
					}
					else {
						$order->payment_complete();
						// $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
						$saveTransactionDB = WC_PAGTUR_DB::SaveTransactionDB($order->id, $jsonResult);
						
						//Redirect to the thank you page
						$array = array(
							'result' => 'success',
							'redirect' => $this->get_return_url( $order )
						);
						return $array;
					}
				}
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function
		}

		/**
		 * Used to webhook
		 */
		public function webhook(){
			try{
			//Action To Do
			}
			catch(Exception $ex){
				//Exception To Do
			}
		//End Function	
		}

		/** 
		 * ThankYou_Page 
		 */
		function pagtur_thankyou($order_id){
			try{
				$order = wc_get_order( $order_id );
				if ($order->payment_method == "wc_pagtur_payment_gateway"){
					if (!empty($order_id)){
						$apiTransactionResult = WC_PAGTUR_DB::GetTransactionDB($order_id);
						
						$jsonResult = json_decode($apiTransactionResult[0]->transactionResponse, true);
						
						$order = wc_get_order($order_id);
						wc_get_template(
							'pagtur-thankyou.php',
							array(
								'pagtur_total_amount'				=> $jsonResult['amount'],
								'pagtur_installments'				=> $jsonResult['installment'],
								'pagtur_auth_code'					=> $jsonResult['status']['auth-code'],
								'pagtur_receipt'					=> $jsonResult['status']['receipt'],
								'order_id'    						=> $order_id,
								'order_amount'						=> $order->get_total(),
								'order_status'						=> $order->get_status(),
								'order_currency'					=> $order->get_order_currency(),
								'order_payment_method'				=>  $order->payment_method,
								'order_billing_country'				=> get_post_meta( $order->id, '_billing_country', true ),
								'order_customer_email'     			=> $order->billing_email,
								'order_customer_name'      			=> $order->billing_first_name,
								'order_customer_last_name' 			=> $order->billing_last_name,
							),
							'',
							WC_PAGTUR_VIEWS_DIR
						);
						wp_enqueue_script(
							'pagtur_typ_js',
							plugins_url( 'assets/js/wc_pagtur_typ.js', plugin_dir_path( __DIR__ ) )
						);
				
						//CSS
						wp_enqueue_style(
							'wc_pagtur_css',
							plugins_url( 'assets/css/wc_pagtur.css', plugin_dir_path( __DIR__ ) )
						);
					}
				}
			}
			catch(Exception $ex){
				//Exception To Do
			}

		}

	//end main class
	}
//end main function
}
?>