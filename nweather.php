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
	$result = '<div id="nweather-navbar">';
	$result .= "<a href=\"#\" id=\"nweather-navbar-3d\" onclick=\"nweather_updateinterval('$context', '3d'); return false;\">3 " . __('days', 'nweather-wordpress-plugin') . '</a>';
	$result .= "<a href=\"#\" id=\"nweather-navbar-1w\" onclick=\"nweather_updateinterval('$context', '1w'); return false;\">1 " . __('week', 'nweather-wordpress-plugin') . '</a>';
	$result .= "<a href=\"#\" id=\"nweather-navbar-1m\" onclick=\"nweather_updateinterval('$context', '1m'); return false;\">1 " . __('month', 'nweather-wordpress-plugin') . '</a>';
	$result .= "<a href=\"#\" id=\"nweather-navbar-6m\" onclick=\"nweather_updateinterval('$context', '6m'); return false;\">6 " . __('months', 'nweather-wordpress-plugin') . '</a>';
	$result .= "<a href=\"#\" id=\"nweather-navbar-1y\" onclick=\"nweather_updateinterval('$context', '1y'); return false;\">1 " . __('year', 'nweather-wordpress-plugin') . '</a>';
	$result .= "<a href=\"#\" id=\"nweather-navbar-5y\" onclick=\"nweather_updateinterval('$context', '5y'); return false;\">5 " . __('years', 'nweather-wordpress-plugin') . '</a>';
	$result .= '</div>';

	return $result;
}

function nweather_creategraph($context, $name, $label) {
	global $wpdb;

	$result = "<div id=\"nweather-graph-$name-container\" class=\"nweather-graph-container closed\">";
	$result .= '	<div class="nweather-graph-title">';
	$result .= "		<a href=\"#\" onclick=\"nweather_togglegraph('$context', '$name', '" . __($label, 'nweather-wordpress-plugin') . "'); return false;\">" . __($name, 'nweather-wordpress-plugin') . ' <span class="nweather-graph-openclosearrow">▸</span></a>';
	$result .= '		<span class="nweather-currvalue">';
	$result .= 				$wpdb->get_var("select `$name` from `nweather-" . $wpdb->escape($context) . '` order by `date` desc limit 1') . ' ' . __($label, 'nweather-wordpress-plugin');
	$result .= '		</span>';
	$result .= '	</div>';
	$result .= '</div>';

	return $result;
}

function nweather_generate($context) {
	global $wpdb;

	if (!$wpdb->get_results('show tables like "nweather-' . $wpdb->escape($context) . '"'))
		return nweather_geterrorstring(__('no such context', 'nweather-wordpress-plugin'));

	$out = nweather_createnavbar($context);
	$out .= nweather_creategraph($context, 'temp-in', '°C');
	$out .= nweather_creategraph($context, 'temp-out', '°C');
	$out .= nweather_creategraph($context, 'hum-in', '%');
	$out .= nweather_creategraph($context, 'hum-out', '%');
	$out .= nweather_creategraph($context, 'pres', 'hPa');
	$out .= nweather_creategraph($context, 'dewpoint', '°C');
	$out .= nweather_creategraph($context, 'rain', 'mm');
	$out .= nweather_creategraph($context, 'windspeed', 'km/h');
	$out .= nweather_creategraph($context, 'winddir', 'degree');

	$out .= "<script type=\"text/javascript\">nweather_updateinterval('$context');</script>";

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
	echo '<script type="text/javascript">';
	echo '	var nweather_plugin_url = "' . plugins_url('', __FILE__) . '/";';
	echo '	var nweather_plugin_timelabel = "' . __('Time', 'nweather-wordpress-plugin') . '";';
	echo '	var nweather_plugin_valuelabel = "' . __('Value', 'nweather-wordpress-plugin') . '";';
	echo '</script>';
}
add_action('wp_head', 'nweather_jscss');
?>
