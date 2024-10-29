<div class="setting-content">

	<h3><?php _ex('Common settings', 'Setting section', 'ebdn'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="ebdn_ebay_per_page"><?php _ex('Products per page', 'Setting title', 'ebdn'); ?></label></th>
			<td class="forminp forminp-text">
				<input type="text" id="ebdn_ebay_per_page" name="ebdn_ebay_per_page" value="<?php echo esc_attr(get_option('ebdn_ebay_per_page', 20)); ?>"/>
				<span class="description"><?php printf(_x('the maximum number of items is %d', 'Setting desc', 'ebdn'), 100); ?></span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="titledesc"><label for="ebdn_ebay_extends_cats"><?php _ex('Use sub categories', 'Setting title', 'ebdn'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="ebdn_ebay_extends_cats" name="ebdn_ebay_extends_cats" value="yes" <?php if (get_option('ebdn_ebay_extends_cats', false)): ?>checked<?php endif; ?>/></td>
		</tr>
	</table>

	<h3><?php _ex('Affiliate setting', 'Setting section', 'ebdn'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="ebdn_ebay_custom_id"><?php _ex('CustomId', 'Setting title', 'ebdn'); ?></label></th>
			<td class="forminp forminp-text"><input type="text" id="ebdn_ebay_custom_id" name="ebdn_ebay_custom_id" value="<?php echo esc_attr(get_option('ebdn_ebay_custom_id')); ?>"/></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="ebdn_ebay_geo_targeting"><?php _ex('geoTargeting', 'Setting title', 'ebdn'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="ebdn_ebay_geo_targeting" name="ebdn_ebay_geo_targeting" value="yes" <?php if (get_option('ebdn_ebay_geo_targeting', false)): ?>checked<?php endif; ?>/></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="ebdn_ebay_network_id"><?php _ex('Network_id', 'Setting title', 'ebdn'); ?></label></th>
			<td class="forminp forminp-select">
				<?php $cur_ebdn_ebay_network_id = get_option('ebdn_ebay_network_id', '9'); ?>
				<select name="ebdn_ebay_network_id" id="ebdn_ebay_network_id">
					<option value="2" <?php if ($cur_ebdn_ebay_network_id == "2"): ?>selected="selected"<?php endif; ?>><?php _ex('Be Free', 'Setting option', 'ebdn'); ?></option>
					<option value="3" <?php if ($cur_ebdn_ebay_network_id == "3"): ?>selected="selected"<?php endif; ?>><?php _ex('Affilinet', 'Setting option', 'ebdn'); ?></option>
					<option value="4" <?php if ($cur_ebdn_ebay_network_id == "4"): ?>selected="selected"<?php endif; ?>><?php _ex('TradeDoubler', 'Setting option', 'ebdn'); ?></option>
					<option value="5" <?php if ($cur_ebdn_ebay_network_id == "5"): ?>selected="selected"<?php endif; ?>><?php _ex('Mediaplex', 'Setting option', 'ebdn'); ?></option>
					<option value="6" <?php if ($cur_ebdn_ebay_network_id == "6"): ?>selected="selected"<?php endif; ?>><?php _ex('DoubleClick', 'Setting option', 'ebdn'); ?></option>
					<option value="7" <?php if ($cur_ebdn_ebay_network_id == "7"): ?>selected="selected"<?php endif; ?>><?php _ex('Allyes', 'Setting option', 'ebdn'); ?></option>
					<option value="8" <?php if ($cur_ebdn_ebay_network_id == "8"): ?>selected="selected"<?php endif; ?>><?php _ex('BJMT', 'Setting option', 'ebdn'); ?></option>
					<option value="9" <?php if ($cur_ebdn_ebay_network_id == "9"): ?>selected="selected"<?php endif; ?>><?php _ex('eBay Partner Network', 'Setting option', 'ebdn'); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="ebdn_ebay_tracking_id"><?php _ex('TrackingId', 'Setting title', 'ebdn'); ?></label></th>
			<td class="forminp forminp-text"><input type="text" id="ebdn_ebay_tracking_id" name="ebdn_ebay_tracking_id" value="<?php echo esc_attr(get_option('ebdn_ebay_tracking_id')); ?>"/></td>
		</tr>
	</table>
	<!--
	<h3>Currency settings</h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="ebdn_ebay_using_woocommerce_currency">Using woocommerce currency</label></th>
			<td class="forminp forminp-text">
				<input type="checkbox" id="ebdn_ebay_using_woocommerce_currency" name="ebdn_ebay_using_woocommerce_currency" value="yes" <?php if (get_option('ebdn_ebay_using_woocommerce_currency', false)): ?>checked<?php endif; ?>/>
				<span class="description">try get price in woocommerce currency</span>
			</td>
		</tr>
	</table>
	-->
</div>