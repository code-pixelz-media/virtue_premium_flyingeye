<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')) :
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('child_theme_configurator_css')) :
    function child_theme_configurator_css()
    {
        wp_enqueue_style('chld_thm_cfg_child', trailingslashit(get_stylesheet_directory_uri()) . 'style.css', array('virtue_print', 'virtue_woo', 'virtue_so_pb', 'virtue_icons'));
    }
endif;
add_action('wp_enqueue_scripts', 'child_theme_configurator_css', 10);

// END ENQUEUE PARENT ACTION

add_action('admin_enqueue_scripts', 'enqueue_select2_assets');
function enqueue_select2_assets()
{
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_style('admin-css',  trailingslashit(get_stylesheet_directory_uri()) . 'admin-style.css', array());
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
    wp_enqueue_script('admin-js', trailingslashit(get_stylesheet_directory_uri()) . 'admin-script.js', array('jquery'), rand(), true);
    wp_localize_script(
        'admin-js',
        'admin_ajax',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}


// Add Physical Stock field to the product general settings tab
add_action('woocommerce_product_options_inventory_product_data', 'add_physical_stock_field');
function add_physical_stock_field()
{
    woocommerce_wp_text_input(array(
        'id' => '_physical_stock',
        'label' => __('Physical Stock', 'woocommerce'),
        'desc_tip' => true,
        'description' => __('Enter the physical stock quantity.', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => 'any',
            'min' => '0'
        )
    ));
}

// Save Physical Stock field
add_action('woocommerce_process_product_meta', 'save_physical_stock_field');
function save_physical_stock_field($post_id)
{
    $physical_stock = isset($_POST['_physical_stock']) ? wc_clean($_POST['_physical_stock']) : '';
    update_post_meta($post_id, '_physical_stock', $physical_stock);
}


add_filter('woocommerce_get_settings_pages', 'flying_eye_custom_woocommerce_settings_tab');

function flying_eye_custom_woocommerce_settings_tab($settings)
{

    if (!class_exists('WC_Settings_Inventory_Setting')) {

        class WC_Settings_Inventory_Setting extends WC_Settings_Page
        {
            function __construct()
            {
                $this->id = 'inventory_setting';
                $this->label = 'Inventory Setting';
                parent::__construct();
            }
        }
        $settings[] = new WC_Settings_Inventory_Setting();
    }

    return $settings;
}

add_filter('woocommerce_get_settings_inventory_setting', 'flying_eye_custom_woocommerce_settings_tab_settings', 10, 2);

function flying_eye_custom_woocommerce_settings_tab_settings($settings, $current_section)
{
    $order_statuses_options = get_woocommerce_order_statuses_options();
    $settings = array(
        array(
            'title' => 'Inventory Setting',
            'desc' => 'Change Inventory for Order Status:',
            'type' => 'title',
        ),
        array(
            'name' => 'Physical Stock',
            'type' => 'inventory_select2',
            'id' => 'inventory_physical_select_1',
            'default' => '',
            'options' => $order_statuses_options,
            'desc' => 'Select single (multiple) order statuses',
            'desc_tip' => 'Selected option will be the status to decrement of inventory.',
            'autoload' => false,
        ),
        array(
            'name' => 'Virtual Stock',
            'type' => 'inventory_select2',
            'id' => 'inventory_virtual_select_1',
            'default' => '',
            'options' => $order_statuses_options,
            'desc' => 'Select single (multiple) order statuses',
            'desc_tip' => 'Selected option will be the status to decrement of inventory.',
            'autoload' => false,
        ),
        array(
            'title' => 'Stock Quantity',
            'desc' => 'Select the quantity to add:',
            'type' => 'select',
            'id' => 'stock_quantity_select',
            'default' => '50',
            'options' => array(
                '20' => '20',
                '50' => '50',
                '100' => '100',
                '200' => '200',
            ),
            'desc' => 'Select the quantity to display:',
            'desc_tip' => 'Choose a stock quantity to display.',
            'autoload' => false,
        ),
        array(
            'type' => 'sectionend',
        ),
    );

    return $settings;
}

add_action('woocommerce_admin_field_inventory_select2', 'render_inventory_select2_field');
function render_inventory_select2_field($value)
{
    $option_value = get_option($value['id'], $value['default']); ?>
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?></label>
            <?php echo wc_help_tip($value['desc_tip']); ?>
        </th>
        <td class="forminp">
            <select id="<?php echo esc_attr($value['id']); ?>" name="<?php echo esc_attr($value['id']); ?>[]" style="width: 100%;" multiple="multiple" class="wc-enhanced-select">
                <?php
                foreach ($value['options'] as $key => $label) {
                    echo '<option value="' . esc_attr($key) . '" ' . selected(in_array($key, (array) $option_value), true, false) . '>' . esc_html($label) . '</option>';
                }
                ?>
            </select>
            <br><span class="description"><?php echo esc_html($value['desc']); ?></span>
        </td>
    </tr>
    <script>
        jQuery(document).ready(function($) {
            $('#<?php echo esc_attr($value['id']); ?>').select2();
        });
    </script>
    <?php
}

function get_woocommerce_order_statuses_options()
{
    $order_statuses = wc_get_order_statuses();
    $options = array();
    foreach ($order_statuses as $key => $status) {
        $options[$key] = $status;
    }
    return $options;
}

// Save the settings
add_action('woocommerce_update_options_inventory', 'flying_eye_save_custom_woocommerce_settings');

function flying_eye_save_custom_woocommerce_settings()
{
    woocommerce_update_options(flying_eye_custom_woocommerce_settings_tab_settings(array(), ''));
}

// Add a custom column to the product listing page
add_filter('manage_edit-product_columns', 'add_inventory_column', 10, 1);
function add_inventory_column($columns)
{
    // Add a new column after the stock column
    if (current_user_can('read_write_physical_stock_inventory') || current_user_can('read_physical_stock_inventory')) {
        $new_columns = [];
        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ('is_in_stock' === $key) {
                $new_columns['inventory'] = __('Inventory', 'woocommerce');
            }
        }
        return $new_columns;
    }
    return $columns;
}

// Populate the custom column with inventory data
add_action('manage_product_posts_custom_column', 'populate_inventory_column', 10, 2);
function populate_inventory_column($column, $post_id)
{
    if (current_user_can('read_write_physical_stock_inventory')) { ?>
        <style>
            #the-list tr:hover .inventory.column-inventory span::after {
                content: 'Edit';
                margin-left: 10px;
                color: #2271B1;
            }
        </style>
    <?php
    }
    if ('inventory' === $column) {
        $physical_stock = get_post_meta($post_id, '_physical_stock', true) ? get_post_meta($post_id, '_physical_stock', true) : get_post_meta($post_id, '_stock', true); ?>
        <span style="width:55px;cursor: pointer;padding: 10px 25px;" id="inventory_number_product_list_<?php echo $post_id; ?>" data-product_id="<?php echo $post_id; ?>" class="inventory_number_product_list"><?php echo $physical_stock; ?> </span>
        <?php if (current_user_can('read_write_physical_stock_inventory')) { ?>
            <div class="admin-tooltip" id="admin-tooltip_<?php echo $post_id; ?>">
                <input type="number" name="inv_number" value="<?php echo $physical_stock; ?>" class="inv_number" data-product_id="<?php echo $post_id; ?>">
                <div class="admin-tooltip-btns">
                    <input type="button" class="button button-danger inven-cancel-btn" value="close">
                    <input type="button" class="button button-primary inven-submit-btn" value="update">
                </div>
            </div>
    <?php
        }
    }
}

add_action("wp_ajax_update_inventory_number", "update_inventory_number");
add_action("wp_ajax_nopriv_update_inventory_number", "update_inventory_number");

function update_inventory_number()
{
    $inventory_number = $_POST['inventory_number'];
    $product_id = $_POST['product_id'];
    update_post_meta($product_id, '_physical_stock', $inventory_number);
    $response = array('number' => $inventory_number, 'product' => $product_id);
    wp_send_json_success($response, 200);
    die();
}

function add_theme_caps()
{
    // gets the author role
    $role = get_role('administrator');

    $role->add_cap('read_write_physical_stock_inventory');
    $role->add_cap('read_physical_stock_inventory');
}
add_action('admin_init', 'add_theme_caps');


//inventory for variable product
add_action('woocommerce_variation_options_inventory', 'custom_variation_inventory_field', 10, 3);
function custom_variation_inventory_field($loop, $variation_data, $variation)
{
    woocommerce_wp_text_input(
        array(
            'id' => 'physical_variation_inventory[' . $variation->ID . ']',
            'label' => __('Physical Inventory', 'woocommerce'),
            'desc_tip' => 'true',
            'description' => __('Enter the physical inventory for this variation.', 'woocommerce'),
            'value' => get_post_meta($variation->ID, '_physical_variation_inventory', true),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '1',
                'min' => '0'
            )
        )
    );
    woocommerce_wp_text_input(
        array(
            'id' => 'virtual_variation_inventory[' . $variation->ID . ']',
            'label' => __('Virtual Inventory', 'woocommerce'),
            'desc_tip' => 'true',
            'description' => __('Enter the virtual inventory for this variation.', 'woocommerce'),
            'value' => get_post_meta($variation->ID, '_virtual_variation_inventory', true),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '1',
                'min' => '0'
            )
        )
    );
}


// Save variable inventory fields
add_action('woocommerce_save_product_variation', 'save_variation_inventory_field', 10, 2);
function save_variation_inventory_field($variation_id, $i)
{
    if (isset($_POST['physical_variation_inventory'][$variation_id])) {
        update_post_meta($variation_id, '_physical_variation_inventory', sanitize_text_field($_POST['physical_variation_inventory'][$variation_id]));
    }
    if (isset($_POST['virtual_variation_inventory'][$variation_id])) {
        update_post_meta($variation_id, '_virtual_variation_inventory', sanitize_text_field($_POST['virtual_variation_inventory'][$variation_id]));
    }
}

// Add variation inventory field to the variation data
add_filter('woocommerce_available_variation', 'add_variation_inventory_field_to_variation_data');
function add_variation_inventory_field_to_variation_data($variation_data)
{
    $variation_data['physical_variation_inventory'] = get_post_meta($variation_data['variation_id'], '_physical_variation_inventory', true);
    return $variation_data;
}


function add_custom_submenu()
{
    add_submenu_page(
        'edit.php?post_type=product', // Parent slug
        'Inventory Update Page',        // Page title
        'Inventory Update',             // Menu title
        'manage_options',             // Capability
        'inventory-update',             // Menu slug
        'inventory_update_page_callback' // Callback function
    );
}
add_action('admin_menu', 'add_custom_submenu');

function inventory_update_page_callback()
{
    if (!class_exists('WooCommerce')) {
        echo 'WooCommerce is not active.';
        get_footer();
        exit;
    }

    // Handle form submission
    if (isset($_POST['update_inventory']) && check_admin_referer('update_inventory_nonce')) {
        if (isset($_POST['inventory']) && is_array($_POST['inventory'])) {
            foreach ($_POST['inventory'] as $id => $stock) {
                $product = wc_get_product($id);
                $stock = intval($stock);
                if ($stock >= 0) {
                    if ($product && $product->is_type('variation')) {
                        update_post_meta($id, '_physical_variation_inventory', $stock);
                    } else {
                        update_post_meta($id, '_physical_stock', $stock);
                    }
                }
            }
        }
        if (isset($_POST['virtual_inventory']) && is_array($_POST['virtual_inventory'])) {
            foreach ($_POST['virtual_inventory'] as $id => $stock) {
                $product = wc_get_product($id);
                $stock = intval($stock);
                if ($stock >= 0) {
                    if ($product && $product->is_type('variation')) {
                        update_post_meta($id, '_virtual_variation_inventory', $stock);
                    } else {
                        update_post_meta($id, '_virtual_stock', $stock);
                    }
                }
            }
        }
        if (isset($_POST['default_inventory']) && is_array($_POST['default_inventory'])) {
            foreach ($_POST['default_inventory'] as $id => $stock) {
                $stock = intval($stock);
                if ($stock >= 0) {
                    // wc_update_product_stock($id, $stock);
                    update_post_meta($id, '_stock', $stock);
                }
            }
        }
        echo '<div class="notice notice-success"><p>Inventory updated successfully.</p></div>';
    }

    // Set the number of products per page
    $products_per_page = get_option('stock_quantity_select');

    // Get the current page number
    $paged = (isset($_GET['paged']) && is_numeric($_GET['paged'])) ? intval($_GET['paged']) : 1;

    // Get the search query
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

    // Query WooCommerce products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $products_per_page,
        'paged' => $paged,
    );

    // Add search term to query if set
    if (!empty($search_query)) {
        $args['s'] = $search_query;
    }
    $loop = new WP_Query($args); ?>

    <form method="get" class="inventory-update-search-form">
        <input type="hidden" name="post_type" value="product">
        <input type="hidden" name="page" value="inventory-update">
        <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Search Products...">
        <input type="submit" value="Search" class="button">
    </form>

    <?php if ($loop->have_posts()) { ?>
        <form method="post" class="shop-inventory-form">
            <?php wp_nonce_field('update_inventory_nonce'); ?>
            <table class="wp-list-table widefat striped table-view-list posts shop-inventory-table">
                <thead class="shop-inventory-head">
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Physical Inventory</th>
                        <th>Default Stock</th>
                        <th>Virtual Stock</th>
                    </tr>
                </thead>
                <tbody class="shop-inventory-body">
                    <?php while ($loop->have_posts()) : $loop->the_post();
                        global $product;
                        $product_id = $product->get_id();
                        $product_name = $product->get_name();
                        $product_type = $product->get_type(); ?>
                        <tr>
                            <td><?php echo $product_id; ?></td>
                            <td>
                                <?php echo $product_name; ?>
                                <?php if ($product_type === 'variable') :
                                    $available_variations = $product->get_available_variations(); ?>
                                    <ul>
                                        <?php foreach ($available_variations as $variation) {
                                            $attributes = implode(', ', $variation['attributes']);
                                            echo  '<li> ' . esc_html($attributes) . '</li>';
                                        } ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product_type === 'variable') :
                                    // Input field for the whole product
                                    $product_physical_stock = get_post_meta($product_id, '_physical_stock', true); ?>
                                    <div>
                                        <input type="number" name="inventory[<?php echo $product_id; ?>]" value="<?php echo $product_physical_stock; ?>">
                                    </div>
                                    <?php foreach ($available_variations as $variation) {
                                        $variation_id = $variation['variation_id'];
                                        $attributes = implode(', ', $variation['attributes']); ?>
                                        <div>
                                            <input type="number" name="inventory[<?php echo $variation_id; ?>]" id="inventory_<?php echo $variation_id; ?>" value="<?php echo get_post_meta($variation_id, '_physical_variation_inventory', true); ?>" min="0">
                                        </div>
                                    <?php }

                                else : ?>
                                    <input type="number" name="inventory[<?php echo $product_id; ?>]" value="<?php echo get_post_meta($product_id, '_physical_stock', true); ?>" min="0">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product_type === 'variable') :
                                    foreach ($available_variations as $variation) {
                                        $variation_id = $variation['variation_id'];
                                        $attributes = implode(', ', $variation['attributes']); ?>
                                        <div>
                                            <input type="number" name="default_inventory[<?php echo $variation_id; ?>]" id="default_inventory_<?php echo $variation_id; ?>" value="<?php echo intval(get_post_meta($variation_id, '_stock', true)); ?>" min="0">
                                        </div>
                                    <?php }
                                else : ?>
                                    <input type="number" name="default_inventory[<?php echo $product_id; ?>]" value="<?php echo $product->get_stock_quantity(); ?>" min="0">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product_type === 'variable') :
                                    foreach ($available_variations as $variation) {
                                        $variation_id = $variation['variation_id'];
                                        $attributes = implode(', ', $variation['attributes']); ?>
                                        <div>
                                            <input type="number" name="virtual_inventory[<?php echo $variation_id; ?>]" id="virtual_inventory_<?php echo $variation_id; ?>" value="<?php echo intval(get_post_meta($variation_id, '_virtual_variation_inventory', true)); ?>" min="0">
                                        </div>
                                    <?php }
                                else : ?>
                                    <input type="number" name="virtual_inventory[<?php echo $product_id; ?>]" value="<?php echo get_post_meta($product_id, '_virtual_stock', true); ?>" min="0">
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <input type="submit" name="update_inventory" value="Update" class="button button-primary button-large">
        </form>


<?php
        $total_pages = $loop->max_num_pages;
        if ($total_pages > 1) {
            $current_page = max(1, $paged);

            // Determine the number of pages to display initially and in the middle section
            $end_size = 1;
            $mid_size = 7;

            if ($total_pages <= 7) {
                // Show all pages if total pages are 7 or less
                $end_size = 7;
                $mid_size = 0;
            }

            $pagination_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'current' => $current_page,
                'total' => $total_pages,
                'prev_text' => __('« Prev'),
                'next_text' => __('Next »'),
                'type' => 'array', // Generate an array of pagination links
                'end_size' => $end_size,
                'mid_size' => $mid_size,
            ));

            if (!empty($pagination_links)) {
                echo '<div class="tablenav bottom shop-inventory-table-pagination">';
                echo '<div class="tablenav-pages">';
                echo '<span class="pagination-links">';

                foreach ($pagination_links as $link) {
                    // Add appropriate classes to the pagination links
                    if (strpos($link, 'prev') !== false) {
                        $link = str_replace('prev page-numbers', 'prev-page button page-numbers', $link);
                    } elseif (strpos($link, 'next') !== false) {
                        $link = str_replace('next page-numbers', 'next-page button page-numbers', $link);
                    } elseif (strpos($link, 'current') !== false) {
                        $link = str_replace('page-numbers current', 'page-numbers tablenav-pages-navspan button disabled current', $link);
                    } else {
                        $link = str_replace('page-numbers', 'page-numbers button', $link);
                    }
                    echo $link;
                }

                echo '</span>';
                echo '</div>';
                echo '</div>';
            }
        }
    } else {
        echo __('No products found');
    }

    // Reset Query
    wp_reset_postdata();
}

// Add custom field for Virtual Stock if Inventory management is enabled
add_action('woocommerce_product_options_inventory_product_data', 'add_virtual_stock_custom_field');
function add_virtual_stock_custom_field()
{
    global $woocommerce, $post;

    echo '<div class="options_group" id="virtual_stock_field">';

    // Virtual Stock
    woocommerce_wp_text_input(
        array(
            'id' => '_virtual_stock',
            'label' => __('Virtual Stock', 'woocommerce'),
            'desc_tip' => 'true',
            'description' => __('Enter the virtual stock quantity for this product.', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        )
    );

    echo '</div>';
}

// Save custom field data
add_action('woocommerce_process_product_meta', 'save_virtual_stock_custom_field');
function save_virtual_stock_custom_field($post_id)
{
    $virtual_stock = isset($_POST['_virtual_stock']) ? $_POST['_virtual_stock'] : '';
    update_post_meta($post_id, '_virtual_stock', esc_attr($virtual_stock));
}



add_action('woocommerce_order_status_changed', 'update_stock_on_order_status_change', 10, 4);

function update_stock_on_order_status_change($order_id, $old_status, $new_status, $order)
{

    // Handle virtual stock update
    $virtual_statuses = get_option('inventory_virtual_select_1');
    $virtual_status_list = array();

    if (is_array($virtual_statuses)) {
        foreach ($virtual_statuses as $status) {
            $virtual_status_list[] = str_replace('wc-', '', $status);
        }
    }

    // Handle physical stock update
    $physical_statuses = get_option('inventory_physical_select_1');
    $physical_status_list = array();

    if (is_array($physical_statuses)) {
        foreach ($physical_statuses as $status) {
            $physical_status_list[] = str_replace('wc-', '', $status);
        }
    }

    //if new satus is refunded, stock increased by the quentity refund_status_select_1
    $refund_statuses = get_option('refund_status_select_1');
    $refund_status_list = array();

    if (is_array($refund_statuses)) {
        foreach ($refund_statuses as $status) {
            $refund_status_list[] = str_replace('wc-', '', $status);
        }
    }


    // die(var_dump(in_array($old_status, $refund_status_list) && $new_status == 'refunded'));

    // check if new status is in the list of virtual staus list
    if (!in_array($old_status, $physical_status_list) && in_array($new_status, $virtual_status_list)) {
        // Check if stock has already been updated for this order
        $stock_updated = get_post_meta($order_id, '_virtual_stock_updated', true);
        if ($stock_updated) {
            return; // Stock has already been updated, so exit the function
        }
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            $products = wc_get_product($product_id);
            if ($product->is_type('simple')) {
                $virtual_stock = get_post_meta($product_id, '_virtual_stock', true);

                if (empty($virtual_stock) || !is_numeric($virtual_stock) || $virtual_stock < 0) {
                    continue; // Skip if $virtual_stock is empty, not numeric, or less than 0
                }

                $virtual_stock = (int)$virtual_stock;
                $new_virtual_stock = max(0, $virtual_stock - $quantity); // Ensure stock doesn't go below 0
                update_post_meta($product_id, '_virtual_stock', $new_virtual_stock);
                update_post_meta($product_id, '_stock', $new_virtual_stock);
            } elseif ($products->get_type() == 'variable') {
                $variation_id = $item->get_variation_id();
                $quantity = $item->get_quantity();

                $_virtual_variation_inventory = get_post_meta($variation_id, '_virtual_variation_inventory', true);
                if ($_virtual_variation_inventory && $_virtual_variation_inventory > 0) {
                    $new_inventory = max(0, $_virtual_variation_inventory - $quantity);
                    update_post_meta($variation_id, '_virtual_variation_inventory', $new_inventory);
                    update_post_meta($variation_id, '_stock', $new_inventory);
                }
            }
        }

        // Mark the order as having had its stock updated
        update_post_meta($order_id, '_virtual_stock_updated', 'yes');
    } elseif ($new_status != 'refunded') {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $product_id = $item->get_product_id();
            $products = wc_get_product($product_id);
            $quantity = $item->get_quantity();
            if ($product->is_type('simple')) {
                $virtual_stock = get_post_meta($product_id, '_virtual_stock', true);

                if (empty($virtual_stock) || !is_numeric($virtual_stock) || $virtual_stock < 0) {
                    continue; // Skip if $virtual_stock is empty, not numeric, or less than 0
                }

                $virtual_stock = (int)$virtual_stock;
                update_post_meta($product_id, '_stock', $virtual_stock);
            } elseif ($products->get_type() == 'variable') {
                $variation_id = $item->get_variation_id();
                $_virtual_variation_inventory = get_post_meta($variation_id, '_virtual_variation_inventory', true);
                $_virtual_variation_inventory = (int)$_virtual_variation_inventory;
                // Assuming $_virtual_variation_inventory should be used to update _stock
                update_post_meta($variation_id, '_stock', $_virtual_variation_inventory);
            }
        }
    }



    // check if new status is in the list of physical staus list
    if (in_array($new_status, $physical_status_list)) {
        // Check if stock has already been updated for this order
        $stock_updated = get_post_meta($order_id, '_physical_stock_updated', true);
        if ($stock_updated) {
            return; // Stock has already been updated, so exit the function
        }

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            $products = wc_get_product($product_id);
            if ($product->is_type('simple')) {
                $physical_stock = get_post_meta($product_id, '_physical_stock', true);

                if (empty($physical_stock) || !is_numeric($physical_stock) || $physical_stock < 0) {
                    continue; // Skip if $physical_stock is empty, not numeric, or less than 0
                }

                $physical_stock = (int)$physical_stock;
                $new_physical_stock = max(0, $physical_stock - $quantity); // Ensure stock doesn't go below 0
                update_post_meta($product_id, '_physical_stock', $new_physical_stock);

                //if an order is directly set from “Devis” (not listed in the parameters Inventory Setting) to “Terminé” (completed) which is listed in PHysical Stock, then both Physical and Virtual stock should be decreased.
                if ($new_status == 'completed') {
                    $virtual_stock = get_post_meta($product_id, '_virtual_stock', true);
                    $virtual_stock = (int)$virtual_stock;
                    $new_virtual_stock = max(0, $virtual_stock - $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($product_id, '_virtual_stock', $new_virtual_stock);
                    update_post_meta($product_id, '_stock', $new_virtual_stock);
                }
            } elseif ($products->get_type() == 'variable') {
                $variation_id = $item->get_variation_id();
                $quantity = $item->get_quantity();

                $_physical_variation_inventory = get_post_meta($variation_id, '_physical_variation_inventory', true);
                if ($_physical_variation_inventory && $_physical_variation_inventory > 0) {
                    $new_inventory = max(0, $_physical_variation_inventory - $quantity);
                    update_post_meta($variation_id, '_physical_variation_inventory', $new_inventory);

                    $_physical_inventory = get_post_meta($product_id, '_physical_stock', true);
                    $new_physical_inventory = max(0, $_physical_inventory - $quantity);
                    update_post_meta($product_id, '_physical_stock', $new_physical_inventory);

                    //if an order is directly set from “Devis” (not listed in the parameters Inventory Setting) to “Terminé” (completed) which is listed in PHysical Stock, then both Physical and Virtual stock should be decreased.
                    if ($new_status == 'completed') {
                        $virtual_stock = get_post_meta($variation_id, '_virtual_variation_inventory', true);
                        $virtual_stock = (int)$virtual_stock;
                        $new_virtual_stock = max(0, $virtual_stock - $quantity); // Ensure stock doesn't go below 0
                        update_post_meta($variation_id, '_virtual_variation_inventory', $new_virtual_stock);
                        update_post_meta($variation_id, '_stock', $new_virtual_stock);
                    }
                }
            }
        }

        // Mark the order as having had its stock updated
        update_post_meta($order_id, '_physical_stock_updated', 'yes');
    }



    if (in_array($old_status, $refund_status_list) && $new_status == 'refunded') {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            $products = wc_get_product($product_id);
            if ($product->is_type('simple')) {
                $physical_stock = get_post_meta($product_id, '_physical_stock', true);

                if (empty($physical_stock) || !is_numeric($physical_stock) || $physical_stock < 0) {
                    continue; // Skip if $physical_stock is empty, not numeric, or less than 0
                }

                $physical_stock = (int)$physical_stock;
                $new_physical_stock = max(0, $physical_stock + $quantity); // Ensure stock doesn't go below 0
                update_post_meta($product_id, '_physical_stock', $new_physical_stock);


                $virtual_stock = get_post_meta($product_id, '_virtual_stock', true);
                $virtual_stock = (int)$virtual_stock;
                $new_virtual_stock = max(0, $virtual_stock + $quantity); // Ensure stock doesn't go below 0
                update_post_meta($product_id, '_virtual_stock', $new_virtual_stock);
                update_post_meta($product_id, '_stock', $new_virtual_stock);
            } elseif ($products->get_type() == 'variable') {
                $variation_id = $item->get_variation_id();
                $quantity = $item->get_quantity();

                $_virtual_variation_inventory = get_post_meta($variation_id, '_virtual_variation_inventory', true);
                $new_inventory = max(0, $_virtual_variation_inventory + $quantity);
                update_post_meta($variation_id, '_virtual_variation_inventory', $new_inventory);
                update_post_meta($variation_id, '_stock', $new_inventory);

                $_physical_variation_inventory = get_post_meta($variation_id, '_physical_variation_inventory', true);
                $new_inventory_physical = max(0, $_physical_variation_inventory + $quantity);
                update_post_meta($variation_id, '_physical_variation_inventory', $new_inventory_physical);

                $_physical_inventory = get_post_meta($product_id, '_physical_stock', true);
                $new_physical_inventory = max(0, $_physical_inventory + $quantity);
                update_post_meta($product_id, '_physical_stock', $new_physical_inventory);
            }
        }
    }

    // Handle backward status changes
    if (in_array($old_status, $physical_status_list)) {
        if (in_array($new_status, $virtual_status_list)) {
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $product_id = $item->get_product_id();
                $quantity = $item->get_quantity();
                $products = wc_get_product($product_id);
                if ($product->is_type('simple')) {
                    $physical_stock = get_post_meta($product_id, "_physical_stock", true);

                    if (empty($physical_stock) || !is_numeric($physical_stock) || $physical_stock < 0) {
                        continue; // Skip if stock is empty, not numeric, or less than 0
                    }

                    $stock = (int)$physical_stock;
                    $new_stock = max(0, $stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($product_id, "_physical_stock", $new_stock);
                } elseif ($products->get_type() == 'variable') {
                    $variation_id = $item->get_variation_id();
                    $quantity = $item->get_quantity();
                    $_physical_variation_inventory = get_post_meta($variation_id, '_physical_variation_inventory', true);
                    $stock = (int)$_physical_variation_inventory;
                    $new_stock = max(0, $stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($variation_id, "_physical_variation_inventory", $new_stock);

                    $_physical_inventory = get_post_meta($product_id, '_physical_stock', true);
                    $new_physical_inventory = max(0, $_physical_inventory + $quantity);
                    update_post_meta($product_id, '_physical_stock', $new_physical_inventory);
                }
            }
        } elseif ((!in_array($new_status, $virtual_status_list) || !in_array($new_status, $physical_status_list)) && $new_status != 'refunded') {
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $product_id = $item->get_product_id();
                $quantity = $item->get_quantity();
                $products = wc_get_product($product_id);
                if ($product->is_type('simple')) {
                    $physical_stock = get_post_meta($product_id, "_physical_stock", true);
                    $virtual_stock = get_post_meta($product_id, "_virtual_stock", true);

                    if (empty($physical_stock) || !is_numeric($physical_stock) || $physical_stock < 0) {
                        continue; // Skip if stock is empty, not numeric, or less than 0
                    }

                    $physical_stock = (int)$physical_stock;
                    $new_physical_stock = max(0, $physical_stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($product_id, "_physical_stock", $new_physical_stock);

                    if (empty($virtual_stock) || !is_numeric($virtual_stock) || $virtual_stock < 0) {
                        continue; // Skip if stock is empty, not numeric, or less than 0
                    }
                    $virtual_stock = (int)$virtual_stock;
                    $new_virtual_stock = max(0, $virtual_stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($product_id, "_virtual_stock", $new_virtual_stock);
                    update_post_meta($product_id, "_stock", $new_virtual_stock);
                } elseif ($products->get_type() == 'variable') {

                    $variation_id = $item->get_variation_id();
                    $quantity = $item->get_quantity();
                    $physical_stock = get_post_meta($variation_id, "_physical_variation_inventory", true);
                    $physical_stock = (int)$physical_stock;
                    $new_physical_stock = max(0, $physical_stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($variation_id, "_physical_variation_inventory", $new_physical_stock);

                    $_physical_inventory = get_post_meta($product_id, '_physical_stock', true);
                    $new_physical_inventory = max(0, $_physical_inventory + $quantity);
                    update_post_meta($product_id, '_physical_stock', $new_physical_inventory);


                    $virtual_stock = get_post_meta($variation_id, "_virtual_variation_inventory", true);
                    $virtual_stock = (int)$virtual_stock;
                    $new_virtual_stock = max(0, $virtual_stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($variation_id, "_virtual_variation_inventory", $new_virtual_stock);
                    update_post_meta($variation_id, "_stock", $new_virtual_stock);
                }
            }
        }
    } elseif (in_array($old_status, $virtual_status_list)) {

        if ((!in_array($new_status, $virtual_status_list) || !in_array($new_status, $physical_status_list)) && $new_status != 'refunded') {
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $product_id = $item->get_product_id();
                $quantity = $item->get_quantity();
                $products = wc_get_product($product_id);
                if ($product->is_type('simple')) {
                    $virtual_stock = get_post_meta($product_id, "_virtual_stock", true);

                    if (empty($virtual_stock) || !is_numeric($virtual_stock) || $virtual_stock < 0) {
                        continue; // Skip if stock is empty, not numeric, or less than 0
                    }
                    $virtual_stock = (int)$virtual_stock;
                    $new_virtual_stock = max(0, $virtual_stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($product_id, "_virtual_stock", $new_virtual_stock);
                    update_post_meta($product_id, "_stock", $new_virtual_stock);
                } elseif ($products->get_type() == 'variable') {
                    $variation_id = $item->get_variation_id();
                    $quantity = $item->get_quantity();
                    $virtual_stock = get_post_meta($variation_id, "_virtual_variation_inventory", true);
                    $virtual_stock = (int)$virtual_stock;
                    $new_virtual_stock = max(0, $virtual_stock + $quantity); // Ensure stock doesn't go below 0
                    update_post_meta($variation_id, "_virtual_variation_inventory", $new_virtual_stock);
                    update_post_meta($variation_id, "_stock", $new_virtual_stock);
                }
            }
        }
    }
}

// Add filter to prevent WooCommerce from reducing stock automatically
add_filter('woocommerce_can_reduce_order_stock', 'filter_woocommerce_can_reduce_order_stock', 10, 2);

function filter_woocommerce_can_reduce_order_stock($can_reduce_stock, $order)
{
    // Get the chosen virtual statuses from the option
    $virtual_statuses = get_option('inventory_virtual_select_1');
    $virtual_status_list = array();

    if (is_array($virtual_statuses)) {
        foreach ($virtual_statuses as $status) {
            $virtual_status_list[] = str_replace('wc-', '', $status);
        }
    }

    // Check if the current order status is one of the chosen statuses
    // Allow manual stock deduction for specific order statuses
    if (!in_array($order->get_status(), $virtual_status_list)) {
        $can_reduce_stock = false;
    }

    return $can_reduce_stock; // Allow WooCommerce to handle stock reduction for the chosen statuses
}
