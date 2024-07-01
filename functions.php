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
    wp_enqueue_script('admin-js', trailingslashit(get_stylesheet_directory_uri()) . 'admin-script.js', array('jquery'), null, true);
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



add_action('woocommerce_order_status_changed', 'update_physical_stock_on_order_status_change', 10, 3);

function update_physical_stock_on_order_status_change($order_id, $old_status, $new_status)
{
    $choosen_status = get_option('inventory_physical_select_1');
    $additional_status = array();
    foreach ($choosen_status as $choosen_stat) {
        $additional_status[] = str_replace('wc-', '', $choosen_stat);
    }
    if (in_array($new_status, $additional_status)) {
        $order = wc_get_order($order_id);
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();

            $physical_stock = get_post_meta($product_id, '_physical_stock', true);
            $new_physical_stock = max(0, $physical_stock - $quantity); // Ensure stock doesn't go below 0
            update_post_meta($product_id, '_physical_stock', $new_physical_stock);
        }
    }
}


add_filter('woocommerce_get_settings_pages', 'flying_eye_custom_woocommerce_settings_tab');

function flying_eye_custom_woocommerce_settings_tab($settings)
{

    if (!class_exists('WC_Settings_Custom_Tab')) {

        class WC_Settings_Custom_Tab extends WC_Settings_Page
        {
            function __construct()
            {
                $this->id = 'inventory_setting';
                $this->label = 'Inventory Setting';
                parent::__construct();
            }
        }
        $settings[] = new WC_Settings_Custom_Tab();
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
            'type' => 'sectionend',
        ),
    );

    return $settings;
}


add_action('woocommerce_admin_field_inventory_select2', 'render_inventory_select2_field');
function render_inventory_select2_field($value)
{
    $option_value = get_option($value['id'], $value['default']);
?>
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

// Add a custom column to the product listing page
add_filter('manage_edit-product_columns', 'add_inventory_column', 10, 1);
function add_inventory_column($columns)
{
    // Add a new column after the stock column
    $new_columns = [];
    foreach ($columns as $key => $column) {
        $new_columns[$key] = $column;
        if ('is_in_stock' === $key) {
            $new_columns['inventory'] = __('Inventory', 'woocommerce');
        }
    }
    return $new_columns;
}

// Populate the custom column with inventory data
add_action('manage_product_posts_custom_column', 'populate_inventory_column', 10, 2);
function populate_inventory_column($column, $post_id)
{
    if ('inventory' === $column) {
        $physical_stock = get_post_meta($post_id, '_physical_stock', true) ? get_post_meta($post_id, '_physical_stock', true) : get_post_meta($post_id, '_stock', true); ?>
        <span style="width:55px;cursor: pointer;padding: 10px 25px;" id="inventory_number_product_list_<?php echo $post_id; ?>" data-product_id="<?php echo $post_id; ?>" class="inventory_number_product_list"><?php echo $physical_stock; ?> </span>
    
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


add_action("wp_ajax_update_inventory_number", "update_inventory_number");
add_action("wp_ajax_nopriv_update_inventory_number", "update_inventory_number");

function update_inventory_number()
{
    $inventory_number = $_POST['inventory_number'];
    $product_id = $_POST['product_id'];
    update_post_meta($product_id, '_physical_stock', $inventory_number);
    $response = array('number' => $inventory_number,'product' => $product_id);
    wp_send_json_success($response,200);
    die();
}
