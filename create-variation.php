<?php
/**
 * Created by PhpStorm.
 * User: Justin Butler
 * Date: 6/1/2018
 * Time: 8:42 AM
 */
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
    wp_set_post_terms( $product_id, $term_name, $taxonomy, false );

    // Set/save the attribute data in the product variation
    $string = strtolower(str_replace(' ','-',str_replace(['(',')'],'',$attribute)));
    //change this to be more dynamic and include more than two whitespace
    update_post_meta( $variation_id, 'attribute_'.$string, str_replace('  ',' ', $term_name));
}

$variation->set_weight('');
$variation->save();
