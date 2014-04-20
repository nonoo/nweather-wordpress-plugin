<?php
	// Data upload interval (this value is needed for data validity checking)
	$nweather_dataintervalinsec = array('context1name' => 900);

	// These contexts and passwords should be specified to the upload script as
	// HTTP GET parameters. Context names are the nweather SQL table names.
	// Rename this example file to upload-config.inc.php.
	$upload_contextpasses = array('context1name' => 'password1',
		'context2name' => 'password2');

	// Set these if you want to receive emails when it starts raining.
	// The timeout setting means you only get an email when the uploaded rain
	// is different than the stored one in the database, and the last rain
	// value change was at least that many seconds ago.
	$rainalert_timeout = array('context1name' => 10800);
	$rainalert_mailto = array('context1name' => array('email@email.com'));
	$rainalert_mailfrom = array('context1name' => 'nweather <nweather@email.com>');
	$rainalert_mailsubject = array('context1name' => "[nweather] It's raining!");
	$rainalert_mailmsg = array('context1name' => "It's raining!");

	// If you want to upload weather data to the APRS-IS network,
	// uncomment and set these parameters.
	//$aprs_server = 'hun.aprs2.net';
	//$aprs_serverport = 14580;
	//$aprs_callsign = 'HA2KDR-4';
	//$aprs_passcode = 0; // Generate this with an APRS passcode generator
	//$aprs_altinfeet = 2080;
	// Use GPS coordinate format, see http://www.csgnetwork.com/gpscoordconv.html
	//$aprs_coord = '4740.55N/01829.60E';
	//$aprs_comment = 'Gerecse WX - www.ha5kdr.hu';
?>
