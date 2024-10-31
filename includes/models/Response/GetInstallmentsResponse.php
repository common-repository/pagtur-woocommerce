<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GetInstallmentsResponse extends PagTurBaseModel{
    public $installment;
	public $description;
    public $amount;
}

?>