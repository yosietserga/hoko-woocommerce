<?php

/**
 * Admin View: Product import form
 *
 * @since 1.0.0
 *
 * @package HOKO
 * @subpackage HOKO/views
 */

defined('ABSPATH') || exit;
?>


<?php if ($_GET['hoko_action'] == 'products_list') { ?>
	<h3><?php esc_html_e('Hoko Products', 'wc-hoko'); ?></h3>
	<hr />


	<div class="row">
	<?php foreach ($products as $v) { ?>
		<div class="cards">
			<div class="card-close-button">X</div>
			<div class="card-content row" 
				data-row_id="<?php echo $v->id; ?>" 
				data-product_id="<?php echo $v->id; ?>" 
        data-code="<?php echo $v->code; ?>" 
				data-name="<?php echo $v->name; ?>" 
				data-description="<?php echo filter_var($v->description, FILTER_SANITIZE_STRING); ?>" 
				data-reference="<?php echo $v->reference; ?>" 
				data-kind="<?php echo $v->kind; ?>" 
				data-created_at="<?php echo $v->created_at; ?>" 
				data-updated_at="<?php echo $v->updated_at; ?>" 
				data-deleted_at="<?php echo $v->deleted_at; ?>" 
				data-min_sale_price="<?php echo $v->min_sale_price; ?>" 
				data-price_by_unit="<?php echo $v->price_by_unit; ?>" 
				data-price_by_amount="<?php echo $v->price_by_amount; ?>" 
				data-price_dropshipping="<?php echo $v->price_dropshipping; ?>" 
				data-tax="<?php echo $v->tax; ?>" 
				data-periodicity="<?php echo $v->periodicity; ?>" 
				data-allowCombo="<?php echo $v->allowCombo; ?>" 
				data-cost="<?php echo $v->cost; ?>" 
				data-store_id="<?php echo $v->store_id; ?>" 
				data-minimal_price="<?php echo $v->minimal_price; ?>" 
				data-video="<?php echo $v->video; ?>" 
				data-warranty="<?php echo $v->warranty; ?>" 
				data-url_qr="<?php echo $v->url_qr; ?>" 
				data-url_code_bar="<?php echo $v->url_code_bar; ?>" 
			<?php if (!empty($v->measures)) {
				foreach ($v->measures as $mk => $mv) {
					$mparts = explode(" ", $mk);
			?> data-<?php echo strtolower($mparts[0]); ?>_value="<?php echo (float)$mv; ?>" 
			data-<?php echo strtolower($mparts[0]); ?>_unit="<?php echo str_replace(array('(', ')'), "", $mparts[1]); ?>" 
			<?php } } ?>
			<?php foreach ($v->images as $kimg => $img) { ?> data-image_<?php echo $kimg; ?>="<?php echo $img; ?>" <?php } ?>>
				<div class="col-xs-6">
					<div class="container-not-expanded">
						<!--<img src="<?php echo $v->images[0]; ?>" alt="photo" width="100%" />-->
						<img src="<?php echo str_replace("https://hoko.com.cohttps:", "/", $v->images[0]); ?>" alt="photo" width="100%" />
					</div>

					<div class="container-expanded owl-carousel">
						<?php foreach ($v->images as $kimg => $img) { ?>
						<a href="#" data-fancybox="gallery_<?php echo $v->code.$v->id; ?>" data-src="<?php echo str_replace("https://hoko.com.cohttps:", "/", $img); ?>">
							<img id="img_<?php echo  $v->id ."_". $kimg; ?>" src="<?php echo str_replace("https://hoko.com.cohttps:", "/", $img); ?>" />
							<!--<img id="img_<?php echo  $v->id ."_". $kimg; ?>" altsrc="<?php echo $img; ?>" />-->
						</a>
						<?php } ?>

						<?php if ($v->video) { ?>
							<video />
						<?php } ?>
					</div>
				</div>

				<div class="col-xs-6">
					<div class="container-not-expanded">
						<h3><?php echo strtoupper($v->name); ?></h3>
						<small><?php echo $v->code; ?></small>
						<?php echo $v->price_by_unit; ?>
						<br />
						<div class="actions">
							<button class="hoko-button hoko-red" onclick="addProduct('<?php echo $v->id; ?>');">Agregar Producto</button>
							<button class="hoko-button hoko-blue" onclick="checkUpdates('<?php echo $v->id; ?>');">Verificar Actualizaciones</button>
							<button class="hoko-button hoko-gray" onclick="removeProduct('<?php echo $v->id; ?>');">Eliminar Producto</button>
						</div>
					</div>

					<div class="container-expanded">
						<h1 class="name"><?php echo $v->name; ?></h1>
						<small class="code"><?php echo $v->code; ?></small>
						
						<div class="actions">
							<button class="hoko-button hoko-red" onclick="addProduct('<?php echo $v->id; ?>');">Agregar Producto</button>
							<button class="hoko-button hoko-blue" onclick="checkUpdates('<?php echo $v->id; ?>');">Verificar Actualizaciones</button>
							<button class="hoko-button hoko-gray" onclick="removeProduct('<?php echo $v->id; ?>');">Verificar Actualizaciones</button>
						</div>

						<hr />
						
						<h3>Detalles</h3>

						<table class="product_edit_table_hoko">

						<?php
						$fields = [
							'price_by_unit'=>'Precio',
							'min_sale_price'=>'Precio Min. Venta',
							'minimal_price'=>'Precio Min.',
							'price_by_amount'=>'Precio por Cantidad',
							'price_dropshipping'=>'Precio Dropshipping',
							'cost'=>'Costo',
							'tax'=>'Impuesto',
							'periodicity'=>'Frecuencia',
							'store_id'=>'Tienda ID',
							'reference'=>'Referencia',
							'kind'=>'Tipo',
							'reference'=>'Referencia',
							'created_at'=>'Fecha Creado',
							'updated_at'=>'Fecha Actualizado',
							'deleted_at'=>'Fecha Eliminado',
							'url_qr'=>'QR Url',
							'url_code_bar'=>'Code Url',
						];
						foreach($fields as $k=>$label) {
							if ($v->{$k}) { ?>
							<tr>
								<td class="<?php echo $k; ?>"><?php echo $label; ?>:</td>
								<td><?php echo $v->{$k}; ?></td>
							</tr>
						<?php	} } ?>
						</table>

						<hr />

						<h3>Descripci&oacute;n</h3>
						<div class="description">
							<?php echo filter_var($v->description, FILTER_SANITIZE_STRING); ?>
						</div>


						<?php if ($v->warranty) { ?>
						<div class="warranty">
							<?php echo filter_var($v->warranty, FILTER_SANITIZE_STRING); ?>
						</div>
						<?php } ?>
						

						<?php if ($v->allowCombo) { ?>
						<div class="allowCombo">
							
						</div>
						<?php } ?>
						
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
	</div>



	<div class="pagination">
	<?php 
    $link_style = 'text-decoration:none;background:#eee;border:solid 1px #ddd; padding:5px; margin:15px 2px; font-weight:bold;';
    $pagination_base_rul ="http://localhost/projects/wordpress-hoko/wp-admin/admin.php?page=hoko_menu&hoko_action=products_list&spage=";

    array_pop($data->links);
    foreach ($data->links as $p=>$v) { 
      if ($p < 1) continue;

      if ($page != $p || !is_numeric((int)$v->label) || !$v->active) {
    ?>

		  <a style="<?php echo $link_style; ?>" href="<?php echo $pagination_base_rul . (int)$v->label; ?>"><?php echo $v->label; ?></a>
		
    <?php
      } else { 
        echo $v->label; 
      }
    } ?>
	</div>
<?php } ?>