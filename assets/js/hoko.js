/**
 * Wordpress admin dashboard plugin integrations.
 *
 * @since 1.0.0
 * @package HOKO
 */

(function( $, woocommerce_hoko_admin_screen ) {
	$(function() {
		var $product_screen = $( '.edit-php.post-type-product' ),
		$title_action       = $product_screen.find( '.page-title-action:first' ),

		form =
		'<form method="post" style="display: inline"><a href="' +
		woocommerce_hoko_admin_screen.urls.import_products_from_hoko +
		'" class="page-title-action">' +
		woocommerce_hoko_admin_screen.strings.import_products_from_hoko +
		'</a></form>';

		$title_action.after( form );
	});

})( jQuery, woocommerce_hoko_admin_screen );

let _$ = jQuery;

_$(".btn-add").on("click", function(e) {
	e.preventDefault(); 
	e.stopImmediatePropagation();
});

function addProduct(id) {
	let elProduct = _$('[data-row_id="'+ id +'"]');
	if (elProduct) {
		let data = {
			hoko_product_id:elProduct.data("product_id"),
			code:elProduct.data("code"),
			title:elProduct.data("name"),
			description:elProduct.data("description"),
			reference:elProduct.data("reference"),
			kind:elProduct.data("kind"),

			created_at:elProduct.data("created_at"),
			updated_at:elProduct.data("updated_at"),
			deleted_at:elProduct.data("deleted_at"),

			min_sale_price:elProduct.data("min_sale_price"),
			price_by_unit:elProduct.data("price_by_unit"),
			price_by_amount:elProduct.data("price_by_amount"),
			price_dropshipping:elProduct.data("price_dropshipping"),
			minimal_price:elProduct.data("minimal_price"),
			tax:elProduct.data("tax"),
			cost:elProduct.data("cost"),

			periodicity:elProduct.data("periodicity"),
			allowCombo:elProduct.data("allowCombo"),

			store_id:elProduct.data("store_id"),
			video:elProduct.data("video"),
			warranty:elProduct.data("warranty"),
			url_qr:elProduct.data("url_qr"),
			url_code_bar:elProduct.data("url_code_bar"),
			
			height:elProduct.data("alto_value") +" "+ elProduct.data("alto_unit"),
			width:elProduct.data("ancho_value") +" "+ elProduct.data("ancho_unit"),
			large:elProduct.data("largo_value") +" "+ elProduct.data("largo_unit"),
			weight:elProduct.data("peso_value") +" "+ elProduct.data("peso_unit"),
		};
		
		data.images = [];
		[0,1,2,3,4,5,6].map(i => {
			console.log( 'image_'+i );
			let img = elProduct.data('image_'+i); 
			console.log( img );
			if (img) data.images.push( img );
		});
		
		console.log({data});
		wp.ajax.post( "hoko_insert_product", data
        ).done(function(response) {
		    alert(response);
	  	}).fail(function() {

	  	});
	}
}

function checkProduct(id) {

}

const makeDraggable = (object) => {
	var initX, initY, firstX, firstY;

	object.addEventListener('mousedown', function(e) {
	
		e.preventDefault();
		initX = this.offsetLeft;
		initY = this.offsetTop;
		firstX = e.pageX;
		firstY = e.pageY;
	
		this.addEventListener('mousemove', dragIt, false);
	
		window.addEventListener('mouseup', function() {
			object.removeEventListener('mousemove', dragIt, false);
		}, false);
	
	}, false);
	
	object.addEventListener('touchstart', function(e) {
	
		e.preventDefault();
		initX = this.offsetLeft;
		initY = this.offsetTop;
		var touch = e.touches;
		firstX = touch[0].pageX;
		firstY = touch[0].pageY;
	
		this.addEventListener('touchmove', swipeIt, false);
	
		window.addEventListener('touchend', function(e) {
			e.preventDefault();
			object.removeEventListener('touchmove', swipeIt, false);
		}, false);
	
	}, false);
}

function dragIt(e) {
	this.style.left = initX+e.pageX-firstX + 'px';
	this.style.top = initY+e.pageY-firstY + 'px';
}

function swipeIt(e) {
	var contact = e.touches;
	this.style.left = initX+contact[0].pageX-firstX + 'px';
	this.style.top = initY+contact[0].pageY-firstY + 'px';
}


const getDimensions = () => {
	let areaWrapper = _$("#wpcontent");
	return {
		left:_$("#adminmenuwrap").width(),
		top:_$("#wpadminbar").height(),
		width:areaWrapper.width() - 20,
		height:_$(window).height() - 10
	}
};

_$(".cards").each(function(){
	let that = this;
    _$(this).off("click").on("click", function(e){
        if (!_$(this).hasClass("expanded")) {
        	_$(this).addClass("expanded");
        	_$(this).removeClass("col-xs-6");
	        let size = getDimensions();
	        _$(this).css({
	        	position:"fixed",
	        }).animate({
	        	top:size.top +"px",
	        	left:size.left+"px",
	        },50).animate({
	        	width:size.width,
	        	height:size.height,
	        	flex:"100%",
	        });
	        
	        _$(that).find("img[altsrc]").each(function(e){
	        	_$(this).attr("src", _$(this).attr("altsrc"));
	        });
	        _$(".owl-carousel").owlCarousel({
			    items: 1
			});
			Fancybox.bind("[data-fancybox]", {
				
			});
	        
        	_$(this).find(".card-close-button").show();
        	_$(this).find(".card-close-button").on("click", function(e) {
        		e.preventDefault();
        		e.stopImmediatePropagation();
        		let anchor = _$(that).find(".card-content");
        		_$(this).hide();
	        	_$(that).removeClass("expanded");
	        	_$(that).addClass("col-xs-6");
		        _$(that).animate({
		        	width:"45%",
		        	height:anchor.height(),
		        	flex:"0 0 45%",
		        	position:"fixed",
		        	top:anchor.offsetTop +"px",
		        	left:anchor.offsetLeft +"px",
		        }, 150);
		        
		        setTimeout(function() {
			        _$(that).removeAttr('style');
		        }, 150);
        	});
        } else {
        	
        }
        
        //render and bind action buttons and events 
    });
});