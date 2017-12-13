<?php

/**
 * New Translation system base on YAML files
 * We need to edit yml file for each languages
 * /translations/yml/en.yml
 * /translations/yml/fr.yml
 *
 * Execute this script to generate files
 *
 * Installation de YAML PARSER
 *
 * sudo apt-get install php5-dev libyaml-dev
 * sudo pecl install yaml
 */

error_reporting( E_ALL );
ini_set( "display_errors", 1 );

$directory  = dirname( dirname( __FILE__ ) ) . '/translations/yml/';
$list_files = array_diff( scandir( $directory ), array( '..', '.', 'index.php' ) );
$list_files = array_diff( $list_files, array( 'en_GB.yml' ) );
array_unshift( $list_files, "en_GB.yml" );

foreach ( $list_files as $list ) {
	$yml_file = yaml_parse_file( $directory . $list );
	$locale   = basename( $directory . $list, '.yml' );
	if ( $list == 'log.yml' ) {
		$fp = fopen( dirname( dirname( __FILE__ ) ) . '/translations/en_GB.csv', 'a+' );
	} else {
		$fp = fopen( dirname( dirname( __FILE__ ) ) . '/translations/' . $locale . '.csv', 'w+' );
	}
	foreach ( $yml_file as $language => $categories ) {
		write_csv( $fp, $categories );
	}
	fclose( $fp );
}

/**
 * Write csv
 *
 * @param string $fp
 * @param string $text
 * @param array $front_key
 */
function write_csv( $fp, $text, &$front_key = array() ) {
	if ( is_array( $text ) ) {
		foreach ( $text as $k => $v ) {
			$front_key[] = $k;
			write_csv( $fp, $v, $front_key );
			array_pop( $front_key );
		}
	} else {
		$line = join( '.', $front_key ) . '|' . str_replace( "\n", '<br />', $text ) . PHP_EOL;
		fwrite( $fp, $line );
	}
}
