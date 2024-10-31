

<style>

</style>

<div id="pagtur_details" class="pagtur-typ-container pagtur-form-row">
    <span class="pagtur-typ-title"><?php echo __('PagTur Transaction Details','woocommerce-pagtur'); ?></span>    
    <br><br>
    
    <label for="pagtur_total_amount"><?php echo __('Total Amount','woocommerce-pagtur'); ?>: R$ <?php echo number_format($pagtur_total_amount,2,',','.'); ?></label>
    <label for="pagtur_installments"><?php echo __('Installments','woocommerce-pagtur'); ?>: <?php echo $pagtur_installments; ?></label>
    <label for="pagtur_auth_code"><?php echo __('Auth Code','woocommerce-pagtur'); ?>: <?php echo $pagtur_auth_code; ?></label>
</div>
<div id="pagtur_receipt" class="pagtur-typ-container">
<center>
    <?php echo $pagtur_receipt; ?>
    <br>
    
</center>
</div>
<div>
    <center>
        <input type="button" id="prnBtn" class="pagtur-typ-btn" value="<?php echo __('PRINT','woocommerce-pagtur'); ?>" onclick="javascript:pagtur_print('pagtur_receipt');" />
    </center>
</div>

