<?php
$graph_options = array(
	'timeline' => '{
						series: {
							lines: { show: true, fill: true },
							points: { show: true }
						},
						xaxis: {
							mode: "time",
							timeformat: "%m/%y",
							minTickSize: [1, "month"]
						},
						grid: {
							mouseActiveRadius: 50,
							borderWidth: 0,
							hoverable: true,
							clickable: true,
							autoHighlight: true
						},
						legend: {
							position: "nw"
						}
					}',
	'type' => ' {
					series: {
						pie: {
							show: true,
							radius: 1,
							innerRadius: 0.3,
							label: {
								show: true,
								radius: 2/3,
								formatter: function(label, series) { return \'<div style="font-size:9px;text-align:center;color:#fff">\'+label+\'<br />\'+Math.round(series.percent)+\'%</div>\'; }
							},
							combine: {
								color: "#999",
								threshold: 0.01
							}
						}
					},
					legend: {
						show: false
					}
				}',
	'keywords' => ' {
					series: {
						bars: {
							horizontal: true,
							barWidth: 0.8,
							show: true,
							label: {
								show: true,
							}
						}
					},
					yaxis: {
						transform: function( v ) { return -v; },
						inverseTransform: function( v ) { return -v; },
						ticks: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49],
						tickFormatter: function ( val, axis ) { return "<div style=\'margin-top: 0.5em;\'>" + kw_ticks[ val ] + "</div>"; }
					},
					xaxis: {
						min: 0
					},
					grid: {
						borderWidth: 0,
						markings: 0
					},
					legend: {
						show: false
					}
				}',
	'readability' => ' {
					series: {
						pie: {
							show: true,
							radius: 1,
							innerRadius: 0.3,
							label: {
								show: true,
								radius: 2/3,
								formatter: function(label, series) { return \'<div style="font-size:9px;text-align:center;color:#fff">\'+label+\'<br />\'+Math.round(series.percent)+\'%</div>\'; }
							},
							combine: {
								color: "#999"
							}
						}
					},
					legend: {
						show: false
					}
				}'
	);

/* EOF */
