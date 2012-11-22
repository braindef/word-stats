<?php
	if( !WS_CURRENT_VERSION ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

	$notify_url = get_admin_url( '', '', 'admin' ) . 'index.php?page=word-stats-graphs&word-stats-action=donation';
	echo '
		<form id="ws-donate-form" action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="MGBHZ3ZMZS3W8">
		<input  type="hidden" name="notify_url" value="', $notify_url, '">
		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypalobjects.com/es_ES/i/scr/pixel.gif" width="1" height="1">
		</form>
	';

/* EOF */
