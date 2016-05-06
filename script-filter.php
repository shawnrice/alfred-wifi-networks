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

if ( 'light' === $alphred->theme_background() ) {
		$wifi_icon = __DIR__ . '/icons/wifi-dark.png';
} else {
		$wifi_icon = __DIR__ . '/icons/wifi-light.png';
}

if ( 'light' === $alphred->theme_background() ) {
		$info_icon = __DIR__ . '/icons/wifi-info-dark.png';
} else {
		$info_icon = __DIR__ . '/icons/wifi-info-light.png';
}

$alphred->add_result([
	'title'    => $title,
	'subtitle' => $subtitle,
	'icon'     => $info_icon,
	'valid'    => false,
]);
$alphred->add_result([
	'title'    => 'Scan for and join WiFi Networks',
	'icon'     => $wifi_icon,
	'valid'    => true,
	'arg'      => '',
]);

$alphred->to_xml();
