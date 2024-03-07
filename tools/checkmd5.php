<?php

/**
 * Check MD5
 */
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

$base = dirname( __DIR__ );
$fp   = fopen( dirname( __DIR__ ) . '/config/checkmd5.csv', 'wb+' );

$list_folders = array(
	'/assets',
	'/includes',
	'/languages',
	'/upgrade',
	'/webservice',
);

$file_paths = array(
	$base . '/lengow.php',
	$base . '/config/index.php',
	$base . '/translations/en_GB.csv',
	$base . '/translations/fr_FR.csv',
);

foreach ( $list_folders as $folder ) {
	if ( file_exists( $base . $folder ) ) {
		$result     = explorer( $base . $folder );
		$file_paths = array_merge( $file_paths, $result );
	}
}
foreach ( $file_paths as $file_path ) {
	if ( file_exists( $file_path ) ) {
		$checksum = array( str_replace( $base, '', $file_path ) => md5_file( $file_path ) );
		write_csv( $fp, $checksum );
	}
}
fclose( $fp );

/**
 * Explore Folders
 *
 * @param string $path folder path
 *
 * @return array
 */
function explorer( $path ) {
	$paths = array();
	if ( is_dir( $path ) ) {
		$me = opendir( $path );
		while ( $child = readdir( $me ) ) {
			if ( $child !== '.' && $child !== '..' && $child !== 'checkmd5.csv' ) {
				$result = explorer( $path . DIRECTORY_SEPARATOR . $child );
				$paths  = array_merge( $paths, $result );
			}
		}
	} else {
		$paths[] = $path;
	}

	return $paths;
}

/**
 * Write csv
 *
 * @param resource     $fp
 * @param array|string $text
 * @param array        $front_key
 */
function write_csv( $fp, $text, &$front_key = array() ) {
	if ( is_array( $text ) ) {
		foreach ( $text as $k => $v ) {
			$front_key[] = $k;
			write_csv( $fp, $v, $front_key );
			array_pop( $front_key );
		}
	} else {
		$line = implode( '.', $front_key ) . '|' . str_replace( "\n", '<br />', $text ) . PHP_EOL;
		fwrite( $fp, $line );
	}
}
