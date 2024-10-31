<label for="pagtur_installments">
	<?php echo $pagtur_installments; ?>
	<span class="required">*</span>
</label>

<select 
	class="input-text"
	id="pagtur_installments"
	name="pagtur_installments"
	data-alert='<?php echo __('You need to select a installment plan','woocommerce-pagtur'); ?>'>
	<option value="0" data-amount="0"><?php echo __('Select your installment plan','woocommerce-pagtur'); ?></option>
<?php
	
	
	if ( get_woocommerce_currency() === "BRL"){
		$installments = WC_Pagtur_API::GetInstallments(WC()->cart->total,2,null);	
	}
	else {
		$pagturSettings = WC_PAGTUR_DB::GetSettingsDB();
		foreach ($pagturSettings as $item){
			$currencyCodeID = $item->currencyCodeID;
		}
		
		$currencyRate = WC_Pagtur_API::GetCurrencyRate($currencyCodeID);
		$installments = WC_Pagtur_API::GetInstallments(WC()->cart->total,2,$currencyRate->rate_token);
	}
	
	foreach ($installments as $item){
		echo sprintf(
			'<option value="%s" data-amount="%s">%s</option>',
			$item->installment,
			$item->amount,
			sprintf (
				'%s de R$ %s',
				$item->description,
				number_format($item->amount,2,',','.')
			)
		);
	}
	print_r($installments);
?>
    
</select>
<label for="pagtur_total_installment">
	<p>
		<?php echo $pagtur_total_amount; ?>: R$ <span id="pagtur_total_installment">0,00</span>
	</p>
</label>



<?php
		echo sprintf('<input type="hidden" name="pagtur_currency_id" value="%s">',$currencyCodeID);
		echo sprintf('<input type="hidden" name="pagtur_rate_token" value="%s">',empty($currencyRate->rate_token) ? '' : $currencyRate->rate_token );
		echo sprintf("<input type='hidden' name='pagtur_installments_json' value='%s'",json_encode($installments));
		
		
?>