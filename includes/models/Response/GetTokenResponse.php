<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GetTokenResponse extends PagTurBaseModel{
	public $access_token;
	public $token_type;
    public $expires_in;
    public $name;
}

?>