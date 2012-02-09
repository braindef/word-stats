<?php

	if( !WS_CURRENT_VERSION ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

	echo '<h4 class="ws-diagnostic-title">', $title, '</h4>';
	echo '<table class="ws-diagnostics" id="ws-diagnostic-', $id, '">';
	echo '<thead><tr>';
	foreach( $fields as $field ) {
		echo '<td class="ws-table-', strtolower( str_replace( ' ', '-', $field ) ),'">', $field, '</td>';
	}
	echo '</tr></thead>';

	$even = false;
	foreach( $rows as $row ) {
		echo '<tr', ( $even ) ? '' : ' class="ws-row-even" ', '>';
		echo $row;
		echo '</tr>';
		$even = !$even;
	}
	echo '</table>';

/* EOF */
