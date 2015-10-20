	jQuery(document).ready(function($) {   
$('.toplevel_page_woocommerce').last()[0].href=$('.toplevel_page_woocommerce').last()[0].href.replace('admin.php?page=woocommerce','edit.php?post_type=shop_order');
	$('.ozhmenu_toplevel').hover(function(){
		var ul=$(this).find('ul');
		if (ul.length>0) ul.addClass('visible-important');
	},function(){
		var ul=$(this).find('ul');
		if (ul.length>0) ul.removeClass('visible-important');
	})
$('.infotip1').each(function(){
var str=$.parseHTML($(this).attr('data-tip'));
$(this).tipTip({keepAlive: true,content:str});	
})

})