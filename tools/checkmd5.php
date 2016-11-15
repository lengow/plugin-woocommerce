<?php

/*
 * Check MD5
 */
error_reporting( E_ALL );
ini_set( "display_errors", 1 );

$base = dirname( dirname( __FILE__ ) );
$fp   = fopen( dirname( dirname( __FILE__ ) ) . '/toolbox/checkmd5.csv', 'w+' );

$list_folders = array(
	'/assets',
	'/includes',
	'/languages',
	'/toolbox',
	'/upgrade',
	'/webservice',
);

$file_paths = array( $base . '/lengow.php' );

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

function explorer( $path ) {
	$paths = array();
	if ( is_dir( $path ) ) {
		$me = opendir( $path );
		while ( $child = readdir( $me ) ) {
			if ( $child != '.' && $child != '..' && $child != 'checkmd5.csv' ) {
				$result = explorer( $path . DIRECTORY_SEPARATOR . $child );
				$paths  = array_merge( $paths, $result );
			}
		}
	} else {
		$paths[] = $path;
	}

	return $paths;
}

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
