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
		!nweather_checkvaliddata('windspeed', $_GET['c'], $_POST['windspeed'], 40)) { // 40*3.6=144 km/h difference
			mysql_close($conn);
			error_badrequest();
	}

	// Rain alert
	if (isset($rainalert_mailto[$_GET['c']])) {
		$res = mysql_query('select `rain` from `nweather-' . $_GET['c'] . '` order by `date` desc limit 1');
		if ($res) {
			$row = mysql_fetch_array($res, MYSQL_NUM);
			if ($row && isset($row[0]))
				$latestrainvalue = $row[0];
			mysql_free_result($res);
		}

		$res = mysql_query('select unix_timestamp(`date`) from `nweather-' . $_GET['c'] .
			'` where cast(`rain` as decimal(5,2)) != cast("' . $latestrainvalue . '" as decimal(5,2))' .
			' order by `date` desc limit 1');
		if ($res) {
			$row = mysql_fetch_array($res, MYSQL_NUM);
			if ($row && isset($row[0]))
				$lastraindate = $row[0];
			mysql_free_result($res);
		}

		if (isset($latestrainvalue) && isset($lastraindate)) {
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

	if (isset($aprs_server[$_GET['c']])) {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket) {
			$result = socket_connect($socket, $aprs_server[$_GET['c']], $aprs_serverport[$_GET['c']]);
			if ($result) {
				// Authenticating
				$tosend = 'user ' . $aprs_callsign[$_GET['c']] . ' pass ' . $aprs_passcode[$_GET['c']] . "\n";
				socket_write($socket, $tosend, strlen($tosend));
				$authstartat = time();
				$authenticated = false;
				while ($msgin = socket_read($socket, 50, PHP_NORMAL_READ)) {
					if (strpos($msgin, $aprs_callsign[$_GET['c']] . ' verified') !== FALSE) {
						$authenticated = true;
						break;
					}
					// Timeout handling
					if (time()-$authstartat > 5)
						break;
				}
				if ($authenticated) {
					$res = mysql_query('select `rain` from `nweather-' . $_GET['c'] . '` where unix_timestamp(`date`) < unix_timestamp()-3600 order by `date` desc limit 1');
					if ($res) {
						$row = mysql_fetch_array($res, MYSQL_NUM);
						mysql_free_result($res);
						if ($row && isset($row[0]))
							$rain1hourago = $row[0];
					}
					$res = mysql_query('select `rain` from `nweather-' . $_GET['c'] . '` where unix_timestamp(`date`) < unix_timestamp()-86400 order by `date` desc limit 1');
					if ($res) {
						$row = mysql_fetch_array($res, MYSQL_NUM);
						mysql_free_result($res);
						if ($row && isset($row[0]))
							$rain24hourago = $row[0];
					}
					if (isset($rain1hourago) && isset($rain24hourago)) {
						$raininlast1hour = $_POST['rain']-$rain1hourago;
						if ($raininlast1hour < 0)
							$raininlast1hour = 0;
						$raininlast1hour *= 0.3937008; // Converting from cm to inches

						$raininlast24hours = $_POST['rain']-$rain24hourago;
						if ($raininlast24hours < 0)
							$raininlast24hours = 0;
						$raininlast24hours *= 0.3937008; // Converting from cm to inches

						$tempfahrenheit = $_POST['temp-out']*(9/5)+32;

						$humoutclamped = $_POST['hum-out'];
						if ($humoutclamped > 99)
							$humoutclamped = 99;

						$windspeedkph = ($_POST['windspeed']/1000)*3600;
						$windspeedmph = $windspeedkph/1.609344;

						// See: http://aprs.org/APRS-docs/WX.TXT
						//      http://homepage.ntlworld.com/wadei/aprs/APRSDEC%20demo%20output.txt
						//      http://aprs.org/APRS-docs/PROTOCOL.TXT
						$tosend = sprintf($aprs_callsign[$_GET['c']] . '>APRS,TCPIP*:@' . date('dHi') . 'z' . $aprs_coord[$_GET['c']] . '_' .
							"%03d/%03dg...t%03dr%03dp%03dh%02db%05d " . $aprs_comment[$_GET['c']] . "\n",
							nweather_winddir_convert($_POST['winddir']), $windspeedmph,
							$tempfahrenheit, $raininlast1hour*100, $raininlast24hours*100,
							$humoutclamped, $_POST['pres']*10);
						socket_write($socket, $tosend, strlen($tosend));
					}
				}
			}
			socket_close($socket);
		}
	}

	mysql_close($conn);

	echo 'ok';
?>
