<?php
/**
 * Created by PhpStorm.
 * User: Justin Butler
 * Date: 6/1/2018
 * Time: 8:32 AM
 */

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