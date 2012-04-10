<?php
/*
  Plugin Name: Robokassa Payment Gateway
  Plugin URI: http://loom-studio.net/
  Description: Allows you to use Robokassa payment gateway with the Jigoshop ecommerce plugin.
  Version: 0.9.1
  Author: Denis Alekseev
  Author URI: http://loom-studio.net/
 */


/*

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */


/* Add a custom payment class to Jigoshop
  ------------------------------------------------------------ */
add_action('plugins_loaded', 'jingoshop_robokassa', 0);
function jingoshop_robokassa()
{
	if (!class_exists('jigoshop_payment_gateway'))
		return; // if the Jigoshop payment gateway class is not available, do nothing

class robokassa extends jigoshop_payment_gateway {
		
	public function __construct() {
		$this->id = 'robokassa';
		$this->icon = '';
		$this->has_fields = false;
		$this->enabled = get_option('jigoshop_robokassa_enabled');
		$this->title = get_option('jigoshop_robokassa_title');
		$this->merchant = get_option('jigoshop_robokassa_merchant');
		$this->key1 = get_option('jigoshop_robokassa_key1');
		$this->key2 = get_option('jigoshop_robokassa_key2');
		$this->test = get_option('jigoshop_robokassa_test');
		
		add_action('init', array(&$this, 'check_callback') );
		add_action('valid-robokassa-callback', array(&$this, 'successful_request') );
		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
		add_action('receipt_robokassa', array(&$this, 'receipt_page'));
		
		add_option('jigoshop_robokassa_enabled', 'yes');
		add_option('jigoshop_robokassa_title', 'Sprypay');
		add_option('jigoshop_robokassa_merchant', '');
		add_option('jigoshop_robokassa_key1', '');
		add_option('jigoshop_robokassa_key2', ''); 
                add_option('jigoshop_robokassa_key2', 'yes');
		add_option('jigoshop_robokassa_title', __('Robokassa', 'jigoshop') );
	}
    
	/**
	* Admin Panel Options 
	* - Options for bits like 'title' and availability on a country-by-country basis
	**/
	public function admin_options() {
		?>
		<thead><tr><th scope="col" width="200px"><?php _e('Robokassa', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('Robocassa', 'jigoshop'); ?></th></tr></thead>
		<tr>
			<td class="titledesc"><?php _e('Enable robokassa', 'jigoshop') ?>:</td>
			<td class="forminp">
				<select name="jigoshop_robokassa_enabled" id="jigoshop_robokassa_enabled" style="min-width:100px;">
					<option value="yes" <?php if (get_option('jigoshop_robokassa_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
					<option value="no" <?php if (get_option('jigoshop_robokassa_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_robokassa_title" id="jigoshop_robokassa_title" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_robokassa_title')) echo $value; else echo 'robokassa'; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('Please enter your robokassa merchant login; this is needed in order to take payment!','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Robokassa Merchant login', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_robokassa_merchant" id="jigoshop_robokassa_merchant" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_robokassa_merchant')) echo $value; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('Please enter your robokassa key1!','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Robokassa key #1', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_robokassa_key1" id="jigoshop_robokassa_key1" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_robokassa_key1')) echo $value; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><a href="#" tip="<?php _e('Please enter your robokassa key2!','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Robokassa key #2', 'jigoshop') ?>:</td>
			<td class="forminp">
				<input class="input-text" type="text" name="jigoshop_robokassa_key2" id="jigoshop_robokassa_key2" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_robokassa_key2')) echo $value; ?>" />
			</td>
		</tr>
		<tr>
			<td class="titledesc"><?php _e('Testmode On', 'jigoshop') ?>:</td>
			<td class="forminp">
				<select name="jigoshop_robokassa_test" id="jigoshop_robokassa_test" style="min-width:100px;">
					<option value="yes" <?php if (get_option('jigoshop_robokassa_test') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
					<option value="no" <?php if (get_option('jigoshop_robokassa_test') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
				</select>
			</td>
		</tr>

		<?php
	}

	/**
	* There are no payment fields for sprypay, but we want to show the description if set.
	**/
	function payment_fields() {
		if ($jigoshop_robokassa_description = get_option('jigoshop_robokassa_title')) echo wpautop(wptexturize($jigoshop_robokassa_title));
	}

	/**
	* Admin Panel Options Processing
	* - Saves the options to the DB
	**/
	public function process_admin_options() {
		if(isset($_POST['jigoshop_robokassa_enabled'])) update_option('jigoshop_robokassa_enabled', jigowatt_clean($_POST['jigoshop_robokassa_enabled'])); else @delete_option('jigoshop_robokassa_enabled');
		if(isset($_POST['jigoshop_robokassa_title'])) update_option('jigoshop_robokassa_title', jigowatt_clean($_POST['jigoshop_robokassa_title'])); else @delete_option('jigoshop_robokassa_title');
		if(isset($_POST['jigoshop_robokassa_merchant'])) update_option('jigoshop_robokassa_merchant', jigowatt_clean($_POST['jigoshop_robokassa_merchant'])); else @delete_option('jigoshop_robokassa_merchant');
		if(isset($_POST['jigoshop_robokassa_key1'])) update_option('jigoshop_robokassa_key1', jigowatt_clean($_POST['jigoshop_robokassa_key1'])); else @delete_option('jigoshop_robokassa_key1');
		if(isset($_POST['jigoshop_robokassa_key2'])) update_option('jigoshop_robokassa_key2', jigowatt_clean($_POST['jigoshop_robokassa_key2'])); else @delete_option('jigoshop_robokassa_key2');
		if(isset($_POST['jigoshop_robokassa_test'])) update_option('jigoshop_robokassa_test', jigowatt_clean($_POST['jigoshop_robokassa_test'])); else @delete_option('jigoshop_robokassa_test');
	}
	/**
	* Generate the dibs button link
	**/
	public function generate_form( $order_id ) {
		
		$order = &new jigoshop_order( $order_id );
		
	//	echo site_url('/jigoshop/robokassacallback.php')."<br />";
        //        echo $order->get_cancel_order_url();die();

		$action_adr = 'https://merchant.roboxchange.com/Index.aspx';
		if($this->test=='yes')
			$action_adr = 'http://test.robokassa.ru/Index.aspx';
		// Dibs currency codes http://tech.dibs.dk/toolbox/currency_codes/
		
		$signature=md5($this->merchant.":".number_format($order->order_total, 2, '.', '').":".$order_id.":".$this->key1);
		$args =
			array(
				// Merchant
				'MrchLogin' => $this->merchant,
				
				// Session
				'Culture' => 'ru',
				
				// Order
				'OutSum' => number_format($order->order_total, 2, '.', ''),
				'InvId' => $order_id,
				'SignatureValue' => $signature
		);
		// Calculate key
		// http://tech.dibs.dk/dibs_api/other_features/md5-key_control/
		foreach ($args as $key => $value) {
			$fields .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}
		$ret.= '<p>'.__('Спасибо за Ваш заказ, пожалуйста, нажмите кнопку ниже, чтобы заплатить.', 'woocommerce').'</p>';
		$ret.= '<p><sub>'.__('Поддержка robokassa реализована <a href="http://loom-studio.net">loom-studio</a> и <a href="http://polzo.ru">akurganow</a>').'</sub></p>';

		return $ret.'<form action="'.$action_adr.'" method="post" id="dibs_payment_form">
				' . $fields . '
				<input type="submit" class="button-alt" id="submit_dibs_payment_form" value="'.__('Pay via DIBS', 'jigoshop').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'jigoshop').'</a>
				<script type="text/javascript">
					jQuery(function(){
						jQuery("body").block(
							{ 
								message: "<img src=\"'.jigoshop::plugin_url().'/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" />'.__('Thank you for your order. We are now redirecting you to DIBS to make payment.', 'jigoshop').'", 
								overlayCSS: 
								{ 
									background: "#fff", 
									opacity: 0.6 
								},
								css: { 
							        padding:        20, 
							        textAlign:      "center", 
							        color:          "#555", 
							        border:         "3px solid #aaa", 
							        backgroundColor:"#fff", 
							        cursor:         "wait" 
							    } 
							});
					});
				</script>
			</form>';
		
	}
	
	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {
		
		$order = &new jigoshop_order( $order_id );
		
		return array(
			'result' => 'success',
			'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('jigoshop_pay_page_id'))))
		);
		
	}
	
	/**
	* receipt_page
	**/
	function receipt_page( $order ) {
		
		echo '<p>'.__('Thank you for your order, please click the button below to pay with Robokassa.', 'jigoshop').'</p>';
		echo $this->generate_form( $order );
		
	}
	
	/**
	* Check for DIBS Response
	**/
	function check_callback() {
		if ( strpos($_SERVER["REQUEST_URI"], '/jigoshop/robokassacallback.php')!==false ) {
			
			error_log('Robocassa callback!');
			
			$_POST = stripslashes_deep($_POST);
			
			do_action("valid-robokassa-callback", $_POST);
		}
		elseif(strpos($_SERVER["REQUEST_URI"], '/jigoshop/robokassathanks.php')!==false)
		{
/*		$f=fopen(dirname(realpath(__FILE__))."/log2.txt","a+");
		fputs($f,$_SERVER["REQUEST_URI"]."\r\n");
		fclose($f);*/

			$inv_id = $_REQUEST["InvId"];
			$order = &new jigoshop_order( $inv_id );
			$order->update_status('on-hold', __('Awaiting cheque payment', 'jigoshop'));
			jigoshop_cart::empty_cart();
			wp_redirect(add_query_arg('key', $order->order_key, add_query_arg('order', $inv_id, get_permalink(get_option('jigoshop_thanks_page_id')))));
			exit;
		}
		elseif(strpos($_SERVER["REQUEST_URI"], '/jigoshop/robokassacancel.php')!==false)
		{

			$inv_id = $_REQUEST["InvId"];
			$order = &new jigoshop_order( $inv_id );
			//$order->update_status('on-hold', __('Awaiting cheque payment', 'jigoshop'));
			jigoshop_cart::empty_cart();
			wp_redirect($order->get_cancel_order_url());
			exit;
		}

//echo add_query_arg('key', $order->order_key, add_query_arg('order', $inv_id, get_permalink(get_option('jigoshop_thanks_page_id'))));

	}

	/**
	* Successful Payment!
	**/
	function successful_request( $posted ) {
		$out_summ = $_REQUEST["OutSum"];
		$inv_id = $_REQUEST["InvId"];
		$shp_item = $_REQUEST["Shp_item"];
		$crc = $_REQUEST["SignatureValue"];
		$mcrc=strtoupper(md5("$out_summ:$inv_id:{$this->key2}" ));
		if($mcrc==$crc)
		{
			$order = &new jigoshop_order( $inv_id );
			$order->update_status('processing', __('Money is comming', 'jigoshop'));
			echo "OK".$posted['InvId'];
		}
exit;		
	}

}

/**
 * Add the gateway to JigoShop
 **/
function add_robokassa_gateway( $methods ) {
	$methods[] = 'robokassa'; return $methods;
}

add_filter('jigoshop_payment_gateways', 'add_robokassa_gateway' );
}