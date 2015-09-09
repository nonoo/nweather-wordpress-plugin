<?php
	ini_set('display_errors','On');
	error_reporting(E_ALL ^ E_NOTICE);

	header("Content-type: text/html; charset=UTF-8");
	header("Pragma: no-cache");
	header("Expires: 0");

	include('../../../wp-config.php');
	include_once('common.inc.php');

	if (!isset($_GET['c']) ||
		!isset($_GET['i']) ||
		!isset($_GET['d']))
			die('error: missing parameters');

	$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if (!$conn)
		die('error: database open error');

	$db = mysql_select_db(DB_NAME, $conn);
	if (!$db)
		die('error: database select error');

	$query = mysql_query('select `option_value` from `wp_options` where `option_name`="gmt_offset"');
	$row = mysql_fetch_row($query);
	$gmtoffset = $row[0];
	$timezone = date("H:i", $gmtoffset * 3600);
	if ($gmtoffset > 0)
		$timezone = '+' . $timezone;
	mysql_query("set time_zone='$timezone'");

	$query = mysql_query('show tables like "nweather-%"');

	$contextok = false;
	while ($row = mysql_fetch_row($query)) {
		if ($row[0] == "nweather-" . $_GET['c'])
			$contextok = true;
	}

	if (!$contextok)
		die('error: invalid context');

	$query = mysql_query('show columns from `nweather-' . mysql_real_escape_string($_GET['c']) . '`');

	$dataok = false;
	while ($row = mysql_fetch_row($query)) {
		if ($row[0] == $_GET['d'])
			$dataok = true;
	}

	if (!$dataok)
		die('error: invalid data');

	$query = mysql_query('select unix_timestamp(max(`date`)) from `nweather-' . mysql_real_escape_string($_GET['c']) . '`');
	$row = mysql_fetch_row($query);
	$latestitemdate = $row[0];

	switch ($_GET['i']) {
		case '3d':
			$t = strtotime('-3 days', $latestitemdate);
			break;
		case '1w':
			$t = strtotime('-1 week', $latestitemdate);
			break;
		case '1m':
			$t = strtotime('-1 month', $latestitemdate);
			break;
		case '6m':
			$t = strtotime('-6 month', $latestitemdate);
			break;
		case '1y':
			$t = strtotime('-1 year', $latestitemdate);
			break;
		case '5y':
			$t = strtotime('-5 year', $latestitemdate);
			break;
		default: 
			die('error: invalid interval');
	}

	$query = mysql_query('select `date`, `' . mysql_real_escape_string($_GET['d']) . '` from `nweather-' . mysql_real_escape_string($_GET['c']) . '` where unix_timestamp(`date`) > "' . mysql_real_escape_string($t) . '" order by `date`');

	while ($row = mysql_fetch_row($query)) {
		$time = $row[0];
		$value = $row[1];

		$value = nweather_valueconvert($_GET['d'], $value);

		echo "$time, $value\n";
	}

	mysql_close($conn);
?>
