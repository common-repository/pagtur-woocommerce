<?php
/**
 * Pagtur Payment template.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$paymentGatewaySettings = WC()->payment_gateways->payment_gateways()['wc_pagtur_payment_gateway']->settings;
$currencySettings= $paymentGatewaySettings['currencyList'];
$pagturCurrencyList = WC_PAGTUR_DB::GetCurrencyListDB();
$woocommerceCurrency = get_woocommerce_currency();
$currency= $woocommerceCurrency == 'BRL' ? 'BRL' : '';

if (empty($currency)){
    foreach($pagturCurrencyList as $item){
        if (
            $item->currency_id == $currencySettings
            && $item->currency_code == $woocommerceCurrency
        ){
            $currency = $item->currency_code;
        }
    }
}

if (empty($currency)){
    echo '<div class="pagtur-payment-container">
    <div id="pagtur-credit-card">
        <input type="hidden" name="pagtur_woocommerce_plugin" value=""/>
        <section class="pagtur-form-row">
            <p class="woocommerce-error">';
    echo __('Payment not available to this currency','woocommerce-pagtur');
    echo ' </p>
        </section>
    </div>';
    return;
}
else {
?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>






<div class="pagtur-payment-container">
    <div id="pagtur-credit-card">
        <input type="hidden" name="pagtur_woocommerce_plugin" value="pagtur_woocommerce_plugin"/>
        <section class="pagtur-form-row">
            <section class="pagtur-form-row">
                <label for="pagtur_cardholdername"><?php echo $pagtur_cardholdername; ?>:<span class="required">*</span></label>
                <input type="text" 
                    class="input-text pagtur-wc-credit-card-form-holder-name" 
                    name="pagtur_cardholdername" 
                    id="pagtur_cardholdername" 
                    placeholder="" 
                    required
                    data-alert='<?php echo __('Card Holder Name invalid. Must be contains more than 5 letters','woocommerce-pagtur'); ?>'>
            </section>
            <section class="pagtur-form-row pagtur-form-row-first">
                <label for="pagtur_cpf"><?php echo $pagtur_cpf; ?>:<span class="required">*</span></label>
                <input type="text" 
                    class="input-text pagtur-wc-credit-card-form-cpf" 
                    id="pagtur_cpf" 
                    name="pagtur_cpf" 
                    autocomplete="off" 
                    placeholder="000.000.000-00" 
                    maxlength="14" 
                    required
                    data-alert='<?php echo __('CPF invalid','woocommerce-pagtur'); ?>'>
            </section>
            <section class="pagtur-form-row pagtur-form-row-last">
                <label for="pagtur_birthdate"><?php echo $pagtur_birthdate; ?>:<span class="required">*</span></label>
                <input type="text" 
                    name="pagtur_birthdate" 
                    id="pagtur_birthdate" 
                    value="" 
                    autocomplete="off" 
                    placeholder="DD/MM/AAAA" 
                    maxlength="10" 
                    class="input-text pagtur-wc-credit-card-form-birth-date" 
                    required
                    data-alert='<?php echo __('Birth date invalid','woocommerce-pagtur'); ?>'>
            </section>
            <section class="pagtur-form-row">
                <label for="pagtur_creditcardnumber"><?php echo $pagtur_creditcardnumber; ?>:<span class="required">*</span></label>
                <input type="text" 
                    class="input-text wc-credit-card-form-card-number" 
                    name="pagtur_creditcardnumber" 
                    id="pagtur_creditcardnumber" 
                    maxlength="20" 
                    autocomplete="off" 
                    placeholder="•••• •••• •••• ••••" 
                    required
                    data-alert='<?php echo __('Credit Card invalid','woocommerce-pagtur'); ?>'>
            </section>
            <section class="pagtur-form-row pagtur-form-row-first">
                <label for="pagtur_expirationMonthYear"><?php echo $pagtur_expirationMonthYear; ?>:<span class="required">*</span></label>
                <input type="text" 
                    class="input-text pagtur-wc-credit-card-form-card-expiry" 
                    id="pagtur_expirationMonthYear" 
                    name="pagtur_expirationMonthYear" 
                    autocomplete="off" 
                    placeholder="MM / AA" 
                    maxlength="7" 
                    required
                    data-alert='<?php echo __('Expiration Date invalid','woocommerce-pagtur'); ?>'>
            </section>
            <section class="pagtur-form-row pagtur-form-row-last">
                <label for="pagtur_cvv"><?php echo $pagtur_cvv; ?>:<span class="required">*</span></label>
                <input type="text" 
                    name="pagtur_cvv" 
                    id="pagtur_cvv" 
                    value="" 
                    autocomplete="off" 
                    placeholder="CVV" 
                    maxlength="4" 
                    class="input-text wc-credit-card-form-card-cvc" 
                    required
                    data-alert='<?php echo __('CVV invalid','woocommerce-pagtur'); ?>'>
            </section>
            <section class="pagtur-form-row">
            <?php include WC_PAGTUR_VIEWS_DIR . 'wc_pagtur_installments.php'; ?>
            </section>
            
            <section class="pagtur-form-row">
                
            </section>
        </section>
    </div>
</div>
<?php
    echo "<script type='text/javascript' src='". plugins_url( 'assets/js/wc_pagtur.js', plugin_dir_path( __DIR__ ) ) ."'></script>";

}
?>
