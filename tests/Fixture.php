<?php

class Fixture extends PHPUnit_Framework_TestCase {
	public function create_item( $rest_request ) {
		$products_controler = new WC_REST_Products_Controller();
		if ( ! isset( $rest_request['status'] ) ) {
			$rest_request['status'] = 'publish';
		}
		if ( ! isset( $rest_request['name'] ) ) {
			$rest_request['name'] = 'name';
		}
		//$rest_request['type'] = 'simple';
		if ( ! isset( $rest_request['description'] ) ) {
			$rest_request['description'] = 'description';
		}
		if ( ! isset( $rest_request['short_description'] ) ) {
			$rest_request['short_description'] = 'short_description';
		}
		$wp_rest_request = new WP_REST_Request( 'POST' );
		$wp_rest_request->set_body_params( $rest_request );

		return $products_controler->create_item( $wp_rest_request );
	}

	public function loadProducts( $file ) {
		global $wpdb;
		$table   = $wpdb->prefix . 'posts';
		$delete  = $wpdb->query( "TRUNCATE TABLE $table" );
		$table1  = $wpdb->prefix . 'postmeta';
		$delete1 = $wpdb->query( "TRUNCATE TABLE $table1" );
		$table2  = $wpdb->prefix . 'lengow_product';
		$delete2 = $wpdb->query( "TRUNCATE TABLE $table2" );

		$yml = \yaml_parse_file( dirname( __FILE__ ) . '/fixtures/' . $file );

		$i = 0;
		foreach ( $yml['product'] as $product ) {
			//print_r($product); die;

			$produit_wc = $this->create_item( $product );

			wp_set_object_terms( $produit_wc->data["id"], 6, 'product_cat' );
			//var_dump($produit_wc->data["id"]);
			if ( $product["select"] == 1 ) {
				$wpdb->insert( $wpdb->prefix . 'lengow_product', array( 'product_id' => ( (int) $produit_wc->data["id"] ) ) );
			}

//            if($i<=0) {
//                print_r($this->create_item($product));
//            }else{
//                $this->create_item($product); $i++;
//            }

		}

	}

	public function loadCategories( $file ) {
		$yml = \yaml_parse_file( dirname( __FILE__ ) . '/fixtures/' . $file );

		$i = 0;
		foreach ( $yml['category'] as $category ) {
			//print_r($category); die;

			$result = wp_insert_term(
				$category["term"], // the term
				'product_cat', // the taxonomy
				array(
					'description' => $category["autres"]["description"],
					'slug'        => $category["autres"]["slug"]
				)
			);

		}

		return $result;

	}

	/**
	 * Read Last line of filename
	 *
	 * @param $file path of the filename
	 *
	 * @return string last line of the file
	 */
	public static function readLastLine( $file ) {
		$line   = '';
		$f      = fopen( $file, 'r' );
		$cursor = - 1;
		fseek( $f, $cursor, SEEK_END );
		$char = fgetc( $f );

		/**
		 * Trim trailing newline chars of the file
		 */
		while ( $char === "\n" || $char === "\r" ) {
			fseek( $f, $cursor --, SEEK_END );
			$char = fgetc( $f );
		}

		/**
		 * Read until the start of file or first newline char
		 */
		while ( $char !== false && $char !== "\n" && $char !== "\r" ) {
			/**
			 * Prepend the new char
			 */
			$line = $char . $line;
			fseek( $f, $cursor --, SEEK_END );
			$char = fgetc( $f );
		}

		return $line;
	}

	/**
	 * Test if last line of log contain text
	 *
	 * @param $text
	 * @param string $message
	 */
	public function assertLogContain( $text, $message = '' ) {
		$log      = new Lengow_Log();
		$lastLine = $this::readLastLine( 'logs/' . $log->file_name );
		self::assertTrue( (bool) strpos( $lastLine, $text ), $message );
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethod( &$object, $methodName, array $parameters = array() ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method     = $reflection->getMethod( $methodName );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $parameters );
	}

	/**
	 * Return value of a private property using ReflectionClass
	 *
	 * @param object &$instance Instantiated object that we will run method on.
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function getInnerPropertyValueByReflection( &$instance, $property = '_data' ) {
		$reflector          = new \ReflectionClass( $instance );
		$reflector_property = $reflector->getProperty( $property );
		$reflector_property->setAccessible( true );

		return $reflector_property->getValue( $instance );
	}

	/**
	 * Return value of a private property using ReflectionClass
	 *
	 * @param object &$instance Instantiated object that we will run method on.
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function setInnerPropertyValueByReflection( &$instance, $property = '_data', $value ) {
		$reflector          = new \ReflectionClass( $instance );
		$reflector_property = $reflector->getProperty( $property );
		$reflector_property->setAccessible( true );
		//$reflector_property = new \ReflectionProperty($reflector, $property);
		$reflector_property->setValue( $instance, $value );

		return true;
	}
}