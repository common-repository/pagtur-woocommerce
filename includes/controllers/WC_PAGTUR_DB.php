<?php
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PAGTUR_DB {
    
    /**
     * CreateDB
     */
    public static function CreateDB(){
        try{
            //SETTINGS
            self::CreateDBSettings();

            //CURRENCYLIST
            self::CreateDBCurrency();

            //TRANSACTIONS
            self::CreateDBTransactions();
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * CreateDBSettings
     */
    public static function CreateDBSettings(){
        try{
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $tableName     = 'pagtur_woocommerce_settings';

            if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tableName ) ) ) {
                return;
            }

            $sql = "CREATE TABLE $tableName (
                id int NOT NULL AUTO_INCREMENT,
                is_sandbox bool default true,
                pagtur_username varchar(150) default null,
                pagtur_password varchar(150) default null,
                pagtur_companyName varchar(150) default null,
                pagtur_token blob default null,
                currencyCodeID int default null,
                softDescriptor varchar(10) default null,
                travelagency_name varchar(150) default null, 
                travelagency_email varchar(150) default null, 
                travelagency_phone varchar(20) default null,
                PRIMARY KEY (id)
            ) $charset_collate";

            dbDelta( $sql );  
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * CreateDBCurrency
     */
    public static function CreateDBCurrency(){
        try{
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $tableName     = 'pagtur_woocommerce_currencies';

            if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tableName ) ) ) {
                return;
            }

            $sql = "CREATE TABLE $tableName (
                id int NOT NULL AUTO_INCREMENT,
                currency_code varchar(150) default null,
                currency_name varchar(150) default null,
                currency_id int default null,
                PRIMARY KEY (id)
            ) $charset_collate";

            dbDelta( $sql );  
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * CreateDBCurrency
     */
    public static function CreateDBTransactions(){
        try{
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $tableName     = 'pagtur_woocommerce_transactions';

            if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tableName ) ) ) {
                return;
            }

            $sql = "CREATE TABLE $tableName (
                id int NOT NULL AUTO_INCREMENT,
                order_id int default null,
                transactionResponse text character set utf8 default null,
                PRIMARY KEY (id)
            ) $charset_collate";

            dbDelta( $sql );  
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }


    /**
     * SaveSettingsDB
     */
    public static function SaveSettingsDB($is_sandbox, $username, $password, $companyName, $currencyCodeID, $softDescriptor, string $travelagency_name, string $travelagency_email, string $travelagency_phone){
        try{
            global $wpdb;
            $tableName     = 'pagtur_woocommerce_settings';
            self::DeleteSettingsDB($tableName);
            self::InsertSettingsDB($tableName,$is_sandbox, $username, $password, $companyName, $currencyCodeID, $softDescriptor, $travelagency_name, $travelagency_email, $travelagency_phone);
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * DeleteSettingsDB
     */
    public static function DeleteSettingsDB($tableName){
        try{
            global $wpdb;
            $wpdb->query( 
                $wpdb->prepare(
                    "DELETE FROM $tableName",
                    null
                )
            );
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * InsertSettingsDB 
     */
    public static function InsertSettingsDB(string $tableName, bool $is_sandbox, string $username, string $password, string $companyName, $currencyCodeID, string $softDescriptor, string $travelagency_name, string $travelagency_email, string $travelagency_phone){
        try{
            global $wpdb;
            $query = $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO $tableName (is_sandbox, pagtur_username, pagtur_password, pagtur_companyName, currencyCodeID, softDescriptor, travelagency_name, travelagency_email, travelagency_phone)
                    VALUES (%d, %s, %s, %s, %d, %s, %s, %s, %s)",
                    $is_sandbox,
                    $username,
                    $password,
                    $companyName,
                    $currencyCodeID,
                    $softDescriptor,
                    $travelagency_name,
                    $travelagency_email,
                    $travelagency_phone
                )
            );
            return $query;
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
    * GetSettingsDB
    */
    public static function GetSettingsDB(){
        try{
            global $wpdb;
            $tableName     = 'pagtur_woocommerce_settings';
            $query = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $tableName",
                    null
                )
            );
            return $query;
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

   /**
    * SaveTokenDB
    */
    public static function SaveTokenDB($token){
        try{
            global $wpdb;
            $tableName     = 'pagtur_woocommerce_settings';
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $tableName SET pagtur_token = '$token'",
                    null
                )
            );
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * GetCurrencyListDB
     */
    public static function GetCurrencyListDB(){
        try{
            global $wpdb;
            $tableName = 'pagtur_woocommerce_currencies';
            $sql = $wpdb->prepare(
                sprintf("SELECT * FROM $tableName"),
                null
            );
            $query = $wpdb->get_results(
                $sql
            );
            return $query;
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /** 
     * InsertCurrencyDB
     */
    public static function InsertCurrencyDB($currencyList){
        try{
            global $wpdb;
            $tableName = 'pagtur_woocommerce_currencies';
            if (! empty($currencyList)){
                $query = sprintf('INSERT INTO %s (currency_name, currency_code, currency_id) VALUES ',$tableName);
                $values = array();
                $place_holders = array();

                for ($i = 0; $i <= count($currencyList)-1; $i++){
                    array_push( $values, $currencyList[$i]->currency_name, $currencyList[$i]->currency_code, $currencyList[$i]->currency_id);
                    $place_holders[] = "( %s, %s, %s)";
                }
                $query .= implode( ', ', $place_holders );
                $sql = $wpdb->prepare("$query",$values);
                
                $wpdb->query($sql);
            }
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /** 
     * DeleteCurrencyDB
     */
    public static function DeleteCurrencyDB(){
        try{
            global $wpdb;
            $tableName = 'pagtur_woocommerce_currencies';
            $wpdb->query( 
                $wpdb->prepare(
                    "DELETE FROM $tableName",
                    null
                )
            );
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * SaveCurrencyDB
     */
    public static function SaveCurrencyDB(array $currencyList){
        try{
            global $wpdb;
            $tableName     = 'pagtur_woocommerce_currencies';
            self::DeleteCurrencyDB();
            self::InsertCurrencyDB($currencyList);
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * SaveTransactionDB
     */
    public static function SaveTransactionDB($order_id,$transactionResult){
        try{
            global $wpdb;
            $tableName     = 'pagtur_woocommerce_transactions';
            
            if (! empty($order_id) || !empty($transactionResult) ){
                $query = $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO $tableName (order_id, transactionResponse)
                        VALUES (%d, %s)",
                        $order_id,
                        $transactionResult
                    )
                );
                return $query;
            }
        }
        catch(Exception $ex){
            //Exception To Do
        }
    //End Function
    }

    /**
     * GetTransactionDB
     */
    public static function GetTransactionDB($order_id){
        try{
            global $wpdb;
            $tableName = 'pagtur_woocommerce_transactions';
            $sql = $wpdb->prepare(
                sprintf("SELECT * FROM $tableName where order_id = $order_id"),
                null
            );
            $query = $wpdb->get_results(
                $sql
            );
            $jsonResult = $query;
            return $jsonResult;
        }
        catch(Exception $ex){
            //Exception To Do
        }
    }
//End Class
}

?>