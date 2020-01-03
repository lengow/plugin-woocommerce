<?php
/**
 * All options to create billing and delivery addresses
 *
 * Copyright 2019 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2019 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Address Class.
 */
class Lengow_Address {

	/**
	 * @var string address type billing.
	 */
	const TYPE_BILLING = 'billing_';

	/**
	 * @var string address type shipping.
	 */
	const TYPE_SHIPPING = 'shipping_';

	/**
	 * @var array API fields for an address.
	 */
	private $_address_api_nodes = array(
		'company',
		'civility',
		'email',
		'last_name',
		'first_name',
		'first_line',
		'full_name',
		'second_line',
		'complement',
		'zipcode',
		'city',
		'state_region',
		'common_country_iso_a2',
		'phone_home',
		'phone_office',
		'phone_mobile',
	);

	/**
	 * @var array current alias of mister.
	 */
	private $_current_male = array(
		'M',
		'M.',
		'Mr',
		'Mr.',
		'Mister',
		'Monsieur',
		'monsieur',
		'mister',
		'm.',
		'mr ',
	);

	/**
	 * @var array current alias of miss.
	 */
	private $_current_female = array(
		'Mme',
		'mme',
		'Mm',
		'mm',
		'Mlle',
		'mlle',
		'Madame',
		'madame',
		'Mademoiselle',
		'madamoiselle',
		'Mrs',
		'mrs',
		'Mrs.',
		'mrs.',
		'Miss',
		'miss',
		'Ms',
		'ms',
	);

	/**
	 * @var string address type (billing or shipping).
	 */
	private $_type;

	/**
	 * @var string first name.
	 */
	private $_first_name;

	/**
	 * @var string last name.
	 */
	private $_last_name;

	/**
	 * @var string company.
	 */
	private $_company;

	/**
	 * @var string first line.
	 */
	private $_address_1;

	/**
	 * @var string second line.
	 */
	private $_address_2;

	/**
	 * @var string postcode.
	 */
	private $_postcode;

	/**
	 * @var string city.
	 */
	private $_city;

	/**
	 * @var string state name.
	 */
	private $_state;

	/**
	 * @var string country code iso a2.
	 */
	private $_country;

	/**
	 * @var string contact email.
	 */
	private $_email;

	/**
	 * @var string contact phone.
	 */
	private $_phone;

	/**
	 * Construct.
	 *
	 * @param object $data address data from API
	 * @param string $type address type (billing or shipping)
	 * @param string|null $relay_id relay id
	 */
	public function __construct( $data, $type, $relay_id = null ) {
		// set all generic fields for WooCommerce Address.
		$address_data      = $this->_extract_address_data_from_api( $data );
		$names             = $this->_get_names( $address_data );
		$address_fields    = $this->_get_address_fields( $address_data, $relay_id );
		$state             = $this->_get_state( $address_data );
		$phone_number      = $this->_get_phone_number( $address_data );
		$this->_type       = $type;
		$this->_first_name = ucfirst( strtolower( $names['firstname'] ) );
		$this->_last_name  = ucfirst( strtolower( $names['lastname'] ) );
		$this->_company    = $address_data['company'];
		$this->_address_1  = strtolower( $address_fields['address_1'] );
		$this->_address_2  = strtolower( $address_fields['address_2'] );
		$this->_postcode   = $address_data['zipcode'];
		$this->_city       = ucfirst( strtolower( preg_replace( '/[!<>?=+@{}_$%]/sim', '', $address_data['city'] ) ) );
		$this->_state      = $state;
		$this->_country    = $address_data['common_country_iso_a2'];
		$this->_email      = $address_data['email'];
		$this->_phone      = $phone_number;
	}

	/**
	 * Get value for a specific attribute.
	 *
	 * @param string $attribute attribute name
	 *
	 * @return string
	 */
	public function get_data( $attribute ) {
		$attribute = '_' . $attribute;

		return isset( $this->{$attribute} ) ? $this->{$attribute} : '';
	}

	/**
	 * Set value for a specific attribute.
	 *
	 * @param string $attribute attribute name
	 * @param string $value attribute value
	 */
	public function set_data( $attribute, $value ) {
		$attribute = '_' . $attribute;
		if ( isset( $this->{$attribute} ) ) {
			$this->{$attribute} = $value;
		}
	}

	/**
	 * Return data formatted for a WooCommerce address (Billing or Shipping).
	 *
	 * @return array
	 */
	public function get_formatted_data() {
		global $woocommerce;

		$address = array();
		$fields  = $woocommerce->countries->get_address_fields( $this->_country, $this->_type );
		foreach ( $fields as $key => $field ) {
			$attribute       = '_' . str_replace( $this->_type, '', $key );
			$address[ $key ] = isset( $this->{$attribute} ) ? $this->{$attribute} : '';
		}

		return $address;
	}

	/**
	 * Extract address data from API.
	 *
	 * @param object $api API nodes containing the data
	 *
	 * @return array
	 */
	private function _extract_address_data_from_api( $api ) {
		$address_data = array();
		foreach ( $this->_address_api_nodes as $node ) {
			$address_data[ $node ] = (string) $api->{$node};
		}

		return $address_data;
	}

	/**
	 * Check if firstname or lastname are empty.
	 *
	 * @param array $address_data API address data
	 *
	 * @return array
	 */
	private function _get_names( $address_data ) {
		$names = array(
			'firstname' => trim( $address_data['first_name'] ),
			'lastname'  => trim( $address_data['last_name'] ),
			'fullname'  => $this->_clean_full_name( $address_data['full_name'] ),
		);
		if ( empty( $names['firstname'] ) ) {
			if ( ! empty( $names['lastname'] ) ) {
				$names = $this->_split_names( $names['lastname'] );
			}
		}
		if ( empty( $names['lastname'] ) ) {
			if ( ! empty( $names['firstname'] ) ) {
				$names = $this->_split_names( $names['firstname'] );
			}
		}
		// check full name if last_name and first_name are empty.
		if ( empty( $names['lastname'] ) && empty( $names['firstname'] ) ) {
			$names = $this->_split_names( $names['fullname'] );
		}
		if ( empty( $names['lastname'] ) ) {
			$names['lastname'] = '__';
		}
		if ( empty( $names['firstname'] ) ) {
			$names['firstname'] = '__';
		}

		return $names;
	}

	/**
	 * Clean fullname field without salutation.
	 *
	 * @param string $fullname fullname of the customer
	 *
	 * @return string
	 */
	private function _clean_full_name( $fullname ) {
		$split = explode( ' ', $fullname );
		if ( $split && ! empty( $split ) ) {
			$fullname = ( in_array( $split[0], $this->_current_male ) || in_array( $split[0], $this->_current_female ) )
				? ''
				: $split[0];
			for ( $i = 1; $i < count( $split ); $i ++ ) {
				if ( ! empty( $fullname ) ) {
					$fullname .= ' ';
				}
				$fullname .= $split[ $i ];
			}
		}

		return $fullname;
	}

	/**
	 * Split fullname.
	 *
	 * @param string $fullname fullname of the customer
	 *
	 * @return array
	 */
	private function _split_names( $fullname ) {
		$split = explode( ' ', $fullname );
		if ( $split && ! empty( $split ) ) {
			$names['firstname'] = $split[0];
			$names['lastname']  = '';
			for ( $i = 1; $i < count( $split ); $i ++ ) {
				if ( ! empty( $names['lastname'] ) ) {
					$names['lastname'] .= ' ';
				}
				$names['lastname'] .= $split[ $i ];
			}
		} else {
			$names['firstname'] = '__';
			$names['lastname']  = empty( $fullname ) ? '__' : $fullname;
		}

		return $names;
	}

	/**
	 * Get clean address fields.
	 *
	 * @param array $address_data API address data
	 * @param string|null $relay_id relay id
	 *
	 * @return array
	 */
	private function _get_address_fields( $address_data, $relay_id = null ) {
		$address_1  = trim( $address_data['first_line'] );
		$address_2  = trim( $address_data['second_line'] );
		$complement = trim( $address_data['complement'] );
		if ( empty( $address_1 ) ) {
			if ( ! empty( $address_2 ) ) {
				$address_1 = $address_2;
				$address_2 = '';
			} elseif ( ! empty( $complement ) ) {
				$address_1  = $complement;
				$complement = '';
			}
		}
		if ( empty( $address_2 ) && ! empty( $complement ) ) {
			$address_2 = $complement;
		}
		// get relay id for shipping address.
		if ( null !== $relay_id && self::TYPE_SHIPPING === $this->_type ) {
			$relay_id  = 'Relay id: ' . $relay_id;
			$address_2 .= ! empty( $address_2 ) ? ' - ' . $relay_id : $relay_id;
		}

		return array(
			'address_1' => $address_1,
			'address_2' => $address_2,
		);
	}

	/**
	 * Get state name.
	 *
	 * @param array $address_data API address data
	 *
	 * @return string
	 */
	private function _get_state( $address_data = array() ) {
		$state                = '';
		$wc_countries         = new WC_Countries();
		$country_iso_a2       = $address_data['common_country_iso_a2'];
		$states               = $wc_countries->get_states( $country_iso_a2 );
		$state_region         = strtoupper( trim( $address_data['state_region'] ) );
		$state_region_cleaned = $this->_clean_string( $address_data['state_region'] );
		if ( ! empty( $states ) && ! empty( $state_region ) ) {
			if ( array_key_exists( $state_region, $states ) ) {
				$state = $state_region;
			} else {
				$results = array();
				foreach ( $states as $code => $name ) {
					$name_cleaned = $this->_clean_string( $name );
					similar_text( $state_region_cleaned, $name_cleaned, $percent );
					if ( $percent > 70 ) {
						$results[ (int) $percent ] = $code;
					}
				}
				if ( ! empty( $results ) ) {
					krsort( $results );
					$state = current( $results );
				}
			}
		}

		return $state;
	}

	/**
	 * Cleaning a string before search.
	 *
	 * @param string $string string to clean
	 *
	 * @return string
	 */
	private function _clean_string( $string ) {
		$cleanFilters = array( ' ', '-', '_', '.' );
		$string       = strtolower( str_replace( $cleanFilters, '', trim( $string ) ) );
		$string       = Lengow_Main::replace_accented_chars( html_entity_decode( $string ) );

		return $string;
	}

	/**
	 * Get clean phone number.
	 *
	 * @param array $address_data API address data
	 *
	 * @return string
	 */
	private function _get_phone_number( $address_data = array() ) {
		$phone_number = '';
		if ( ! empty( $address_data['phone_home'] ) ) {
			$phone_number = $address_data['phone_home'];
		} elseif ( ! empty( $address_data['phone_mobile'] ) ) {
			$phone_number = $address_data['phone_mobile'];
		} elseif ( ! empty( $address_data['phone_office'] ) ) {
			$phone_number = $address_data['phone_office'];
		}

		return Lengow_Main::clean_phone( $phone_number );
	}
}
