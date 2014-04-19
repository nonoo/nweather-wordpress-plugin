<?php
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
?>
