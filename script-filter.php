<?php

require_once( __DIR__ . '/__autoloader__.php' );

$alphred = new Alphred;

$subtitle = '';
if ( Network::get_wifi_power() ) {
	$wifi = Network::get_current_wifi_network();
	if ( 'You are not associated with an AirPort network.' === $wifi ) {
		$wifi = '<not connected>';
	} else {
		$subtitle = 'IP: ' . Network::get_network_info()['IP address'];
		$alphred->console( print_r( Network::get_network_info(), true ), 4 );
	}
	$title = "WiFi: {$wifi}";
} else {
	$title = 'WiFi is off.';
}

$alphred->add_result([
	'title' => $title,
	'subtitle' => $subtitle,
]);
$alphred->add_result([
	'title' => 'Scan for and join WiFi Networks',
]);

$alphred->console( print_r( Network::get_network_info(), true ), 4 );

$alphred->to_xml();
