<?php
/**
 * Plugin Name: nweather wordpress plugin
 * Plugin URI: https://github.com/nonoo/nweather-wordpress-plugin
 * Description: Displays weather graphs from a MySQL database table.
 * Version: 1.0
 * Author: Nonoo, Misa
 * Author URI: http://dp.nonoo.hu/
 * License: MIT
*/

function nweather_geterrorstring($error) {
	return '[nweather ' . __('error', 'nweather-wordpress-plugin') . ']' . $error . '[/nweather ' . __('error', 'nweather-wordpress-plugin') . ']';
}

function nweather_createnavbar($context) {
	$result = '';

	return $result;
}

function nweather_creategraph($context, $name, $label) {
	$result = '<div class="nweather-graph-title">' . __($name, 'nweather-wordpress-plugin') . '</div>';
	$result .= "<div id=\"nweather-graph-$name-loading\" class=\"nweather-graph-loading\"><img src=\"" . plugins_url('images/ajax-loader.gif', __FILE__) . '" /></div>';
	$result .= "<div id=\"nweather-graph-$name\" class=\"nweather-graph\"></div>";
	$result .= '<script type="text/javascript">';
	$jsname = preg_replace('/[^a-zA-Z0-9]+/', '', $name); // Replacing non-alnum chars
	$result .= "	var dycolors = new Array();";
	$result .= "	dycolors[0] = '#7abf34';";
	$result .= "	var dylabels = new Array();";
	$result .= "	dylabels[0] = '" . __('Time', 'nweather-wordpress-plugin') . "';";
	$result .= "	dylabels[1] = '" . __('Value', 'nweather-wordpress-plugin') . "';";
	$result .= "	var nweathergraph_$jsname = new Dygraph(";
	$result .= "		document.getElementById('nweather-graph-$name'),";
	$result .= "		'" . plugins_url('getcsv.php', __FILE__) . "?c=$context&d=$name&i=6m',";
	$result .= "		{";
	$result .= "			ylabel: '" . __($label, 'nweather-wordpress-plugin') . "',";
	$result .= "			labels: dylabels,";
	$result .= "			colors: dycolors,";
	$result .= "			connectSeparatedPoints: true,";
	$result .= "			showRangeSelector: true,";
	$result .= "			drawCallback: function() { document.getElementById('nweather-graph-$name-loading').style.display = 'none'; }";
	$result .= "		});";
	$result .= "</script>";

	return $result;
}

function nweather_generate($context) {
	global $wpdb;

	if (!$wpdb->get_results('show tables like "nweather-' . $wpdb->escape($context) . '"'))
		return nweather_geterrorstring(__('no such context', 'nweather-wordpress-plugin'));

	$out = nweather_createnavbar($context);
	$out .= nweather_creategraph($context, 'temp-in', '°C');
	$out .= nweather_creategraph($context, 'temp-out', '°C');
/*	$out .= nweather_creategraph($context, 'hum-in', '%');
	$out .= nweather_creategraph($context, 'hum-out', '%');
	$out .= nweather_creategraph($context, 'pres', 'hPa');
	$out .= nweather_creategraph($context, 'dewpoint', '°C');
	$out .= nweather_creategraph($context, 'rain', 'mm');
	$out .= nweather_creategraph($context, 'windspeed', 'km/h');
	$out .= nweather_creategraph($context, 'winddir', 'degree');*/

	return $out;
}

function nweather_filter($content) {
    $startpos = strpos($content, '<nweather');
    if ($startpos === false)
		return $content;

    for ($j=0; ($startpos = strpos($content, '<nweather', $j)) !== false;) {
		$endpos = strpos($content, '>', $startpos);
		$block = substr($content, $startpos, $endpos - $startpos + 1);

		$contextstartpos = strpos($block, 'context="') + 9;
		$context = substr($block, $contextstartpos, strpos($block, '"', $contextstartpos) - $contextstartpos);

		if ($context) {
			$out = nweather_generate($context);
		} else
			$out = nweather_geterrorstring(__('no context parameter given', 'nweather-wordpress-plugin'));

		$content = str_replace($block, $out, $content);
		$j = $endpos;
    }
    return $content;
}
load_plugin_textdomain('nweather-wordpress-plugin', false, basename(dirname(__FILE__)) . '/languages');
add_filter('the_content', 'nweather_filter');
add_filter('the_content_rss', 'nweather_filter');
add_filter('the_excerpt', 'nweather_filter');
add_filter('the_excerpt_rss', 'nweather_filter');

function nweather_jscss() {
	echo '<link rel="stylesheet" type="text/css" media="screen" href="' . plugins_url('nweather.css', __FILE__) . '" />';
	echo '<script type="text/javascript" src="' . plugins_url('nweather.js', __FILE__) . '"></script>';
	echo '<script type="text/javascript" src="' . plugins_url('dygraph-combined.js', __FILE__) . '"></script>';
}
add_action('wp_head', 'nweather_jscss');
?>
