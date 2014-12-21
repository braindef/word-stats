<?php

	if ( !WS_CURRENT_VERSION ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

	echo '<!--[if lte IE 8]>';

	$src = plugins_url( WS_FOLDER . '/js/excanvas.min.js' );
	echo '
		<script type="text/javascript" src="' , $src, '"></script>
		<![endif]-->';
	# Loading custom jquery-ui seems to fix a problem loading datepicker with the built-in jquery-ui provided with WordPress
	$scripts = array(
		plugins_url( WS_FOLDER . '/js/ui/jquery.ui.1.9.2.js' ),
		plugins_url( WS_FOLDER . '/js/ui/jquery.ui.datepicker.min.js' ),
		plugins_url( WS_FOLDER . '/js/flot/jquery.flot.js' ),
		plugins_url( WS_FOLDER . '/js/flot/jquery.flot.resize.js' ),
		plugins_url( WS_FOLDER . '/js/flot/jquery.flot.pie.js' )
	);
	foreach ( $scripts as $script ) { echo '<script type="text/javascript" src="' , $script, '"></script>', "\n"; }

	# Hide the graphs if javascript is off
	echo '
	<noscript>
		<style type="text/css">#ws-graph-wrapper { display: none; }</style>
		<div class="updated inline fade"><p>
			', __( 'JavaScript is disabled. Enable it to load the graphs.', 'word-stats' ),
		'</p></div><br />
	</noscript>
	<div class="wrap ws-wrap"><h2></h2>'; # Important: The h2 is used by admin_notices to find where to insert the messages.

	$i = 0;
	$author_graph = ( $_GET[ 'author-tab' ] ) ? intval( $_GET[ 'author-tab' ] ) : $user_ID;

	/*
		Links to author tabs (starting with an option for all authors) and date range selector.
	*/
	echo	'
	<div id="ws-forms-wrapper">
		<form id="authors-form" name="select-author" action="index.php" method="get">
			<input type="hidden" name="page" value="word-stats-graphs" />',
			__( 'View author:', 'word-stats' ),
			' <select name="author-tab" id="authors-list">';
				foreach ( $report[ 'author_count' ] as $id=>$post_type ) {
					if ( $id == -1 ) {
						# All authors option. This data is loaded into $report[ 'author_count_total' ][ -1 ], etc.
			 			echo '<option class="author-graph-option" value="-1"', ( $author_graph == '-1' ) ? ' selected="selected" ' : '', '>', __( 'All authors', 'word-stats' ), '</option>';
					} else {
						$this_author = get_userdata( $id );
					 	echo '<option class="author-graph-option" value="', $id, '"', ( $author_graph == $id ) ? ' selected="selected" ' : '', '>', $this_author->nickname, '</option>';
				 	}
				}
			echo '</select>
			', __( 'Period:', 'word-stats' ), ' <input type="text" name="period-start" id="period-start" value="', $period_start, '" /> - <input type="text" name="period-end" id="period-end" value="', $period_end, '" />
			<input type="checkbox" id="view-all" name="view-all"', ( $_GET[ 'view-all' ] ) ? ' checked="checked" ' : '', ' /> ', __( 'all time', 'word-stats' ), '
			<input id="ws-period-submit" type="submit" name="ws-submit" value="', __( 'View', 'word-stats' ), '" />
		</form>
		<script type="text/javascript">
			jQuery("#authors-list").change( function() { jQuery( "#authors-form").submit(); } );
			jQuery("#period-start").datepicker( { dateFormat: "yy-mm-dd" } );
			jQuery("#period-end").datepicker( { dateFormat: "yy-mm-dd" } );
		</script>';
	include 'donate.php';
	echo '</div>';

	/*
		Display stats for the currently selected author
	*/
	echo '<br style="clear:both" />';
	echo
	'<div id="ws-graph-wrapper">
		<div id="ws-headlines">
			<div style="float:left;">
				<h2 id="ws-total">', number_format( $report[ 'author_count_total' ][ $author_graph ] ), '</h2>
				<p id="ws-total-period">',
				 sprintf( __( '%1$swords%2$s between %3$s and %4$s', 'word-stats' ), '<strong>', '</strong> ', $period_start, $period_end ), '</p>
			</div>
			<div id="ws-meters">
				<table>
					<tr><td  class="pt-meter pt-meter-label">', __( 'Posts', 'word-stats' ), '</td><td class="pt-meter" id="pt-meter-post"></td></tr>
					<tr><td class="pt-meter pt-meter-label">', __( 'Pages', 'word-stats' ), '</td><td class="pt-meter" id="pt-meter-page"></td></tr>
					<tr><td class="pt-meter pt-meter-label">', __( 'Custom', 'word-stats' ), '</td><td class="pt-meter" id="pt-meter-custom"></td></tr>
					<tr><td class="pt-meter pt-meter-label">', __( 'All', 'word-stats' ), ' </td><td class="pt-meter" id="pt-meter-all"></td></tr>
				</table>
			</div>
			<br style="clear:both" />

			<div id="ws-graph-index-wrap">
				<div id="ws-graph-index-pc" title="', __( 'Readability level', 'word-stats' ), '"></div>
			</div>
			<div id="ws-graph-total-wrap">
				<div id="ws-graph-total-pc"  title="', __( 'Share of total words', 'word-stats' ), '"></div>
			</div>

		</div>

		<div id="ws-graph-keywords-wrap">
			<h3 class="ws-header ws-header-keywords">', __( 'Keywords', 'word-stats') . '</h3>
			<div id="ws-graph-keywords"></div>
		</div>

		<br style="clear:both" />
		<div id="ws-graph-timeline" width="258" height="390"></div>

		<br style="clear:both;" />';

	# Timeline tooltip
	echo
		'<script type="text/javascript" src="', plugins_url( WS_FOLDER . '/js/timeline-tooltip.js' ), '"></script>', "\n",
		'<script type="text/javascript">', "\n";

	# Words per Month
	$series = '[ '; $z = 0;
	if ( count( $report[ 'author_count' ][ $author_graph ] ) ) {
		foreach ( $report[ 'author_count' ][ $author_graph ] as $type=>$months ) {
			if ( $months ) {
				if ( $z ) { $series .= ', '; }
				$z++;
				$series .= '{ label: "' . $type . '", data: d' . $z . ' }';
				$comma = false;
				foreach ( $months as $month=>$count ) {
					$total_per_type[ $type ] += $count;
					$month .= '-01';
					$month = strtotime( $month ) * 1000;
					# ToDo: Zero months with no words
					if ( $comma ) { $data[ $z ] .= ', '; }
					$data[ $z ] .= "[ $month, $count ]"; # Add a data point to the series
					$comma = true;
				}
				echo "var d$z = [",  $data[ $z ], "];\n"; # Create the data array for each post type
			}
		}
	} else {
		$series .= '{ label: "' . __( 'No data', 'word-stats' ). '", data: 100, color: "#666" }';
	}
	$series .= ' ]';
	echo "jQuery.plot(jQuery(\"#ws-graph-timeline\"), $series, ", $graph_options[ 'timeline' ], ");\n";

	# Percentage of each post type. We counted the totals in the loop for the main chart.
	$series = '[ ';
	$z = 0;
	$comma = false;
	foreach ( $report[ 'type_count' ] as $type=>$count ) {
		$total_sum += $count;
	}
	if ( count( $total_per_type ) ) {
		foreach ( $total_per_type as $type=>$count ) {
			$z++;
			if ( $comma ) { $series .= ', '; }
			$series .= '{ label: "' . $type . '", data: ' . $count . ' }';
			$comma = true;
		}
	} else {
		$series .= '{ label: "' . __( 'No data', 'word-stats' ) . '", data: 100, color: "#666" }';
	}

	$series .= ' ]';
	echo "jQuery.plot(jQuery(\"#ws-graph-total-pc\"), $series,", $graph_options[ 'type' ], " );\n";

	$series = '[ ';
	$z = 0;
	$comma = false;
	if ( array_sum( $report[ 'totals_readability' ] ) ) {
		foreach ( $report[ 'totals_readability' ] as $index=>$count ) {
				$z++;
				if ( $comma ) { $series .= ', '; }
				switch( $index ) {
					case 0: $label = __( 'Basic', 'word-stats' ); $color = '#19c'; break;
					case 1: $label = __( 'Intermediate', 'word-stats' ); $color = '#1c9'; break;
					case 2: $label = __( 'Advanced', 'word-stats' ); $color = '#c36'; break;
				}
				$series .= '{ label: "' . $label . '", data: ' . $count . ', color: \'' . $color . '\' }';
				$comma = true;
		}
	} else {
		$series .= '{ label: "' . __( 'No data', 'word-stats' ) . '", data: 100, color: "#666" }';
	}
	$series .= ' ]';
	echo "jQuery.plot(jQuery(\"#ws-graph-index-pc\"), $series,", $graph_options[ 'readability' ], " );\n";


	# Keywords.
	$series = '[ { label: "keywords", data: kw1, color: \'#38c\' } ]';
	$comma = false;
	$z = 0;
	if( count( $report[ 'total_keywords' ] ) ) {
		foreach ( $report[ 'total_keywords' ] as $key=>$value ) {
				if ( $comma ) { $kw_data .= ', '; $kw_ticks .= ', '; $var_kw_ticks .= ', '; }
				$kw_data .= "[  $value, $z ]";
				$var_kw_ticks .= '"' . str_replace( '"', '\"', $key ) . '"'; # Double check for unescaped quotes
				$comma = true;
				$z++;
				if ( $z == 20 ) { break; }
		}
	}
	# Fill the blanks
	if ( $z < 20 ) {
		for( $i = 1; 20 - $z; $i++ ) {
			if ( $comma ) { $kw_data .= ', '; $kw_ticks .= ', '; $var_kw_ticks .= ', '; }
			$kw_data .= "[  0, $z ]";
			$var_kw_ticks .= '""';
			$comma = true;
			$z++;
		}
	}

	# 1. Create the data array for each post type. 2. Create the data array for each post type (ticks)
	echo "
		var kw1 = [",  $kw_data, "];\n
		var kw_ticks = new Array(",  $var_kw_ticks, ");\n
		jQuery.plot(jQuery(\"#ws-graph-keywords\"), $series,", $graph_options[ 'keywords' ], " );\n;";

	# Post type counts
	$bar_max_width = 125;
	$total_posts =  $report[ 'type_count' ][ 'post' ]; $total_pages =  $report[ 'type_count' ][ 'page' ]; $total_custom = $report[ 'type_count' ][ 'custom' ];
	$total_all_types = $total_posts + $total_pages + $total_custom;

	if ( !$total_all_types ) { $total_all_types = 1; }
	$meter_post_width = intval( $bar_max_width * ( $total_posts / $total_all_types ) + 1 );
	$meter_page_width = 	intval( $bar_max_width * ( $total_pages / $total_all_types ) + 1);
	$meter_custom_width = intval( $bar_max_width * ( $total_custom / $total_all_types ) + 1);
	$meter_all_width = intval( $bar_max_width + 1 );
	echo '
			jQuery("#pt-meter-post").html("<div class=\'pt-meter-bar pt-meter-post-bar\' style=\'width:', $meter_post_width, 'px\'></div> ', $total_posts, '");
			jQuery("#pt-meter-page").html("<div class=\'pt-meter-bar pt-meter-page-bar\' style=\'width:', $meter_page_width, 'px\'></div> ', $total_pages, '");
			jQuery("#pt-meter-custom").html("<div class=\'pt-meter-bar pt-meter-custom-bar\'  style=\'width:', $meter_custom_width, 'px\'></div> ', $total_custom, '");
			jQuery("#pt-meter-all").html("<div class=\'pt-meter-bar pt-meter-all-bar\' style=\'width:', $meter_all_width, 'px\'></div> ', $total_posts + $total_pages + $total_custom, '");
		</script>
	</div>
	<div id="ws-diagnostics-wrap">
		<h3 class="ws-header">', __( 'Diagnostics', 'word-stats' ), '</h3>
			<div id="ws-tables">';

	echo $diagnostic_tables;

	echo '	</div>';
	include 'view-feedback-links.php';
	echo '</div>
		<br style="clear:both;"></div>'; # End wrap

/* EOF */
