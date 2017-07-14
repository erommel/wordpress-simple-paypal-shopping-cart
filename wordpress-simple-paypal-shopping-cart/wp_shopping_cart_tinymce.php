<?php

function wp_cart_add_tinymce_button() {

    // Don't bother doing this stuff if the current user lacks permissions
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }

    // Add only in Rich Editor mode
    if (get_user_option('rich_editing') == 'true') {

        add_action('admin_print_scripts', 'wp_cart_print_admin_scripts');
        add_action('wp_ajax_wp_cart_get_tinymce_form', 'wp_cart_tinymce_ajax_handler'); // Add ajax action handler for tinymce
        add_filter('mce_external_plugins', "wp_cart_add_tinymce_plugin", 5);
        add_filter('mce_buttons', 'wp_cart_register_button', 5);

        // Required by TinyMCE button
//        add_action('wp_ajax_orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content', 'orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content');
//        add_action('wp_ajax_orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content', 'orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content');
    }
}

function wp_cart_add_tinymce_plugin($plugin_array) {
    $plugin_array['wp_cart_shortcode'] = WP_CART_URL . '/assets/js/tinymce/wp_shopping_cart_plugin.js';
    return $plugin_array;
}

function wp_cart_register_button($buttons) {
    $buttons[] = 'wp_cart_shortcode';
    return $buttons;
}

function wp_cart_print_admin_scripts() {
    //The following is used by the TinyMCE button.
    ?>
    <script type="text/javascript">
        var wp_cart_admin_ajax_url = '<?php echo admin_url('admin-ajax.php?action=ajax'); ?>';
    </script>
    <?php
}

function wp_cart_tinymce_ajax_handler() {
    ?>
    <style>
        #TB_window, #TB_ajaxContent {height: auto !important}
        .mceActionPanel {padding: 20px; margin-top: 10px;    border-top: 1px solid silver;}
    </style>
    <script>
        function ui_for_ppsc_insert_content() {
            var extra = '';
            var content;
            var template = '<p>[wp_cart:%%PRODUCT-NAME%%:price:%%PRODUCT-PRICE%%%%EXTRA%%:end]</p>';

            var wpsppsc = document.getElementById('wpsppsc_panel');

            var product_name = document.getElementById('wpsppsc_product_name').value;
            var product_price = document.getElementById('wpsppsc_product_price').value;
            var shipping = document.getElementById('wpsppsc_shipping').value;
            var sizes = document.getElementById('wpsppsc_sizes').value;
            var colors = document.getElementById('wpsppsc_colors').value;

            var custom1_id = document.getElementById('wpsppsc_custom1_id').value;
            var custom1_vals = document.getElementById('wpsppsc_custom1_values').value;

            var custom2_id = document.getElementById('wpsppsc_custom2_id').value;
            var custom2_vals = document.getElementById('wpsppsc_custom2_values').value;

            var seq = 1; // Shopping cart needs VAR1, VAR2 etc.

            // who is active ?
            if (wpsppsc.className.indexOf('current') != -1) {
                product_name = product_name.replace(/</g, '').replace(/\n/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '').replace(/:+/g, '-');
                product_price = product_price.replace(/[^\d-.]/g, '');
                sizes = sizes.replace(/</g, '').replace(/\n/g, '').replace(/^[\s,]*/g, '').replace(/[\s,]*$/g, '').replace(/\s*,+\s*/g, '|').replace(/:+/g, '');
                colors = colors.replace(/</g, '').replace(/\n/g, '').replace(/^[\s,]*/g, '').replace(/[\s,]*$/g, '').replace(/\s*,+\s*/g, '|').replace(/:+/g, '');
                shipping = shipping.replace(/[^\d-.]/gi, '');

                custom1_id = custom1_id.replace(/[<>\r\n:]+/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '');
                custom1_vals = custom1_vals.replace(/[<>\r\n:]+/gi, '').replace(/^[\s,]*/g, '').replace(/[\s,]*$/g, '').replace(/\s*,+\s*/g, '|');

                custom2_id = custom2_id.replace(/[<>\r\n:]+/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '');
                custom2_vals = custom2_vals.replace(/[<>\r\n:]+/gi, '').replace(/^[\s,]*/g, '').replace(/[\s,]*$/g, '').replace(/\s*,+\s*/g, '|');

                // Validations
                if (product_name == '') {
                    alert('Please enter product name');
                    document.getElementById('wpsppsc_product_name').focus();
                    return false;
                }

                product_price = product_price || 0;

                if (product_price == 0) {
                    alert('Please enter product price');
                    document.getElementById('wpsppsc_product_price').focus();
                    return false;
                }

                shipping = shipping || 0;

                if (shipping) {
                    extra += ':shipping:' + shipping;
                }

                // eg. left_bracket2wp_cart:Demo Product 1:price:15:var1[Size|Small|Medium|Large]:end]
                if (sizes) {
                    extra += ':var' + seq + '[Size|' + sizes + ']';
                    seq++;
                }

                if (colors) {
                    extra += ':var' + seq + '[Colour|' + colors + ']';
                    seq++;
                }

                // e.g. custom1_id: Format | custom1_vals: PAL, Secam
                if (custom1_id) {
                    extra += ':var' + seq + '[' + custom1_id + '|' + custom1_vals + ']';
                    seq++;
                }

                if (custom2_id) {
                    extra += ':var' + seq + '[' + custom2_id + '|' + custom2_vals + ']';
                    seq++;
                }

                content = template;
                content = content.replace(/%%PRODUCT-NAME%%/ig, product_name).replace(/%%PRODUCT-PRICE%%/ig, product_price);
                content = content.replace(/%%EXTRA%%/ig, extra);
            }

            parent.tinyMCE.execCommand('mceInsertContent', false, content);

            tb_remove();

            return false;
        }
    </script>
    <form name="wpsppsc_form" action="#">
        <div class="panel_wrapper">
            <!-- panel -->
            <div id="wpsppsc_panel" class="panel current">
                <br />
                <table border="0" cellpadding="4" cellspacing="0">
                    <tr>
                        <td nowrap="nowrap">
                            <label for="wpsppsc_product_name"><?php _e("Product Name", 'WPSPPSC'); ?></label>
                        </td>
                        <td>
                            <input type="text" id="wpsppsc_product_name" name="wpsppsc_product_name" value="" />
                        </td>
                        <td>
                            Example: My Great Product
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap">
                            <label for="wpsppsc_product_price"><?php _e("Price", 'WPSPPSC'); ?></label>
                        </td>
                        <td>
                            <input type="text" id="wpsppsc_product_price" name="wpsppsc_product_price" value="" />
                        </td>
                        <td>
                            Example: 10 or 10.50
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap">
                            <label for="wpsppsc_shipping"><?php _e("Shipping", 'WPSPPSC'); ?></label>
                        </td>
                        <td>
                            <input type="text" id="wpsppsc_shipping" name="wpsppsc_shipping" value="" />
                        </td>
                        <td>
                            Example: 10 or 10.50
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap">
                            <label for="wpsppsc_sizes"><?php _e("Sizes", 'WPSPPSC'); ?></label>
                        </td>
                        <td>
                            <input type="text" id="wpsppsc_sizes" name="wpsppsc_sizes" value="" />
                        </td>
                        <td>
                            (Optional) Example: small, large, extra large
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap">
                            <label for="wpsppsc_colors"><?php _e("Colours", 'WPSPPSC'); ?></label>
                        </td>
                        <td>
                            <input type="text" id="wpsppsc_colors" name="wpsppsc_colors" value="" />
                        </td>
                        <td>
                            (Optional) Example: blue,red,orange,black,white
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" colspan="3">
                            <br/>
                            <strong>Custom Fields (Optional)</strong>
                            <p>Example: For DVD sales | Name: Format | Values: PAL, NTSC</p>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap">
                            <label for="wpsppsc_colors"><?php _e("Custom 1: Name", 'WPSPPSC'); ?></label>
                        </td>
                        <td><input type="text" id="wpsppsc_custom1_id" name="wpsppsc_custom1_id" value="" />
                        </td>
                        <td>
                            <?php _e("Values", 'WPSPPSC'); ?>
                            <input type="text" id="wpsppsc_custom1_values" name="wpsppsc_custom1_values" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap">
                            <label for="wpsppsc_colors"><?php _e("Custom 2: Name", 'WPSPPSC'); ?></label>
                        </td>
                        <td><input type="text" id="wpsppsc_custom2_id" name="wpsppsc_custom2_id" value="" />
                        </td>
                        <td>
                            <?php _e("Values", 'WPSPPSC'); ?>
                            <input type="text" id="wpsppsc_custom2_values" name="wpsppsc_custom2_values" value="" />
                        </td>
                    </tr>
                </table>
            </div>
            <!-- end panel -->

            Note: The <a href="http://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart" target="_blank">WP Simple Shopping Cart</a>
            allows you to use only two variations.
            For example if you use colours and sizes you *can't* use custom fields.
            If you use the two custom fields you *can't* use the sizes and colours fields.

            <div class="mceActionPanel">


                <div style="float: left">
                    <input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'WPSPPSC'); ?>"
                           class='app_positive_button  mceButton'
                           onclick="ui_for_ppsc_insert_content();
                                       return false;" />
                </div>

                <div style="float: right">
                    <input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'WPSPPSC'); ?>"
                           class='app_negative_button'
                           onclick="tb_remove();" />
                </div>
            </div>
        </div>
    </form>
    <?php
    die();
}
