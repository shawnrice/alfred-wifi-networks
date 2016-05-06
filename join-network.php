<?php

require_once( __DIR__ . '/__autoloader__.php' );
$alphred = new Alphred;

// Grab the SSID so we know what to join
$network = $argv[1];

$alphred->console( 'Looking for password', 4 );
if ( ! $password = $alphred->get_password( $network ) ) {
	$alphred->console( 'No password, attempting to show dialog', 4 );
	$password = $alphred->get_password_dialog();
	if ( empty( $password ) || 'canceled' === $password ) {
		die( 'Setting password aborted by user. Cannot join network.' );
	}
	$alphred->console( 'Got password, trying to save', 4 );
	if ( $alphred->save_password( $network, $password ) ) {
		$alphred->console( 'Saved password', 4 );
		$alphred->console( $network, 1 );
	} else {
		$alphred->console( 'Could not save password', 4 );
	}
}

if ( empty( $network ) ) {
	$alphred->console( 'ERROR: you must provide an SSID to connect to a network' );
	exit( 1 );
}

Network::join_network( $network, $password );
