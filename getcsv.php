<?php
	ini_set('display_errors','On');
	error_reporting(E_ALL ^ E_NOTICE);

	header("Content-type: text/html; charset=UTF-8");
	header("Pragma: no-cache");
	header("Expires: 0");

	include('../../../wp-config.php');

	function db_query($query) {
		$result = mysql_query($query);
		if (!$result)
			die('error: database query error (' . $query . ')');

		return $result;
	}

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

	$query = db_query('show tables like "nweather-%"');

	$contextok = false;
	while ($row = mysql_fetch_row($query)) {
		if ($row[0] == "nweather-" . $_GET['c'])
			$contextok = true;
	}

	if (!$contextok)
		die('error: invalid context');

	$query = db_query('show columns from `nweather-' . $_GET['c'] . '`');

	$dataok = false;
	while ($row = mysql_fetch_row($query)) {
		if ($row[0] == $_GET['d'])
			$dataok = true;
	}

	if (!$dataok)
		die('error: invalid data');

	$query = db_query('select unix_timestamp(max(`date`)) from `nweather-' . $_GET['c'] . '`');
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

	$query = db_query('select `date`, `' . $_GET['d'] . '` from `nweather-' . $_GET['c'] . '` where unix_timestamp(`date`) > "' . $t . '" order by `date`');

	while ($row = mysql_fetch_row($query)) {
		$time = $row[0];
		$value = $row[1];

		switch ($_GET['d']) {
			case 'winddir':
				switch ($value) {
					case 'N': $value = 0; break;

					case 'NNE': $value = 22.5; break;
					case 'NE': $value = 45; break;
					case 'ENE': $value = 67.5; break;

					case 'E': $value = 90; break;

					case 'ESE': $value = 112.5; break;
					case 'SE': $value = 135; break;
					case 'SSE': $value = 157.5; break;

					case 'S': $value = 180; break;

					case 'SSW': $value = 202.5; break;
					case 'SW': $value = 225; break;
					case 'WSW': $value = 247.5; break;

					case 'W': $value = 270; break;

					case 'WNW': $value = 292.5; break;
					case 'NW': $value = 315; break;
					case 'NNW': $value = 337.5; break;
				}
				break;
		}

		echo "$time, $value\n";
	}

	mysql_close($conn);
?>
