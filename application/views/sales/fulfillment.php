<?php $this->load->view("partial/header"); ?>
<?php
	$this->load->helper('sale');
	$is_integrated_credit_sale = is_sale_integrated_cc_processing();
	$is_sale_integrated_ebt_sale = is_sale_integrated_ebt_sale();
	$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
	$website = ($website = $this->Location->get_info_for_key('website', isset($override_location_id) ? $override_location_id : FALSE)) ? $website : $this->config->item('website');
	$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');
?>

<div class="manage_buttons hidden-print">
	<div class="row">
		<?php if(rawurldecode($sale_id_raw) != lang('sales_test_mode_transaction')) { ?>
		<div class="col-md-6">
			<div class="hidden-print search no-left-border">
				<ul class="list-inline print-buttons">
					<li></li>
					<li>
						<button class="btn btn-primary btn-lg hidden-print" id="fufillment_sheet_button" onclick="window.open('<?php echo site_url("sales/receipt/$sale_id_raw"); ?>', 'blank');" > <?php echo lang('sales_receipt'); ?></button>
					</li>
				</ul>
			</div>
		</div>
		<?php } ?>
		<div class="col-md-<?php echo rawurldecode($sale_id_raw) != lang('sales_test_mode_transaction') ? 6 : 12;?>">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<ul class="list-inline">
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="print_button" onclick="print_fulfillment()" > <?php echo lang('common_print'); ?> </button>		
						</li>
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="new_sale_button_1" onclick="window.location='<?php echo site_url('sales'); ?>'" > <?php echo lang('sales_new_sale'); ?> </button>	
						</li>
					</ul>
				</div>
			</div>				
		</div>
	</div>
</div>
<div class="row manage-table receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	<div class="col-md-12" id="receipt_wrapper_inner">
		<div class="panel panel-piluku">
			<div class="panel-body panel-pad">
			    <div class="row">
			        <!-- from address-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-address">
			                <?php if($company_logo) {?>
			                	<li class="invoice-logo">
									<?php echo img(array('src' => $this->Appfile->get_url_for_file($company_logo))); ?>
			                	</li>
			                <?php } ?>
			                <li class="company-title"><?php echo $company; ?></li>
			                <li><?php echo $this->Location->get_info_for_key('address', isset($override_location_id) ? $override_location_id : FALSE); ?></li>
			                <li><?php echo $this->Location->get_info_for_key('phone', isset($override_location_id) ? $override_location_id : FALSE); ?></li>
			                <?php if($website) { ?>
								<li><?php echo $website; ?></li>
							<?php } ?>
							<li class="title">
								<span class="pull-left"> <?php echo lang('sales_fulfillment_sheet'); ?></span>
								<span class="pull-right"><?php echo $transaction_time ?></span>
							</li>
			            </ul>
			        </div>
			        <!--  sales-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-detail">
							<li class="big-screen-title">
								 <?php echo lang('sales_fulfillment_sheet'); ?>
								 <br>
								 <strong><?php echo $transaction_time ?></strong>
							</li>
				      <li><span><?php echo lang('common_sale_id').":"; ?></span><?php echo rawurldecode($sale_id); ?></li>
							
							<?php if (isset($sale_type)) { ?>
								<li><?php echo $sale_type; ?></li>
							<?php } ?>
							
							<li><span><?php echo lang('common_employee').":"; ?></span><?php echo $employee; ?></li>
							<?php 
							if($this->Location->get_info_for_key('enable_credit_card_processing',isset($override_location_id) ? $override_location_id : FALSE))
							{
								echo '<li id="merchant_id"><span>'.lang('common_merchant_id').'</span>: '.$this->Location->get_merchant_id(isset($override_location_id) ? $override_location_id : FALSE).'</li>';
							}
							?>
			            </ul>
			        </div>
							
			        <!-- to address-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			          <?php if(isset($customer)) { ?>
				        <ul class="list-unstyled invoice-address invoiceto">
									<li class="invoice-to"><?php echo lang('sales_invoice_to');?>:</li>
									<li><?php echo lang('common_customer').": ".$customer; ?></li>
									<?php if(!empty($customer_company)) { ?><li><?php echo lang('common_company').": ".$customer_company; ?></li><?php } ?>
									
									<?php if (!$this->config->item('remove_customer_contact_info_from_receipt')) { ?>
										<?php if(!empty($customer_address_1)){ ?><li><?php echo lang('common_address'); ?> : <?php echo $customer_address_1. ' '.$customer_address_2; ?></li><?php } ?>
										<?php if (!empty($customer_city)) { echo '<li>'.$customer_city.' '.$customer_state.', '.$customer_zip.'</li>';} ?>
										<?php if(!empty($customer_phone)){ ?><li><?php echo lang('common_phone_number'); ?> : <?php echo $customer_phone; ?></li><?php } ?>
										<?php if(!empty($customer_email)){ ?><li><?php echo lang('common_email'); ?> : <?php echo $customer_email; ?></li><?php } ?>
									<?php } ?>
				        </ul>
								
						<?php } ?>
			        </div>
							
			        <!-- delivery address-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
						
			          <?php if(isset($delivery_person_info)) { ?>
				        <ul class="list-unstyled invoice-address" style="margin-bottom:2px;">
								
								
									<li class="invoice-to"><?php echo lang('deliveries_shipping_address');?>:</li>
									<li><?php echo lang('common_name').": ".$delivery_person_info['first_name'].' '.$delivery_person_info['last_name']; ?></li>
									
									<?php if(!empty($delivery_person_info['address_1']) || !empty($delivery_person_info['address_2'])){ ?><li><?php echo lang('common_address'); ?> : <?php echo $delivery_person_info['address_1']. ' '.$delivery_person_info['address_2']; ?></li><?php } ?>
									<?php if (!empty($delivery_person_info['city'])) { echo '<li>'.$delivery_person_info['city'].' '.$delivery_person_info['state'].', '.$delivery_person_info['zip'].'</li>';} ?>
									<?php if(!empty($delivery_person_info['phone'])){ ?><li><?php echo lang('common_phone_number'); ?> : <?php echo $delivery_person_info['phone']; ?></li><?php } ?>
									<?php if(!empty($delivery_person_info['email'])){ ?><li><?php echo lang('common_email'); ?> : <?php echo $delivery_person_info['email']; ?></li><?php } ?>
				        </ul>
								<?php } ?>
			        </div>
							
			    </div>
			    <!-- invoice heading-->
				<?php 
					$x_col = 6;
					$xs_col = 2;
					if($discount_exists)
					{
						$x_col = 4;
						$xs_col = 2;
					}
				?>
				<div class="invoice-table">
			        <div class="row">
			            <div class="col-md-<?php echo $x_col; ?> col-sm-<?php echo $x_col; ?> col-xs-<?php echo $x_col; ?>">
			                <div class="invoice-head item-name"><?php echo lang('common_item_name'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
			                <div class="invoice-head"><?php echo lang('common_price'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
			                <div class="invoice-head"><?php echo lang('common_quantity'); ?></div>
			            </div>
						<?php if($discount_exists) { ?>
				            <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
				                <div class="invoice-head"><?php echo lang('common_discount_percent'); ?></div>
				            </div>
			            <?php } ?>
			            <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
			                <div class="invoice-head pull-right"><?php echo lang('common_total'); ?></div>
			            </div>
			        </div>
			    </div>				
			    <!-- Items -->
			    <?php if (count($sales_items) > 0) { ?>
					<div class="row">
			        	<div class="col-md-12 item-kits-heading">
			        		<?php echo lang('module_items'). ' ('.lang('common_without_tax').')'; ?>
			        	</div>

					</div>
				
			    <!-- Items table -->
			    <?php
				}
	    		$current_category = FALSE;

				foreach($sales_items as $item)
				{
					
				?>
			    <!-- invoice items-->
			    <div class="invoice-table-content">
			        <div class="row">
			        
					
					<div class="col-md-<?php echo $x_col; ?> col-sm-<?php echo $x_col; ?> col-xs-<?php echo $x_col; ?>">
					    <div class="invoice-content invoice-con">
					        <div class="invoice-content-heading"><?php echo $item['name']; ?><?php if ($item['size']){ ?> (<?php echo $item['size']; ?>)<?php } ?></div>
									<?php if (!$this->config->item('hide_desc_on_receipt') && isset($item['description']) && !$item['description']=="" ) {?>
		                    	<div class="invoice-desc"><?php echo $item['description']; ?></div>
		                    <?php } ?>
							
		                    <?php if(isset($item['serialnumber']) && $item['serialnumber'] !=""){ ?>
		                    	<div class="invoice-desc"><?php echo $item['serialnumber']; ?></div>
		                    <?php } ?>
							
        
					    </div>
					</div>					
					
				    <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
						<div class="invoice-content"><?php echo to_currency($item['item_unit_price']); ?></div>
		            </div>
					
		            <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
		                <div class="invoice-content"><?php echo to_quantity($item['quantity_purchased']); ?></div>
		            </div>
					
					
					<?php if($discount_exists) { ?>
					<div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
					    <div class="invoice-content"><?php echo to_quantity($item['discount_percent']); ?></div>
					</div>
					<?php } ?>
					
					
					<div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
		            
		                <div class="invoice-content pull-right"><?php echo to_currency($item['item_unit_price']*$item['quantity_purchased']-$item['item_unit_price']*$item['quantity_purchased']*$item['discount_percent']/100); ?></div>
			        </div>				
					</div>
			    </div>
								
			    <?php } ?>

			    <!-- Item Kits -->
			    <?php if (count($sales_item_kits) > 0) { ?>
					<div class="row">
			        	<div class="col-md-12 item-kits-heading">
			        		<?php echo lang('module_item_kits'). ' ('.lang('common_without_tax').')'; ?>
			        	</div>

					</div>
			    <?php
	    		$current_category = FALSE;

				foreach($sales_item_kits as $item)
				{
					
				?>
				
			    <!-- invoice items-->
			    <div class="invoice-table-content">
			        <div class="row">
			      
					
					<div class="col-md-<?php echo $x_col; ?> col-sm-<?php echo $x_col; ?> col-xs-<?php echo $x_col; ?>">
					    <div class="invoice-content invoice-con">
					        <div class="invoice-content-heading"><?php echo $item['name']; ?></div>
		                    <?php if(isset($item['description']) && $item['description'] !=""){ ?>
		                    	<div class="invoice-desc"><?php echo $item['description']; ?></div>
		                    <?php } ?>
							
		                    <?php if(isset($item['serialnumber']) && $item['serialnumber'] !=""){ ?>
		                    	<div class="invoice-desc"><?php echo $item['serialnumber']; ?></div>
		                    <?php } ?>
							
        
					    </div>
					</div>					
					
				    <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
						<div class="invoice-content"><?php echo to_currency($item['item_kit_unit_price']); ?></div>
		            </div>
					
		            <div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
		                <div class="invoice-content"><?php echo to_quantity($item['quantity_purchased']); ?></div>
		            </div>
					
					
					<?php if($discount_exists) { ?>
					<div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
					    <div class="invoice-content"><?php echo to_quantity($item['discount_percent']); ?></div>
					</div>
					<?php } ?>
					
					
					<div class="col-md-2 col-sm-2 col-xs-<?php echo $xs_col; ?>">
		            
		                <div class="invoice-content pull-right"><?php echo to_currency($item['item_kit_unit_price']*$item['quantity_purchased']-$item['item_kit_unit_price']*$item['quantity_purchased']*$item['discount_percent']/100); ?></div>
			        </div>				
					</div>
			    </div>
			    <?php }
			}
			?>


				<?php
				$number_of_items_sold = 0;
				$number_of_items_returned = 0;
				foreach(array_reverse($cart, true) as $line=>$item)
				{
					if ($item['quantity'] > 0 && $item['name'] != lang('common_store_account_payment') && $item['name'] != lang('common_discount'))
					{
						$number_of_items_sold = $number_of_items_sold + $item['quantity'];
					}
					elseif ($item['quantity'] < 0 && $item['name'] != lang('common_store_account_payment') && $item['name'] != lang('common_discount'))
					{
						$number_of_items_returned = $number_of_items_returned + abs($item['quantity']);
					}
				}
				?>
			    <div class="invoice-footer gift_receipt_element">
						<?php if ($exchange_name) { ?>
						
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
					                <div class="invoice-footer-heading"><?php echo lang('common_exchange_to').' '.$exchange_name; ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
					                <div class="invoice-footer-value">x <?php echo to_currency_no_money($exchange_rate); ?></div>
					            </div>
					        </div>
											
						<?php } ?>
						
			        <div class="row">
			            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
			                <div class="invoice-footer-heading"><?php echo lang('common_sub_total'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-value">
			                	
												<?php if (isset($exchange_name) && $exchange_name) { 
													echo to_currency_as_exchange($subtotal);
												?>
												<?php } else {  ?>
												<?php echo to_currency($subtotal); ?>				
												<?php
												}
												?>
			                </div>
			            </div>
			        </div>
			        <?php if ($this->config->item('group_all_taxes_on_receipt')) { ?>
						<?php 
						$total_tax = 0;
						foreach($taxes as $name=>$value) 
						{
							$total_tax+=$value;
					 	}
						?>	
						<div class="row">
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_tax'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value">
								
												<?php if (isset($exchange_name) && $exchange_name) { 
													echo to_currency_as_exchange($total_tax*$exchange_rate);					
												?>
												<?php } else {  ?>
												<?php echo to_currency($total_tax*$exchange_rate); ?>				
												<?php
												}
												?>
												
												</div>
				            </div>
				        </div>
						
					<?php }else {?>
						<?php foreach($taxes as $name=>$value) { ?>
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
					                <div class="invoice-footer-heading"><?php echo $name; ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
					                <div class="invoice-footer-value">
													
													
													<?php if (isset($exchange_name) && $exchange_name) { 
														echo to_currency_as_exchange($value*$exchange_rate);					
													?>
													<?php } else {  ?>
													<?php echo to_currency($value); ?>				
													<?php
													}
													?>
													
													
													</div>
					            </div>
					        </div>
						<?php }; ?>
					<?php } ?>
			        <div class="row">
			            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
			                <div class="invoice-footer-heading"><?php echo lang('common_total'); ?></div>
			            </div>
			            <div class="col-md-2 col-sm-2 col-xs-4">
			                <div class="invoice-footer-value invoice-total">
																							
											
											<?php if (isset($exchange_name) && $exchange_name) { 
												?>
												<?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ?  to_currency_as_exchange(round_to_nearest_05($total)) : to_currency_as_exchange($total); ?>				
											<?php } else {  ?>
											<?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($total)) : to_currency($total); ?>				
											<?php
											}
											?>
											
											</div>
			            </div>
			        </div> 
					
			        <div class="row">
						<?php if ($number_of_items_sold) { ?>
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_items_sold'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_quantity($number_of_items_sold); ?></div>
				            </div>
						<?php } ?>
						
						<?php if ($number_of_items_returned) { ?>
							
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_items_returned'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_quantity($number_of_items_returned); ?></div>
				            </div>
						<?php } ?>
						
			        </div> 
					
			        <?php
						foreach($payments as $payment_id=>$payment)
						{ 
					?>
						<div class="row">
				            <div class="col-md-offset-4 col-sm-offset-4 col-xs-offset-4 col-md-4 col-sm-4 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment['payment_date'])) : lang('common_payment'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				            	<?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment() || $is_sale_integrated_ebt_sale || sale_has_partial_ebt_payment()) && ($payment['payment_type'] == lang('common_credit') ||  $payment['payment_type'] == lang('sales_partial_credit') || $payment['payment_type'] == lang('common_ebt') || $payment['payment_type'] == lang('common_partial_ebt') ||  $payment['payment_type'] == lang('common_ebt_cash') ||  $payment['payment_type'] == lang('common_partial_ebt_cash'))) { ?>
									<div class="invoice-footer-value"><?php echo $is_sale_integrated_ebt_sale ? 'EBT ' : '';?><?php echo $payment['card_issuer']. ': '.$payment['truncated_card']; ?></div>
								<?php } else { ?>
									<div class="invoice-footer-value"><?php $splitpayment=explode(':',$payment['payment_type']); echo $splitpayment[0]; ?></div>																				
								<?php } ?>								
				            </div>
							
				            <div class="col-md-2 col-sm-2 col-xs-4">
								<div class="invoice-footer-value invoice-payment">
									
									
									
									<?php 
									
									if (isset($exchange_name) && $exchange_name) { 
										?>
										<?php echo $this->config->item('round_cash_on_sales') && $payment['payment_type'] == lang('common_cash') ?  to_currency_as_exchange(round_to_nearest_05($payment['payment_amount'])) : to_currency_as_exchange($payment['payment_amount']); ?>				
									<?php } else {  ?>
									<?php echo $this->config->item('round_cash_on_sales') && $payment['payment_type'] == lang('common_cash') ?  to_currency(round_to_nearest_05($payment['payment_amount'])) : to_currency($payment['payment_amount']); ?>				
									<?php
									}
									
									
									?>
								
								
								</div>
				            </div>
							
			            	<?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment() || $is_sale_integrated_ebt_sale || sale_has_partial_ebt_payment()) && ($payment['payment_type'] == lang('common_credit') ||  $payment['payment_type'] == lang('sales_partial_credit') || $payment['payment_type'] == lang('common_ebt') || $payment['payment_type'] == lang('common_partial_ebt') ||  $payment['payment_type'] == lang('common_ebt_cash') ||  $payment['payment_type'] == lang('common_partial_ebt_cash'))) { ?>
							
				           <div class="col-md-offset-6 col-sm-offset-6 col-xs-offset-3 col-md-6 col-sm-6 col-xs-9">
								<?php if ($payment['entry_method']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_entry_method'). ': '.$payment['entry_method']; ?></div>
								<?php } ?>

								<?php if ($payment['tran_type']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_transaction_type'). ': '.($is_sale_integrated_ebt_sale ? 'EBT ' : '').$payment['tran_type']; ?></div>
								<?php } ?>
							
								<?php if ($payment['application_label']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_application_label').': '.$payment['application_label']; ?></div>
								<?php } ?>
							
								<?php if ($payment['ref_no']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_ref_no'). ': '.$payment['ref_no']; ?></div>
								<?php } ?>
								<?php if ($payment['auth_code']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_auth_code'). ': '.$payment['auth_code']; ?></div>
								<?php } ?>
															
							
								<?php if ($payment['aid']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'AID: '.$payment['aid']; ?></div>
								<?php } ?>
							
								<?php if ($payment['tvr']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'TVR: '.$payment['tvr']; ?></div>
								<?php } ?>
							
							
								<?php if ($payment['tsi']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'TSI: '.$payment['tsi']; ?></div>
								<?php } ?>
							
							
								<?php if ($payment['arc']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'ARC: '.$payment['arc']; ?></div>
								<?php } ?>

								<?php if ($payment['cvm']) { ?>
								<div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'CVM: '.$payment['cvm']; ?></div>
								<?php } ?>
							</div>
							<?php } ?>							
							
						</div>
					<?php
						}
					?>

					<?php foreach($payments as $payment) {?>
						<?php if (strpos($payment['payment_type'], lang('common_giftcard'))!== FALSE) {?>
							<?php $giftcard_payment_row = explode(':', $payment['payment_type']); ?>
							
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-4 col-sm-4 col-xs-4">
					                <div class="invoice-footer-heading"><?php echo lang('sales_giftcard_balance'); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
										<div class="invoice-footer-value"><?php echo $payment['payment_type'];?></div>											
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
									<div class="invoice-footer-value invoice-payment"><?php echo to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row))); ?></div>
					            </div>
					        </div>
						<?php }?>
					<?php }?> 

					<?php if ($amount_change >= 0) {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('common_change_due'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total">
													
													<?php if (isset($exchange_name) && $exchange_name) { 
														?>
														<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency_as_exchange(round_to_nearest_05($amount_change)) : to_currency_as_exchange($amount_change); ?>				
													<?php } else {  ?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($amount_change)) : to_currency($amount_change); ?>				
													<?php
													}
													?>
													
												
												</div>
				            </div>
				        </div>
					<?php
					}
					else
					{
					?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('common_amount_due'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total">
													<?php if (isset($exchange_name) && $exchange_name) { 
														?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency_as_exchange(round_to_nearest_05($amount_change * -1)) : to_currency_as_exchange($amount_change * -1); ?>
													<?php } else {  ?>
													<?php echo $this->config->item('round_cash_on_sales')  && $is_sale_cash_payment ?  to_currency(round_to_nearest_05($amount_change * -1)) : to_currency($amount_change * -1); ?>
													<?php
													}
													?>
												
												</div>
				            </div>
				        </div>
					<?php
					} 
					?>  
					
					<?php if (isset($ebt_balance) && ($ebt_balance) !== FALSE) {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('sales_ebt_balance_amount'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_currency($ebt_balance); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>					
					
					<?php if (isset($customer_balance_for_sale) && $customer_balance_for_sale !== FALSE && !$this->config->item('hide_store_account_balance_on_receipt')) {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('sales_customer_account_balance'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_currency($customer_balance_for_sale); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>
					
					<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($sales_until_discount) && !$this->config->item('hide_sales_to_discount_on_receipt') && $this->config->item('loyalty_option') == 'simple') {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('common_sales_until_discount'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo $sales_until_discount <= 0 ? lang('sales_redeem_discount_for_next_sale') : to_quantity($sales_until_discount); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>
					

					<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($customer_points) && !$this->config->item('hide_points_on_receipt') && $this->config->item('loyalty_option') == 'advanced') {?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-2 col-md-2 col-sm-2 col-xs-6">
				                <div class="invoice-footer-heading"><?php echo lang('common_points'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo to_quantity($customer_points); ?></div>
				            </div>
				        </div>
					<?php
					}
					?>


					<?php
					if ($ref_no)
					{
					?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('sales_ref_no'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo $ref_no; ?></div>
				            </div>
				        </div>
					<?php
					}
					if (isset($auth_code) && $auth_code)
					{
					?>
						<div class="row">
				            <div class="col-md-offset-8 col-sm-offset-8 col-xs-offset-4 col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-heading"><?php echo lang('sales_auth_code'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"><?php echo $auth_code; ?></div>
				            </div>
				        </div>
					<?php
					}
					?>

					<div class="row">
			            <div class="col-md-12 col-sm-12 col-xs-12">
			                <div class="text-center">
			                	<?php if($show_comment_on_receipt==1)
									{
										echo $comment ;
									}
								?>
			                </div>
			            </div>
			        </div>
			    </div>

			   
			    <!-- invoice footer-->
			    <div class="row">
			        <div class="col-md-12 col-sm-12">
			            <div class="invoice-policy">
			                <?php echo nl2br($this->config->item('return_policy')); ?>
			            </div>
			            <?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')) {?>
										<div id='barcode' class="invoice-policy">
										<?php echo "<img src='".site_url('barcode')."?barcode=$sale_id&text=$sale_id' />"; ?>
									</div>
									<?php } ?>
			        </div>
			    </div>
			</div>
			<!--container-->
		</div>		
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>
<?php if ($this->config->item('print_after_sale') && $this->uri->segment(2) == 'fulfillment')
{
?>
<script type="text/javascript">
$(window).bind("load", function() {
	window.print();
});
</script>
<?php }  ?>

<script type="text/javascript">
function print_fulfillment()
 {
 	window.print();
 }
 </script>
