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

<h2><?php esc_html_e( 'Importar Productos desde Hoko', 'wc-hoko' ); ?></h2>


<div class="woocommerce-BlankState-buttons">
	<a class="woocommerce-BlankState-cta button-primary button" href="<?php echo admin_url('admin.php?page=hoko_menu&hoko_action=settings' ); ?>">
		Configuraci&oacute;n
	</a>
	<a class="woocommerce-BlankState-cta button-primary button" href="<?php echo admin_url('admin.php?page=hoko_menu&hoko_action=products_list' ); ?>">
		Productos Hoko
	</a>
</div>


<?php if (!isset($_GET['hoko_action']) || $_GET['hoko_action'] === 'settings') { ?>
<h3><?php esc_html_e( 'Settings', 'wc-hoko' ); ?></h3>
<hr />
<form action="options.php" method="post">
	<?php settings_fields( 'plugin_hoko_options' ); ?>
	<?php do_settings_sections( 'plugin2' ); ?>
</form>
<?php } ?>

<?php if ($_GET['hoko_action'] === 'products_list') { ?>
	<?php do_settings_sections( 'plugin_hoko' ); ?>
<?php } ?>