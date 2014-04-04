<?php
	//ini_set('display_errors','On'); error_reporting(E_ALL ^ E_NOTICE);
	ini_set('display_errors','Off'); error_reporting(E_NONE);

	include('../../../wp-config.php');
	include_once('upload-config.inc.php');

	function error_badrequest() {
		die('400 Bad Request');
	}

	function error_missingparameters() {
		die('412 Precondition Failed');
	}

	function error_forbidden() {
		die('403 Forbidden');
	}

	function error_general() {
		die('500 Internal Server Error');
	}

	$idpassok = false;
	foreach ($upload_contextpasses as $context => $pass) {
		if ($_GET['c'] == $context && $_GET['p'] == $pass)
			$idpassok = true;
	}
	if (!$idpassok)
		error_forbidden();

	if (empty($_POST))
		error_badrequest();

	// Date must be defined.
	if (!isset($_POST['date']))
		error_badrequest();

	// Don't continue if none of the parameters is set.
	if (!isset($_POST['temp-in']) &&
		!isset($_POST['temp-out']) &&
		!isset($_POST['hum-in']) &&
		!isset($_POST['hum-out']) &&
		!isset($_POST['pres']) &&
		!isset($_POST['dewpoint']) &&
		!isset($_POST['rain']) &&
		!isset($_POST['windspeed']) &&
		!isset($_POST['winddir']))
			error_missingparameters();

	if (!is_numeric($_POST['date']) ||
		!is_numeric($_POST['temp-in']) ||
		!is_numeric($_POST['temp-out']) ||
		!is_numeric($_POST['hum-in']) ||
		!is_numeric($_POST['hum-out']) ||
		!is_numeric($_POST['pres']) ||
		!is_numeric($_POST['dewpoint']) ||
		!is_numeric($_POST['rain']) ||
		!is_numeric($_POST['windspeed']))
			error_badrequest();

	$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if (!$conn)
		error_general();

	$db = mysql_select_db(DB_NAME, $conn);
	if (!$db)
		error_general();

	$res = mysql_query('replace into `nweather-' . $_GET['c'] . '` ' .
		'(`date`, `temp-in`, `temp-out`, `hum-in`, `hum-out`, `pres`, `dewpoint`, `rain`, `windspeed`, `winddir`) values (
		"' . date("Y-m-d H:i:s" , $_POST['date']) . '" ,
		"' . $_POST['temp-in'] . '" ,
		"' . $_POST['temp-out'] . '" ,
		"' . $_POST['hum-in'] . '" ,
		"' . $_POST['hum-out'] . '" ,
		"' . $_POST['pres'] . '" ,
		"' . $_POST['dewpoint'] . '" ,
		"' . $_POST['rain'] . '" ,
		"' . $_POST['windspeed'] . '" ,
		"' . mysql_real_escape_string($_POST['winddir']) . '") ');

	if (!$res)
    	error_general();

	mysql_close($conn);

	echo 'ok';
?>
