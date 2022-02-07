<?php
/**
 * Admin View: Product import form
 *
 * @since 1.0.0
 *
 * @package HOKO
 * @subpackage HOKO/views
 */

defined( 'ABSPATH' ) || exit;
?>

<table class="form-table woocommerce-importer-options">
	<tbody>
		<tr>
			<th scope="row">
				<label for="hoko_subscription_key">Hoko Subscription Key</label>
			</th>
			<td>
				<input type="text" id="hoko_subscription_key" required name="plugin_hoko_options[hoko_subscription_key]" value="<?php echo esc_html( $hoko_subscription_key ); ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="hoko_api_username">Hoko Usuario API</label>
			</th>
			<td>
				<input type="text" id="hoko_api_username" required name="plugin_hoko_options[hoko_api_username]" value="<?php echo esc_html( $hoko_api_username ); ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="hoko_api_password">Hoko Password API</label>
			</th>
			<td>
				<input type="password" id="hoko_api_password" required name="plugin_hoko_options[hoko_api_password]" value="<?php echo esc_html( $hoko_api_password ); ?>" />
			</td>
		</tr>
		<?php if ($hoko_connected) { ?>
		<tr>
			<td colspan="2">
				<h3 style="background:#7de67d;border:solid 1px green;color:#fff;margin:10px;padding:10px 20px;">Hoko Conectado!</h3>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

	<input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>" />

