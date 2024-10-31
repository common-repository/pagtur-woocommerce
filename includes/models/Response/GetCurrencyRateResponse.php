<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GetCurrencyRateResponse extends PagTurBaseModel{
    public $currency_name;
    public $currency_code;
    public $rate;
    public $rate_before_vat;
    public $rate_token;
    public $rate_date;
}


?>