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
 * (at your option) any later version.
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

	/* Country iso codes */
	const ISO_A2_ES = 'ES';
	const ISO_A2_IT = 'IT';

	/* Address types */
	const TYPE_BILLING = 'billing_';
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
	 * @var array All region codes for correspondence.
	 */
	private $_region_codes = array(
		self::ISO_A2_ES => array(
			'01' => 'VI',
			'02' => 'AB',
			'03' => 'A',
			'04' => 'AL',
			'05' => 'AV',
			'06' => 'BA',
			'07' => 'PM',
			'08' => 'B',
			'09' => 'BU',
			'10' => 'CC',
			'11' => 'CA',
			'12' => 'CS',
			'13' => 'CR',
			'14' => 'CO',
			'15' => 'C',
			'16' => 'CU',
			'17' => 'GI',
			'18' => 'GR',
			'19' => 'GU',
			'20' => 'SS',
			'21' => 'H',
			'22' => 'HU',
			'23' => 'J',
			'24' => 'LE',
			'25' => 'L',
			'26' => 'LO',
			'27' => 'LU',
			'28' => 'M',
			'29' => 'MA',
			'30' => 'MU',
			'31' => 'NA',
			'32' => 'OR',
			'33' => 'O',
			'34' => 'P',
			'35' => 'CG',
			'36' => 'PO',
			'37' => 'SA',
			'38' => 'TF',
			'39' => 'S',
			'40' => 'SG',
			'41' => 'SE',
			'42' => 'SO',
			'43' => 'T',
			'44' => 'TE',
			'45' => 'TO',
			'46' => 'V',
			'47' => 'VA',
			'48' => 'BI',
			'49' => 'ZA',
			'50' => 'Z',
			'51' => 'CE',
			'52' => 'ML',
		),
		self::ISO_A2_IT => array(
			'00' => 'RM',
			'01' => 'VT',
			'02' => 'RI',
			'03' => 'FR',
			'04' => 'LT',
			'05' => 'TR',
			'06' => 'PG',
			'07' => array(
				'07000-07019' => 'SS',
				'07020-07029' => 'OT',
				'07030-07049' => 'SS',
				'07050-07999' => 'SS',
			),
			'08' => array(
				'08000-08010' => 'OR',
				'08011-08012' => 'NU',
				'08013-08013' => 'OR',
				'08014-08018' => 'NU',
				'08019-08019' => 'OR',
				'08020-08020' => 'OT',
				'08021-08029' => 'NU',
				'08030-08030' => 'OR',
				'08031-08032' => 'NU',
				'08033-08033' => 'CA',
				'08034-08034' => 'OR',
				'08035-08035' => 'CA',
				'08036-08039' => 'NU',
				'08040-08042' => 'OG',
				'08043-08043' => 'CA',
				'08044-08049' => 'OG',
				'08050-08999' => 'NU',
			),
			'09' => array(
				'09000-09009' => 'CA',
				'09010-09017' => 'CI',
				'09018-09019' => 'CA',
				'09020-09041' => 'VS',
				'09042-09069' => 'CA',
				'09070-09099' => 'OR',
				'09100-09169' => 'CA',
				'09170-09170' => 'OR',
				'09171-09999' => 'CA',
			),
			'10' => 'TO',
			'11' => 'AO',
			'12' => array(
				'12000-12070' => 'CN',
				'12071-12071' => 'SV',
				'12072-12999' => 'CN',
			),
			'13' => array(
				'13000-13799' => 'VC',
				'13800-13999' => 'BI',
			),
			'14' => 'AT',
			'15' => 'AL',
			'16' => 'GE',
			'17' => 'SV',
			'18' => array(
				'18000-18024' => 'IM',
				'18025-18025' => 'CN',
				'18026-18999' => 'IM',
			),
			'19' => 'SP',
			'20' => array(
				'20000-20799' => 'MI',
				'20800-20999' => 'MB',
			),
			'21' => 'VA',
			'22' => 'CO',
			'23' => array(
				'23000-23799' => 'SO',
				'23800-23999' => 'LC',
			),
			'24' => 'BG',
			'25' => 'BS',
			'26' => array(
				'26000-26799' => 'CR',
				'26800-26999' => 'LO',
			),
			'27' => 'PV',
			'28' => array(
				'28000-28799' => 'NO',
				'28800-28999' => 'VB',
			),
			'29' => 'PC',
			'30' => 'VE',
			'31' => 'TV',
			'32' => 'BL',
			'33' => array(
				'33000-33069' => 'UD',
				'33070-33099' => 'PN',
				'33100-33169' => 'UD',
				'33170-33999' => 'PN',
			),
			'34' => array(
				'34000-34069' => 'TS',
				'34070-34099' => 'GO',
				'34100-34169' => 'TS',
				'34170-34999' => 'GO',
			),
			'35' => 'PD',
			'36' => 'VI',
			'37' => 'VR',
			'38' => 'TN',
			'39' => 'BZ',
			'40' => 'BO',
			'41' => 'MO',
			'42' => 'RE',
			'43' => 'PR',
			'44' => 'FE',
			'45' => 'RO',
			'46' => 'MN',
			'47' => array(
				'47000-47799' => 'FC',
				'47800-47999' => 'RN',
			),
			'48' => 'RA',
			'50' => 'FI',
			'51' => 'PT',
			'52' => 'AR',
			'53' => 'SI',
			'54' => 'MS',
			'55' => 'LU',
			'56' => 'PI',
			'57' => 'LI',
			'58' => 'GR',
			'59' => 'PO',
			'60' => 'AN',
			'61' => 'PU',
			'62' => 'MC',
			'63' => array(
				'63000-63799' => 'AP',
				'63800-63999' => 'FM',
			),
			'64' => 'TE',
			'65' => 'PE',
			'66' => 'CH',
			'67' => 'AQ',
			'70' => 'BA',
			'71' => 'FG',
			'72' => 'BR',
			'73' => 'LE',
			'74' => 'TA',
			'75' => 'MT',
			'76' => 'BT',
			'80' => 'NA',
			'81' => 'CE',
			'82' => 'BN',
			'83' => 'AV',
			'84' => 'SA',
			'85' => 'PZ',
			'86' => array(
				'86000-86069' => 'CB',
				'86070-86099' => 'IS',
				'86100-86169' => 'CB',
				'86170-86999' => 'IS',
			),
			'87' => 'CS',
			'88' => array(
				'88000-88799' => 'CZ',
				'88800-88999' => 'KR',
			),
			'89' => array(
				'89000-89799' => 'RC',
				'89800-89999' => 'VV',
			),
			'90' => 'PA',
			'91' => 'TP',
			'92' => 'AG',
			'93' => 'CL',
			'94' => 'EN',
			'95' => 'CT',
			'96' => 'SR',
			'97' => 'RG',
			'98' => 'ME',
		),
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
		if ( empty( $names['firstname'] ) && ! empty( $names['lastname'] ) ) {
			$names = $this->_split_names( $names['lastname'] );
		}
		if ( empty( $names['lastname'] ) && ! empty( $names['firstname'] ) ) {
			$names = $this->_split_names( $names['firstname'] );
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
			$fullname    = ( in_array( $split[0], $this->_current_male, true )
			                 || in_array( $split[0], $this->_current_female, true )
			) ? '' : $split[0];
			$count_split = count( $split );
			for ( $i = 1; $i < $count_split; $i ++ ) {
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
			$count_split        = count( $split );
			for ( $i = 1; $i < $count_split; $i ++ ) {
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
	 * Get country state if exist.
	 *
	 * @param array $address_data API address data
	 *
	 * @return string|false
	 */
	private function _get_state( $address_data ) {
		$state          = false;
		$country_iso_a2 = $address_data['common_country_iso_a2'];
		if ( in_array( $country_iso_a2, array( self::ISO_A2_ES, self::ISO_A2_IT ), true ) ) {
			$state = $this->_search_state_by_postcode( $country_iso_a2, $address_data['zipcode'] );
		} elseif ( ! empty( $address_data['state_region'] ) ) {
			$state = $this->_search_state_by_state_region( $country_iso_a2, $address_data['state_region'] );
		}

		return $state;
	}

	/**
	 * Search state by postcode for specific countries.
	 *
	 * @param string $country_iso_a2 country iso a2 code
	 * @param string $postcode address postcode
	 *
	 * @return string|false
	 */
	private function _search_state_by_postcode( $country_iso_a2, $postcode ) {
		$postcode_substr = substr( str_pad( $postcode, 5, '0', STR_PAD_LEFT ), 0, 2 );
		switch ( $country_iso_a2 ) {
			case self::ISO_A2_ES:
				$state = isset( $this->_region_codes[ $country_iso_a2 ][ $postcode_substr ] )
					? $this->_region_codes[ $country_iso_a2 ][ $postcode_substr ]
					: false;
				break;
			case self::ISO_A2_IT:
				$state = isset( $this->_region_codes[ $country_iso_a2 ][ $postcode_substr ] )
					? $this->_region_codes[ $country_iso_a2 ][ $postcode_substr ]
					: false;
				if ( $state && is_array( $state ) && ! empty( $state ) ) {
					$state = $this->_get_state_from_interval_postcodes( (int) $postcode, $state );
				}
				break;
			default:
				$state = false;
				break;
		}

		return $state;
	}

	/**
	 * Get short code from interval postcodes.
	 *
	 * @param integer $postcode address postcode
	 * @param array $interval_postcodes postcode intervals
	 *
	 * @return string|false
	 */
	private function _get_state_from_interval_postcodes( $postcode, $interval_postcodes ) {
		foreach ( $interval_postcodes as $interval_postcode => $state ) {
			$interval_postcodes = explode( '-', $interval_postcode );
			if ( ! empty( $interval_postcodes ) && count( $interval_postcodes ) === 2 ) {
				$min_postcode = is_numeric( $interval_postcodes[0] ) ? (int) $interval_postcodes[0] : false;
				$max_postcode = is_numeric( $interval_postcodes[1] ) ? (int) $interval_postcodes[1] : false;
				if ( ( $min_postcode && $max_postcode )
				     && ( $postcode >= $min_postcode && $postcode <= $max_postcode )
				) {
					return $state;
				}
			}
		}

		return false;
	}

	/**
	 * Search state by state region return by api.
	 *
	 * @param string $country_iso_a2 country iso a2 code
	 * @param string $state_region address state region
	 *
	 * @return string
	 */
	private function _search_state_by_state_region( $country_iso_a2, $state_region ) {
		$state                = '';
		$wc_countries         = new WC_Countries();
		$states               = $wc_countries->get_states( $country_iso_a2 );
		$state_region         = strtoupper( trim( $state_region ) );
		$state_region_cleaned = $this->_clean_string( $state_region );
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

		return Lengow_Main::replace_accented_chars( html_entity_decode( $string ) );
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
