<?php

class Fixture
{
    public function create_item($rest_request) {
        $products_controler = new WC_REST_Products_Controller();
        if (!isset($rest_request['status']))
            $rest_request['status'] = $rest_request["status"];
        $rest_request['name'] = 'name';
        //$rest_request['type'] = 'simple';
        $rest_request['description'] = 'description';
        $rest_request['short_description'] = 'short_description';
        $wp_rest_request = new WP_REST_Request('POST');
        $wp_rest_request->set_body_params($rest_request);
        return $products_controler->create_item($wp_rest_request);
    }

    public function loadProducts($file)
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'posts';
        $delete = $wpdb->query("TRUNCATE TABLE $table");
        $table1  = $wpdb->prefix . 'postmeta';
        $delete1 = $wpdb->query("TRUNCATE TABLE $table1");
        $table2  = $wpdb->prefix . 'lengow_product';
        $delete2 = $wpdb->query("TRUNCATE TABLE $table2");

        $yml = \yaml_parse_file(dirname( __FILE__ ).'/fixtures/'.$file);

        $i = 0;
        foreach ($yml['product'] as $product){
            //print_r($product); die;

            $produit_wc = $this->create_item($product);
            //var_dump($produit_wc->data["id"]);
            if($product["select"] == 1)
                $wpdb->insert( $wpdb->prefix . 'lengow_product', array( 'product_id' => ( (int) $produit_wc->data["id"] ) ) );

//            if($i<=0) {
//                print_r($this->create_item($product));
//            }else{
//                $this->create_item($product); $i++;
//            }

        }

    }
}