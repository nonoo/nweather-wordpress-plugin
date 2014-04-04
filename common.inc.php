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
		}
		return $value;
	}
?>