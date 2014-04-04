var nweather_plugin_dygraphs = new Array();

function nweather_opengraph(context, name, label, interval) {
	$('#nweather-graph-' + name + '-container .nweather-graph-openclosearrow').html('▾');
	$('#nweather-graph-' + name + '-container')
		.append($('<div id="nweather-graph-' + name + '-loading" class="nweather-graph-loading"></div>')
			.append('<img src="' + nweather_plugin_url + 'images/ajax-loader.gif" />')
		)
		.append($('<div id="nweather-graph-' + name + '" class="nweather-graph"></div>'));

	var dycolors = new Array();
	dycolors[0] = '#7abf34';
	var dylabels = new Array();
	dylabels[0] = nweather_plugin_timelabel;
	dylabels[1] = nweather_plugin_valuelabel;

	if (nweather_plugin_dygraphs[name])
		nweather_plugin_dygraphs[name].destroy();

	nweather_plugin_dygraphs[name] = new Dygraph(
		document.getElementById('nweather-graph-' + name),
		nweather_plugin_url + 'getcsv.php?c=' + context + '&d=' + name + '&i=' + interval,
		{
			ylabel: label,
			labels: dylabels,
			colors: dycolors,
			connectSeparatedPoints: true,
			showRangeSelector: true,
			drawCallback: function() { $('#nweather-graph-' + name + '-loading').remove(); }
		});
}

function nweather_closegraph(name) {
	nweather_plugin_dygraphs[name].destroy();
	nweather_plugin_dygraphs[name] = null;
	$('#nweather-graph-' + name).remove();
	$('#nweather-graph-' + name + '-container .nweather-graph-openclosearrow').html('▸');
}

function nweather_togglegraph(context, name, label, interval) {
	if ($('#nweather-graph-' + name + '-container').hasClass('closed')) {
		nweather_opengraph(context, name, label, interval);
		$('#nweather-graph-' + name + '-container').switchClass('closed', 'opened');
	} else {
		nweather_closegraph(name);
		$('#nweather-graph-' + name + '-container').switchClass('opened', 'closed');
	}
}
