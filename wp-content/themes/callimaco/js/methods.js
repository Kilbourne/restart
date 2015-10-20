
jQuery(document).ready(function($) {   

function adjustSelectize(){

$('.vp-selectize select').selectize({
    create: true,
	  dropdownParent: "body",
    sortField: 'text',
   createOnBlur:true,
   searchField: ['text']
    
});

$('.vp-field.vp-selectize').each(function(){
	var cbox=$(this).siblings().find('.vp-input.vp-cbox').val();
	var select=$(this).find('.vp-input.vp-selectize');
	if(cbox!==''){
	
	select[0].selectize.setValue(cbox);
	}
	select.change(function(){
	var cbox=$(this).closest('.vp-field.vp-selectize').siblings().find('.vp-input.vp-cbox');		
	var val=$(this).val();	
	cbox.attr('value',val);
	cbox.val(val);

})
});
}
adjustSelectize();
var target = document.getElementById("wpa_loop-[group1][0][autore]");
 
// create an observer instance
//
var observer = new MutationObserver(function(mutations) {
  if(mutations[0]['addedNodes']){
  	var newmeta=$(mutations[0]['addedNodes'][0]);
  	newmeta.find('.selectize-control').remove();
  	var select=newmeta.find('.vp-selectize select');  	
  	select.selectize({
    create: true,
	  dropdownParent: "body",
    sortField: 'text',
   createOnBlur:true,
   searchField: "item"
	});
	var fieldize=newmeta.find('.vp-field.vp-selectize');
	fieldize.each(function(){
	var cbox=$(this).siblings().find('.vp-input.vp-cbox').val();
	var select=$(this).find('.vp-input.vp-selectize');
	if(cbox!==''){
	
	select[0].selectize.setValue(cbox);
	}
	select.change(function(){
	var cbox=$(this).closest('.vp-field.vp-selectize').siblings().find('.vp-input.vp-cbox');		
	var val=$(this).val();	
	cbox.attr('value',val);
	cbox.val(val);

	})
	});
  }
});
 
// configuration of the observer:
var config = { attributes: true, childList: true, characterData: true };
 
// pass in the target node, as well as the observer options
if(target !== null)
observer.observe(target, config);
 


});
