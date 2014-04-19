<?php
	//ini_set('display_errors','On'); error_reporting(E_ALL ^ E_NOTICE);
	ini_set('display_errors','Off'); error_reporting(E_NONE);

	include('../../../wp-config.php');
	include_once('upload-config.inc.php');
	include_once('common.inc.php');

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
	if (!$db) {
		mysql_close($conn);
		error_general();
	}

	if (!nweather_checkvaliddata('temp-in', $_GET['c'], $_POST['temp-in'], 20) ||
		!nweather_checkvaliddata('temp-out', $_GET['c'], $_POST['temp-out'], 20) ||
		!nweather_checkvaliddata('hum-in', $_GET['c'], $_POST['hum-in'], 20) ||
		!nweather_checkvaliddata('hum-out', $_GET['c'], $_POST['hum-out'], 20) ||
		!nweather_checkvaliddata('pres', $_GET['c'], $_POST['pres'], 20) ||
		!nweather_checkvaliddata('dewpoint', $_GET['c'], $_POST['dewpoint'], 20) ||
		!nweather_checkvaliddata('rain', $_GET['c'], $_POST['rain'], 200) ||
		!nweather_checkvaliddata('windspeed', $_GET['c'], $_POST['windspeed'], 200)) {
			mysql_close($conn);
			error_badrequest();
	}

	// Rain alert
	if (isset($rainalert_mailto[$_GET['c']])) {
		$res = mysql_query('select unix_timestamp(`date`) from `nweather-' . $_GET['c'] .
			'` where cast(`rain` as decimal(5,1)) != ' . $_POST['rain'] . ' order by date desc limit 1');
		$row = mysql_fetch_array($res, MYSQL_NUM);
		mysql_free_result($res);
		if ($row && isset($row[0])) {
			$lastraindate = $row[0];

			$res = mysql_query('select `rain` from `nweather-' . $_GET['c'] . '` order by date desc limit 1');
			$row = mysql_fetch_array($res, MYSQL_NUM);
			$latestrainvalue = $row[0];
			mysql_free_result($res);

			if ($latestrainvalue != $_POST['rain'] && time()-$lastraindate > $rainalert_timeout[$_GET['c']]) {
				foreach ($rainalert_mailto[$_GET['c']] as $mailto) {
					nweather_sendmail($rainalert_mailfrom[$_GET['c']], $mailto,
						$rainalert_mailsubject[$_GET['c']],	$rainalert_mailmsg[$_GET['c']] .
						"\n\n--\nnweather\nhttps://github.com/nonoo/nweather-wordpress-plugin");
				}
			}
		}
	}

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

	if (!$res) {
		mysql_close($conn);
    	error_general();
    }

	mysql_free_result($res);
	mysql_close($conn);

	echo 'ok';
?>
