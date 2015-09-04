<?php
	function nweather_winddir_convert($value) {
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
		return $value;
	}

	function nweather_valueconvert($dataname, $value) {
		switch ($dataname) {
			case 'winddir': $value = nweather_winddir_convert($value); break;
			case 'windgust': $value = $value*3.6; // m/s to km/h
			case 'windspeed': $value = $value*3.6; // m/s to km/h
		}
		return $value;
	}

    function nweather_sendmail($from, $to, $subject, $msg, $headers = '') {
		$header = "From: $from\nReply-To: $from\nMIME-Version: 1.0";
		if (strstr($headers, 'Content-type:') == false)
			$header .= "\nContent-type: text/plain; charset=UTF-8";
		if ($headers)
			$header .= "\n$headers";

		mail($to, '=?UTF-8?B?' . base64_encode($subject) .'?=', $msg, $header);
	}

	function nweather_checkvaliddata($dataname, $context, $currvalue, $maxdiff) {
		global $nweather_dataintervalinsec;
		$context = mysql_real_escape_string($context);
		$res = mysql_query("select `$dataname`, unix_timestamp(`date`) from `nweather-$context` order by date desc limit 1");
		$row = mysql_fetch_array($res, MYSQL_NUM);
		$latestvalue = $row[0];
		$latestvaluedate = $row[1];

		if (time()-$latestvaluedate < $nweather_dataintervalinsec[$context]+600 && abs($currvalue-$latestvalue) > $maxdiff)
			return false;
		return true;
	}
?>
