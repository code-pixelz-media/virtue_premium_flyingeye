jQuery(document).ready(function () {
    jQuery(document).on('click','.inventory_number_product_list',function(){
        jQuery(this).parent().find('.admin-tooltip').css('display','flex');
    });
  jQuery(".inven-submit-btn").on("click", function () {
    var inventory_number = jQuery(this).parent().parent().find('.inv_number').val();
    var product_id = jQuery(this).parent().parent().find('.inv_number').attr('data-product_id');
    jQuery.ajax({
      type: "post",
      url: admin_ajax.ajaxurl,
      data: { 
        action: "update_inventory_number", 
        inventory_number: inventory_number,
        product_id: product_id,
    },
      success: function (response) {
        // console.log('response',response);
        if (response.success) {
            jQuery('#inventory_number_product_list_'+response.data.product).text(response.data.number)
          jQuery('#admin-tooltip_'+response.data.product).css('display','none');
        }
      },
    });
  });

  jQuery('.inven-cancel-btn').on('click',function(){
    jQuery(this).parent().parent().css('display','none');
  });
});
