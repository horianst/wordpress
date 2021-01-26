jQuery(function () {
    function do_replace(section) {

        var element = jQuery('[name="'+section+'_city"]');
        var type = element.prop('nodeName');

        var attr_class = element.attr('class');
        var attr_name = element.attr('name');
        var attr_id = element.attr('id');
        var attr_placeholder = element.attr('placeholder');
        var attr_autocomplete = element.attr('autocomplete');
        var value = element.val();

        if (element != null) {
            if (jQuery('[name="'+section+'_country"]').val() == 'RO' && jQuery('[name="'+section+'_state"]').val()) {
                jQuery.post('/?urgentcargus&judet=' + jQuery('[name="'+section+'_state"]').val() + '&val=' + value, function (data) {
                    element.replaceWith('<select class="' + attr_class + '" name="' + attr_name + '"  id="' + attr_id + '">' + data + '</select>');
                });
            } else {
                if (type != 'INPUT') {
                    element.replaceWith('<input type="text" class="' + attr_class + '" name="' + attr_name + '"  id="' + attr_id + '" placeholder="' + attr_placeholder + '" autocomplete="' + attr_autocomplete + '" value="" />');
                }
            }
        }
    }

    jQuery(document).on('change', '[name="billing_state"]', function () {
        do_replace('billing');
    });

    jQuery(document).on('change', '[name="shipping_state"]', function () {
        do_replace('shipping');
    });

    do_replace('billing');
    do_replace('shipping');

    jQuery(document).on('change', '[name="payment_method"]', function () {
        forceShippingRecalculation();
    });

    jQuery(document).on('change', 'select[name="billing_city"]', function () {
        forceShippingRecalculation();
    });

    function forceShippingRecalculation() {
        // fac asta pentru ca daca nu modific un string din adresa, nu se recalculeaza transpprtul
        if (jQuery('#ship-to-different-address-checkbox:checked').length > 0) {
            var shipping_address = jQuery('#shipping_address_1').val();
            var shipping_last = shipping_address.substr(shipping_address.length - 1);
            if (shipping_last == ' ') {
                jQuery('#shipping_address_1').val(shipping_address.trim());
            } else {
                jQuery('#shipping_address_1').val(shipping_address + ' ');
            }
        } else {
            var billing_address = jQuery('#billing_address_1').val();
            var billing_last = billing_address.substr(billing_address.length - 1);
            if (billing_last == ' ') {
                jQuery('#billing_address_1').val(billing_address.trim());
            } else {
                jQuery('#billing_address_1').val(billing_address + ' ');
            }
        }

        // trigger functia care face refresh checkout-ului
        jQuery('body').trigger('update_checkout');
    }
});