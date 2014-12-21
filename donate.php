<?php
	if( !WS_CURRENT_VERSION ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

	$notify_url = get_admin_url( '', '', 'admin' ) . 'index.php?page=word-stats-graphs&word-stats-action=donation';
	echo '
		<a style="float:right; font-size: 10px;" href="http://es.linkedin.com/in/franontanaya/en">Add me on LinkedIn »</a>
	';

/* EOF */
