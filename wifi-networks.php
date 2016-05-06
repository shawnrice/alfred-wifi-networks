<?php

require_once( __DIR__ . '/__autoloader__.php' );

$query = $argv[1];
$alphred = new Alphred( [ 'error_on_empty' => true ] );
maybe_make_cache_dir();

$networks = get_data();
$networks = $alphred->filter( $networks, $query, 'ssid' );

foreach ( $networks as $network ) :
	$alphred->add_result([
		'title'        => $network['ssid'],
		'subtitle'     => "Channel {$network['channel']} | Noise {$network['noise']} | BSSID {$network['bssid']}",
		'valid'        => true,
		'arg'          => $network['ssid'],
	]);
endforeach;

$alphred->to_xml();


function maybe_make_cache_dir() {
	$cache = \Alphred\Globals::cache();

	if ( ! file_exists( $cache ) && ! is_dir( $cache ) ) {
		mkdir( $cache, 0777, true );
	}
}

function get_data() {
	global $alphred;

	if ( is_cache_fresh() ) {
		$alphred->console( 'Cache is fresh (less than 30 seconds old). Using the file.', 1 );
		return json_decode( file_get_contents( cache_location() ), true );
	}

	$alphred->console( 'Cache is NOT fresh. Re-scanning for networks.', 1 );
	// Scan the networks and grab the data
	$networks = Network::get_wifi_networks();

	file_put_contents( cache_location(), json_encode( $networks ) );
	return $networks;
}

function is_cache_fresh() {
	global $alphred;

	if ( ! file_exists( cache_location() ) ) {
		$alphred->console( 'Cache file does not exist', 1 );
		return false;
	}
	return ( ( time() - stat( cache_location() )['mtime'] ) > 30 ) ? false : true;
}

function cache_location() {
	return \Alphred\Globals::cache() . '/networks.json';
}
