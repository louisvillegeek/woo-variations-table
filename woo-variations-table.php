<?php
/*
Plugin Name: Woo Variations table
Plugin URI: https://lb.linkedin.com/in/alaa-rihan-6971b686
Description: Show WooCommerce variable products variations as table with filters and sorting instead of normal dropdowns.
Author: Alaa Rihan
Author URI: https://lb.linkedin.com/in/alaa-rihan-6971b686
Text Domain: woo-variations-table
Domain Path: /languages/
Version: 1.3.8
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


define("WOO_VARIATIONS_TABLE_VERSION", '1.3.8');

// Check if WooCommerce is enabled
add_action('plugins_loaded', 'check_woocommerce_enabled', 1);
function check_woocommerce_enabled(){
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices','woocommerce_disabled_notice');
        return;
    }

}

 // Display WC disabled notice
function woocommerce_disabled_notice(){
    echo '<div class="error"><p><strong>' .__('Woo Variations Table', 'woo-variations-table') .'</strong> ' .sprintf( __( 'requires %sWooCommerce%s to be installed & activated!' , 'woo-variations-table' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' ) .'</p></div>';
}

// Settings menu item
add_action('admin_menu', 'woo_variations_table_settings',99);
function woo_variations_table_settings() {
  add_submenu_page( 'woocommerce', __('Woo Variations Table', 'woo-variations-table'), __('Woo Variations Table', 'woo-variations-table'), 'manage_options', 'woo_variations_table', 'woo_variations_table_settings_page_callback' ); 
  //call register settings function
	add_action( 'admin_init', 'woo_variations_table_register_settings' );
  
}

// Register our settings
function woo_variations_table_register_settings() {
	register_setting( 'woo_variations_table_columns', 'woo_variations_table_columns' );
	register_setting( 'woo_variations_table_columns', 'woo_variations_table_show_attributes' );
}

// Settings page callback function
function woo_variations_table_settings_page_callback() {
  $default_columns = array( 
  'image_link' => 1,
  'sku' => 1,
  'variation_description' => 1,
  'dimensions' => 0,
  'weight_html' => 0,
  'stock' => 1,
  'price_html' => 1,
  );
  $columns_labels =  array( 
  'image_link' => __('Thumbnail', 'woo-variations-table'),
  'sku' => __('SKU', 'woo-variations-table'),
  'variation_description' => __('Description', 'woo-variations-table'),
  'dimensions' => __('Dimensions', 'woo-variations-table'),
  'weight_html' => __('Weight', 'woo-variations-table'),
  'stock' =>  __('Stock', 'woo-variations-table'),
  'price_html' => __('Price', 'woo-variations-table'),
  );
  $columns = get_option('woo_variations_table_columns', $default_columns);
  $showAttributes = get_option('woo_variations_table_show_attributes', '');
  ?>
<div class="wrap">
  <h1><?php echo __('Woo Variations Table Settings', 'woo-variations-table'); ?></h1>
  <form method="post" action="options.php">
      <?php settings_fields( 'woo_variations_table_columns' ); ?>
      <?php do_settings_sections( 'woo_variations_table_columns' ); ?>
      <table class="form-table">
          <tr valign="top">
          <th scope="row"><?php echo __('Columns to show', 'woo-variations-table'); ?></th>
          <td><?php woo_variations_table_create_multi_select_options('woo-variations-table-columns', $default_columns, $columns, $columns_labels); ?></td>
          </tr>
          <tr valign="top">
          <th scope="row"><?php echo __('Show Attributes', 'woo-variations-table'); ?></th>
          <td><ul style="margin-top: 5px;" class='mnt-checklist' id='woo-variations-table-attributes'><li>
            <input type='checkbox' name='woo_variations_table_show_attributes' <?php echo $showAttributes ? "checked='checked'" : '';  ?> /> Show Attributes
          </li></ul></td>
          </tr>
      </table>
      
      <?php submit_button(); ?>
  
  </form>
</div>
<?php
}


function woo_variations_table_create_multi_select_options($id, $columns, $values, $labels) { 
	echo "<ul style='margin-top: 5px;' class='mnt-checklist' id='$id' >"."\n";
	foreach ($columns as $key => $value) {
		$checked = " ";
		if (isset($values[$key])) {
			$checked = " checked='checked' ";
		}
		echo "<li>\n";
		echo "<input type='checkbox' name='woo_variations_table_columns[$key]' $checked />".$labels[$key]."\n";
		echo "</li>\n";
	}
	echo "</ul>\n";
}

// Remove default variable product add to cart
add_action( 'plugins_loaded', 'remove_variable_product_add_to_cart' );
function remove_variable_product_add_to_cart() {
  remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
}

add_action( 'woocommerce_single_product_summary', 'woo_variations_table_available_options_btn', 11 );
function woo_variations_table_available_options_btn(){
  global $product;
  	if(!$product->is_type('variable'))
  		return;

  ?>
  <div class="available-options-btn">
    <button scrollto="#variations-table" type="button" class="single_add_to_cart_button button alt"><?php echo apply_filters( 'woo_variations_table_available_options_btn_text', __('Available options', 'woo-variations-table') ); ?></button>
  </div>
  <?php
}

// Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'variations_table_scripts' );
function variations_table_scripts() {
	if(is_product()){
		wp_enqueue_script( 'vuejs', '//unpkg.com/vue@2.4.4/dist/vue.min.js', array(), '2.4.4', false );
		wp_enqueue_script( 'woo-variations-table', plugins_url( 'js/woo-variations-table.js', __FILE__), 'vuejs', WOO_VARIATIONS_TABLE_VERSION, false );
		wp_enqueue_script( 'woo-variations-table-scripts', plugins_url( 'js/woo-variations-table-scripts.js', __FILE__), array( 'jquery' ), WOO_VARIATIONS_TABLE_VERSION , true);
		wp_localize_script( 'woo-variations-table', 'localData', array(
			'ajaxURL' => admin_url( 'admin-ajax.php?add_variation_to_cart=1' ),
		) );
		wp_enqueue_style( 'woo-variations-table-style', plugins_url( 'css/woo-variations-table.css', __FILE__ ), array(), WOO_VARIATIONS_TABLE_VERSION);
	}
}

// Add ajax callback to add variation to cart
add_action( 'wp_ajax_variation_add_to_cart', 'variations_table_ajax_variation_add_to_cart' );
add_action( 'wp_ajax_nopriv_variation_add_to_cart', 'variations_table_ajax_variation_add_to_cart' );
function variations_table_ajax_variation_add_to_cart() {
    ob_start();
    $product_id        = apply_filters( 'vartable_add_to_cart_product_id', absint( $_POST['product_id'] ) );
    $quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
    $variation_id      = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
    $variations         = variations_table_get_variation_data_from_variation_id($variation_id);

    $passed_validation = apply_filters( 'vartable_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);

    if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
        do_action( 'vartable_ajax_added_to_cart', $product_id );
        if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
            wc_add_to_cart_message( $product_id );
        }
        // Return fragments
        WC_AJAX::get_refreshed_fragments();
    } else {
        // If there was an error adding to the cart, redirect to the product page to show any errors
        $data = array(
            'error' => true,
            'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
        );
        wp_send_json( $data );
    }

    die();
}

function variations_table_get_variation_data_from_variation_id( $variation_id ) {
    $_product = new WC_Product_Variation( $variation_id );
    $variation_data = $_product->get_variation_attributes();
    return $variation_data; // $variation_data will return only the data which can be used to store variation data
}

// Update database
add_action('admin_init', 'variations_table_database_update');
function variations_table_database_update(){
  $plugin_db_version = get_option('woo_variations_table_db_version', '1.1');
  if (in_array($plugin_db_version, array('1.1', '1.0', '0.9.0', '0.8.1'))){
    $activeColumns = get_option('woo_variations_table_columns');
    if(isset($activeColumns['weight'])){
      $activeColumns['weight_html'] = $activeColumns['weight'];
      unset($activeColumns['weight']);
      update_option('woo_variations_table_columns', $activeColumns);
    }
    update_option('woo_variations_table_show_attributes', '');
  }
  if($plugin_db_version != WOO_VARIATIONS_TABLE_VERSION){
    update_option('woo_variations_table_db_version', WOO_VARIATIONS_TABLE_VERSION);
  }
}
//code review start
add_action('admin_footer', 'display_import_button');
function display_import_button(){
    ?>
    <script type="text/javascript">

        let attributes = document.getElementById("product_attributes");
        let toolbar = attributes.getElementsByClassName("toolbar");


        let form = document.createElement("form");
        form.type = "form";
        form.method = "POST";
        form.name = "csv_form";
        form.action = "";
        form.className = "form";
        form.style.marginTop = "10px";
        form.enctype = "multipart/form-data";
        toolbar[0].appendChild(form);


        let button = document.createElement("input");
        button.type = "submit";
        button.name = "submit_button";
        button.className = "button import_csv";
        button.value = "Import";
        button.style.marginLeft = "30px";

        let file = document.createElement("input");
        file.type = "file";
        file.className = "csv_file";
        file.id = 'csv_name';
        file.innerHTML = "Choose File";
        file.style.marginLeft = "5px";
        file.name = "csv_name";

        let the_form = toolbar[0].getElementsByClassName("form");
        toolbar[0].appendChild(form);
        the_form[0].appendChild(button);
        the_form[0].appendChild(file);


    </script>

    <?php
    if($_POST['submit_button']) {
        $csv = $_FILES["csv_name"]["tmp_name"];

        $file = fopen($csv,"r");
        $keys = fgetcsv($file);

        $values = [];

        while(! feof($file))
        {
            $values[] = fgetcsv($file);
        }

        $cols = [];
        for ($i = 0; $i < sizeof($values);$i++){
            $row = $values[$i];
            for ($j = 0; $j < sizeof($row);$j++){
                $cols[$j][] = $row[$j];
            }
        }

        $table = [];
        $i = 0;
        foreach ($keys as $val){
            //$keyval = array($val => $cols[$i]);
            $table[$val] = $cols[$i];
            $i++;
        }


        //update the database
        $id = get_the_ID();
        wcproduct_set_attributes($id,$table);


        //get old variations
        $product_temp = wc_get_product($id);
        $variation_ids = $product_temp->get_children();


        //create new variation tables
        $variation_data =  [];
        for($i = 0; $i < sizeof($table['Model'])-1; $i++){
            for($j = 0; $j < sizeof($keys); $j++){
                $variation_data[$keys[$j]] = $table[$keys[$j]][$i];
            }
            create_product_variation($id, $variation_data);
            $variation_data =  [];
        }

        //delete old variations
        foreach ($variation_ids as $id){
            wp_delete_post($id);
        }


        header("Refresh:0");
      //  $wc_product = wc_get_product($id);
       // $wc_product->save();
      //  $wc_product->apply_changes();
    }
}



//Currently does not like adding values that are arrays for the attributes

// @param int $post_id - The id of the post that you are setting the attributes for
// @param array[] $attributes - This needs to be an array containing ALL your attributes so it can insert them in one go
function wcproduct_set_attributes($post_id, $attributes) {
    $i = 0;
    // Loop through the attributes array
    foreach ($attributes as $name => $value) {

        //wp_set_object_terms($post_id, $value[1], $name, true);
        $str = implode(' | ', $value);
        //var_dump($str);
        wp_set_object_terms($post_id, $str, $name, true);
        $product_attributes[$i] = array(
            'name' => htmlspecialchars(stripslashes($name)), // set attribute name
            'value' => htmlspecialchars(stripslashes($str)), // set attribute value
            //'value' => "test1 | test2 | test3", // set attribute value
            'position' => 0,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 0
        );
        if($product_attributes[$i]['name'] == 'List Price') $product_attributes[$i]['is_variation'] = 0;
        $i++;



    }
    // Now update the post with its new attributes
    update_post_meta($post_id, '_product_attributes', $product_attributes);
}



/**
 * Create a product variation for a defined variable product ID.
 *
 * @since 3.0.0
 * @param int   $product_id | Post ID of the product parent variable product.
 * @param array $variation_data | The data to insert in the product.
 */
function create_product_variation( $product_id, $variation_data ){
    // Get the Variable product object (parent)
    $product = wc_get_product($product_id);




    $variation_post = array(
        'post_title'  => $product->get_title(),
        'post_name'   => 'product-'.$product_id.'-variation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => $product->get_permalink()
    );

    // Creating the product variation
    $variation_id = wp_insert_post( $variation_post );

    // Get an instance of the WC_Product_Variation object
    $variation = new WC_Product_Variation( $variation_id );


    // Iterating through the variations attributes
    //foreach ($variation_data['attributes'] as $attribute => $term_name )
    foreach ($variation_data as $attribute => $term_name )
    {
       // var_dump($term_name);
        $taxonomy = 'pa_'.$attribute; // The attribute taxonomy

        // Check if the Term name exist and if not we create it.
        if( ! term_exists( $term_name, $taxonomy ) )
            wp_insert_term( $term_name, $taxonomy ); // Create the term

        $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

        // Get the post Terms names from the parent variable product.
        $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

        // Check if the post term exist and if not we set it in the parent variable product.
        //if( ! in_array( $term_name, $post_term_names ) )
        wp_set_post_terms( $product_id, $term_name, $taxonomy, false );

        // Set/save the attribute data in the product variation
        //update_post_meta( $variation_id, 'attribute_'.strtolower($taxonomy), $term_name );
        $string = strtolower(str_replace(' ','-',str_replace(['(',')'],'',$attribute)));
        //change this to be more dynamic and include more than two whitespace
        update_post_meta( $variation_id, 'attribute_'.$string, $term_name);
    }




   // $variation->set_sku( 'test' );


    $variation->set_weight(''); // weight (reseting)
    $variation->save();





}
//code review end


// Print variations table after product summary
add_filter('woocommerce_after_single_product_summary','variations_table_print_table',9);
function variations_table_print_table(){
    global $product;
    if( $product->is_type( 'variable' ) ){
        $thumb_name = apply_filters( 'woo_variations_table_thumb_name', 'shop_single');
        $productImageURL = wp_get_attachment_image_src(get_post_thumbnail_id( $product->get_id() ),  $thumb_name);
        if (is_array($productImageURL) && count($productImageURL)){
          $productImageURL = $productImageURL[0];
        }
        $variations = $product->get_available_variations();

        // Image link is no longer exist in WooCommerce 3.x so do this work around
        foreach ( $variations as $key => $variation ) {
          if(!isset($variation['image_link']) && isset($variation['image'])){
            $variations[$key]['image_link'] = $variation['image']['src'];
          }
          // price_html is empty if all variations have the same price in WooCommerce 3.x so do this work around
          if(empty($variation['price_html'])){
            $variations[$key]['price_html'] = $product->get_price_html();
          }
        }
        
        $variations = json_encode($variations);
        $product_attributes = $product->get_attributes();
        $variation_attributes = $product->get_variation_attributes();
        $attrs = array();
        foreach ( $variation_attributes as $key => $name ) {
            $correctkey = wc_sanitize_taxonomy_name( stripslashes( $key ) );
            $attrs[$correctkey]['name']= wc_attribute_label($key);
            $attrs[$correctkey]['visible'] =  $product_attributes[$correctkey]->get_visible();
            for($i=0; count($name) > $i; $i++){
                $terms = array_values($name);
                $term = get_term_by('slug', $terms[$i], $key);
                if($term){
                $attrs[$key]['options'][]=array('name'=>$term->name, 'slug'=>array_values($name)[$i]);
                }else{
                  $attrs[$correctkey]['options'][]= array('name'=>array_values($name)[$i], 'slug'=>array_values($name)[$i]);
                }
            }
        }
        $attributes = json_encode($attrs);
        $default_columns = array( 
          'image_link' => 'on',
          'sku' => 'on',
          'variation_description' => 'on',
          'dimensions' => 0,
          'weight_html' => 0,
          'stock' => 0,
          'price_html' => 'on',
        );
        $activeColumns = json_encode(get_option('woo_variations_table_columns', $default_columns));
        $showAttributes = json_encode(get_option('woo_variations_table_show_attributes', ''));
        ?>
        <div id='variations-table' class="variations-table">
            <h3 class="available-title"><?php echo esc_html_e( 'Available Options', 'woo-variations-table' );?>:</h3>
            <!-- grid component template -->
            <script type="text/x-template" id="grid-template">
              <table class="variations">
                <thead>
                  <tr>
                    <th v-for="column in columns" v-if="activeColumns[column.key] == 'on'"
                      @click="sortBy(column.key)"
                      :class="[{ active: sortKey == column.key }, column.key]">
                      {{ column.title }}
                      <span class="arrow" :class="sortOrders[column.key] > 0 ? 'asc' : 'dsc'">
                      </span>
                    </th>
                    <template v-if="showAttributes" v-for="attr in attributes">
                    <th v-if="attr.visible"> {{ attr.name }} </th>
                    </template>
                    <th class="stock" v-if="activeColumns['stock'] == 'on'" 
                      @click="sortBy('stock')"
                      :class="[{ active: sortKey == 'stock' }, 'stock']">
                      <?php echo __("Stock", 'woo-variations-table'); ?>
                      <span class="arrow" :class="sortOrders['stock'] > 0 ? 'asc' : 'dsc'">
                      </span>
                    </th>
                    <th class="quantity"><?php echo __("Quantity", 'woo-variations-table'); ?></th>
                    <th class="add-to-cart"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(entry, index) in filteredData" :class="'variation-'+entry.variation_id+ ' image-'+ imageClass(entry['image_link'])">
                    <td v-for="column in columns" :class="column.key" v-if="activeColumns[column.key] == 'on'" :data-title="column.title">
                      <span class="item" v-if="column.type == 'image'"><img v-if="imageURL(entry[column.key]) != ''" :src="imageURL(entry[column.key])"></span>
                      <span class="item" v-if="column.type == 'text' ">{{entry[column.key]}}</span>
                      <span class="item" v-if="column.type == 'html'" v-html="entry[column.key]"></span>
                    </td>
                    <template v-if="showAttributes" v-for="(attr, key, index) in entry.attributes">
                    <td v-if="attributes[key.substr(10)].visible" :data-title="attributes[key.substr(10)].name">{{ showAttributeNameFromSlug(attr, attributes[key.substr(10)].options) }}</td>
                    </template>
                    <td class="stock" v-if="activeColumns['stock'] == 'on'" data-title="Stock">
                      <span class="item">
                        <template v-if="entry['is_in_stock']">
                          <span class='in-stock' v-if="entry['availability_html']" v-html="entry['availability_html']"></span>
                          <?php do_action('woo_variations_table_after_stock', $product->get_id());  ?>
                        </template>
                        <span v-else class="out-of-stock"><?php echo __("Out of Stock", 'woo-variations-table'); ?></span>
                      </span>
                    </td>
                    <td class="quantity"><input :ref="'quantity-'+entry.variation_id" value="1" type="number" step="1" min="1" name="quantity" data-title="Qty" title="Qty" class="input-text qty text" size="4" pattern="[0-9]*" inputmode="numeric"></td>
                    <td class="add-to-cart"><button :ref="'variation-'+entry.variation_id" @click="addToCart(entry)" type="submit" class="single_add_to_cart_button button alt" :class="{added: entry.added}"><?php echo __("Add to Cart", 'woo-variations-table'); ?></button></td>
                  </tr>
                </tbody>
              </table>
            </script>
            <div id="variations">
              <div class="variation-filters">
                  <div class="filters form-inline">
                    <div class="filter">
                      <input placeholder="Keywords" name="query" v-model="searchQuery" class="form-control">
                    </div>
                    <div v-for="(attribute, key, index) in attributes" class="filter">
                        <label>{{ attribute.name }} </label>
                        <select v-model="activeFilters[index]" @change="setFilters()" class="form-control">
                          <option value=""><?php echo __('Any', 'woo-variations-table'); ?></option>
                          <option v-for="option in attribute.options" :value="'attribute_'+key+':'+option.slug">{{ option.name }}</option>
                        </select>
                    </div>
                  </div>
              </div>
              <data-grid
                :data="gridData"
                :columns="gridColumns"
                :active-columns="activeColumns"
                :filter-key="searchQuery"
                :filters="filters"
                :attributes="attributes"
                :show-attributes="showAttributes">
              </data-grid>
            </div>
            <script type="text/javascript">
                var productID = '<?php echo $product->get_id(); ?>';
                var variations = <?php echo $variations; ?>;
                var attributes = <?php echo $attributes; ?>;
                var imageURL = '<?php echo $productImageURL; ?>';
                var activeColumns = <?php echo $activeColumns; ?>;
                var showAttributes = <?php echo $showAttributes; ?>;
                // bootstrap the grid
                var vm = new Vue({
                  el: '#variations',
                  data: {
                    searchQuery: '',
                    gridColumns: [
                        {key: 'image_link', title: '', type: 'image'},
                        {key: 'sku', title: 'SKU', type: 'text'},
                        {key: 'variation_description', title: 'Description', type: 'html'},
                        {key: 'weight_html', title: 'Weight', type: 'text'},
                        {key: 'dimensions', title: 'Dimensions', type: 'text'},
                        {key: 'price_html', title: 'Price', type: 'html'}
                    ],
                    activeColumns: activeColumns,
                    gridData: variations,
                    attributes: attributes,
                    activeFilters: [],
                    filters: [],
                    isLoading: true,
                    productID: productID,
                    imageURL: imageURL,
                    showAttributes: showAttributes
                  },
                  mounted: function(){
                    var activeFilters = []
                    for (i = 0; i < Object.keys(this.attributes).length; i++) {
                          activeFilters.push("");
                    }
                    
                    this.activeFilters = activeFilters;
                  },
                  methods: {
                    setFilters: function () {
                        var activeFilters = this.activeFilters;
                        var filters = [];
                        if(activeFilters.length){
                            var filterAny = 0
                            for(i=0; i < activeFilters.length; i++){
                                    if(activeFilters[i] != ""){
                                        var tup = activeFilters[i].split(':');
                                        filters[i-filterAny] = {};
                                        filters[i-filterAny][tup[0]] = tup[1];
                                    }else{
                                        filterAny++
                                    }
                            
                            }
                        }
                        this.filters = filters;
                        return filters;
                    }
                  }
                })
            </script>
        </div>
        <?php        
    }
}
