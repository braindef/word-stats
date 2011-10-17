<?php

// Don't ask if they are somehow running WP < 3.0 (get_admin_url not available).
//if( function_exists( get_admin_url() ) ) {
	$notify_url = get_admin_url( '', '', 'admin' ) . 'index.php?page=word-stats-graphs&word-stats-action=alternative';

	echo '<div id="ws-premium-overlay">
	<div id="ws-premium-dialog">
		<div id="ws-premium-dialog-text">
		<h1>', __( 'Unlock this screen', 'word-stats' ), '</h1>
		<p>', __( 'Get access to this screen and future premium features with a single contribution.', 'word-stats' ), '</p>
		<p><small>', sprintf( __( 'Problems activating the premium features? %sClick here%s', 'word-stats' ), '<a href="' . $notify_url .'">', '</a>' ), '</small></p>
		</div>
		<div id="ws-premium-dialog-button">';

		echo '
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="AJWD34TVP345S">
		<input  type="hidden" name="notify_url" value="', $notify_url, '">
		<table>
		<tr><td><input type="hidden" name="on0" value="Your score">', __( 'Your rating', 'word-stats' ), '</td></tr><tr><td><select name="os0">
			<option value="Meh, whatever">', __( 'Meh, whatever', 'word-stats' ), ' ', sprintf( __( '€%1$s.%2$s', 'word-stats' ), '2', '00' ), '</option>
			<option value="Good plugin!">', __( 'Good plugin!', 'word-stats' ), ' ', sprintf( __( '€%1$s.%2$s', 'word-stats' ), '10', '00' ), '</option>
			<option value="Awesomesauce!">', __( 'Awesomesauce!', 'word-stats' ), ' ', sprintf( __( '€%1$s.%2$s', 'word-stats' ), '30', '00' ), '</option>
		</select> </td></tr>
		</table>
		<input type="hidden" name="currency_code" value="EUR">
		<input type="image" src="https://www.paypalobjects.com/en_US/ES/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypalobjects.com/es_ES/i/scr/pixel.gif" width="1" height="1">
		</form>
		';

	echo '</div>
	<br style="clear:both;"></div></div>';
//} else {
	//echo 'ERROR';
//}

?>
