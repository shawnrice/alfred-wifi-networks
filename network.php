<?php
/**
 *
 */

// Import in the CFPropertyList library so that we can parse information about Wi-Fi networks
require_once( __DIR__ . '/libraries/CFPropertyList/CFPropertyList.php' );

/**
 *
 */
class Network {

	public static function get_external_ip() {
		// Create a switch for 4 and 6, or do both
	}

	public static function get_external_ipv4() {
		return file_get_contents( 'http://myexternalip.com/raw' );
	}

	public static function get_external_ipv6() {
		// Write this. I need to be in a place that has ipv6 enabled in order to find a way to do this
	}

	public static function get_mac_address( $device = false ) {
		$device = ( ! $device ) ? self::get_wifi_device() : $device;
		$cmd = "networksetup -getmacaddress {$device}";
		exec( $cmd, $output );
		// There should be only one line here; and now we scrub the output
		$output = array_shift( $output );
		$output = str_replace( 'Ethernet Address:', '', $output );
		$output = str_replace( "(Device: {$device})", '', $output );
		$output = trim( $output );

	}

	/**
	 * [parse_hardware_list description]
	 * @return array a list of all network hardware devices and information about them
	 */
	public static function parse_hardware_list() {
		// Run the shell command to get the hardware
		exec( 'networksetup -listallhardwareports', $output );

		// These are the "keys" that we care about, so we'll put them in a array here
		$keys = [
			'Hardware Port',
			'Device',
			'Ethernet Address',
		];

		// Initialize an empty list to store everything
		$list = [];
		// We'll need a counter to keep things sorted
		$counter = 0;

		// Go through each line of the output and parse it
		foreach ( $output as $line ) :
			// Now, cycle through each of the
			foreach ( $keys as $key ) :

				// Check if the line has information that we care about
				if ( false !== strpos( $line, $key ) ) {
					$length = strlen( $key ) + 1;
					// Grab the value and store it in the array
					$list[ $counter ][ $key ] = trim( substr( $line, $length ) );
					// Ethernet Address appears last, so if we're on that, increment the counter
					if ( false !== strpos( $line, 'Ethernet Address' ) ) {
						$counter++;
					}
				}

			endforeach;
		endforeach;

		// Sort the array by Device Name
		usort( $list, function ( $a, $b ) {
		    return $a['Device'] < $b['Device'] ? -1 : 1;
		});

		return $list;
	}

	/**
	 * [get_wifi_device description]
	 *
	 * Grabs the first Wi-Fi device and returns it.
	 *
	 * @return [type] [description]
	 */
	public static function get_wifi_device() {
		$hardware = self::parse_hardware_list();

		foreach ( $hardware as $item ) :
			if ( 'Wi-Fi' !== $item['Hardware Port'] ) {
				continue;
			}
			return $item['Device'];
		endforeach;
	}

	/**
	 * [get_current_wifi_network description]
	 * @return [type] [description]
	 */
	public static function get_current_wifi_network() {
		$wifi = Network::get_wifi_device();
		exec( "networksetup -getairportnetwork {$wifi}", $output );
		return trim( str_replace( 'Current Wi-Fi Network:', '', array_shift( $output ) ) );
	}

	/**
	 * [find_airport description]
	 * @return [type] [description]
	 */
	public static function find_airport() {
		$locations = [
			// Systems pre El Capitan
			'/System/Library/PrivateFrameworks/Apple80211.framework/Versions/A/Resources/airport',
			// El Capitan
			'/System/Library/PrivateFrameworks/Apple80211.framework/Versions/Current/Resources/airport',
		];

		foreach ( $locations as $location ) :
			if ( file_exists( $location ) ) {
				return $location;
			}
		endforeach;

		// Whoops. We couldn't find the airport utility
		return false;

	}

	/**
	 * [get_wifi_networks description]
	 *
	 * @todo  Maybe add in something to grab the security information, it's kind of hard to find in
	 *        the plist though...
	 *
	 * @return [type] [description]
	 */
	public static function get_wifi_networks() {

		// Scan and grab XML, unforunately as a plist
		$cmd = self::find_airport() . ' --scan --xml';
		exec( $cmd, $networks );
		// Push it all back into a string and encode to utf8 so that DOMDocument (which CFPropertyList
		// uses) will be less likely to error out.
		$network_data = utf8_encode( implode( "\n", $networks ) );

		// Initialize a container
		$networks = [];

		// Parse the plist
		$plist = new CFPropertyList\CFPropertyList();
		$plist->parse( $network_data );

		// Parse the data into a usable array and cherry pick what we want
		foreach ( $plist->toArray() as $network ) :
			$tmp = [
				'ssid'    => $network['SSID'],
				'noise'   => $network['NOISE'],
				'channel' => $network['CHANNEL'],
				'bssid'   => $network['BSSID'],
			];
			array_push( $networks, $tmp );
		endforeach;

		return $networks;
	}

	/**
	 * [get_wifi_power description]
	 * @return [type] [description]
	 */
	public static function get_wifi_power() {
		$wifi = self::get_wifi_device();
		$cmd = "networksetup -getairportpower {$wifi}";
		exec( $cmd, $output );
		$power = trim( str_replace( "Wi-Fi Power ({$wifi}):", '', array_shift( $output ) ) );

		return ( 'On' === $power );
	}

	/**
	 * [turn_wifi_off description]
	 * @return [type] [description]
	 */
	public static function turn_wifi_off() {
		// Check if wifi is on...
		if ( false === self::get_wifi_power() ) {
			// WiFi is off, so we'll just
			return;
		}
		self::alter_wifi_power( 'off' );
	}

	/**
	 * [turn_wifi_on description]
	 * @return [type] [description]
	 */
	public static function turn_wifi_on() {
		// Check if wifi is on...
		if ( false === self::get_wifi_power() ) {
			// WiFi is on, so we'll just
			return;
		}
		self::alter_wifi_power( 'on' );
	}

	/**
	 * [alter_wifi_power description]
	 * @param  [type] $status [description]
	 * @return [type]         [description]
	 */
	public static function alter_wifi_power( $status ) {
		$wifi   = self::get_wifi_device();
		$status = strtolower( $status );
		$cmd    = "networksetup -setairportpower {$wifi} {$status}";
		exec( $cmd );
	}

	/**
	 * [join_network description]
	 *
	 * @todo  find a way get feedback on whether or not this succeeds or fails
	 *
	 * @param  [type]  $ssid     [description]
	 * @param  boolean $password [description]
	 * @return [type]            [description]
	 */
	public static function join_network( $ssid, $password = false ) {
		$wifi = self::get_wifi_device();
		$password = ( $password ) ? $password : '';
		$cmd = "networksetup -setairportnetwork {$wifi} '{$ssid}' {$password}";
		exec( $cmd, $output, $status );
	}

	/**
	 * [get_proxy_info description]
	 *
	 * @todo  remove possible bad param when `explode` doesn't return a two-item array
	 * @todo  test when actually connected to a proxy
	 *
	 * @param  string $service [description]
	 * @return [type]          [description]
	 */
	public static function get_proxy_info( $service = 'Wi-Fi' ) {
		$cmd = "networksetup -getwebproxy {$service}";
		exec( $cmd, $output );
		return self::parse_to_array( $output );

		/* Returns...
			Array
			(
		    [Enabled] => No
		    [Server] =>
		    [Port] => 0
		    [Authenticated Proxy Enabled] => 0
			)
		*/
	}

	/**
	 * [get_network_info description]
	 * @param  string $service [description]
	 * @return [type]          [description]
	 */
	public static function get_network_info( $service = 'Wi-Fi' ) {
		$cmd = "networksetup -getinfo {$service}";
		exec( $cmd, $output );
		// The first line is just a header, so remove it
		array_shift( $output );

		return self::parse_to_array( $output );

	}

	/**
	 * [parse_to_array description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function parse_to_array( $data ) {
		$clean_data = [];

		foreach ( $data as $line ) :
			$tmp = explode( ':', $line, 2 );
			$clean_data[ trim( array_shift( $tmp ) ) ] = trim( array_shift( $tmp ) );
		endforeach;

		return $clean_data;
	}
}
