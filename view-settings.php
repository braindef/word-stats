<?php
		echo '
	<div class="wrap">
		<h2>' , __( 'Word Stats Options', 'word-stats' ), '</h2>
		<form method="post" action="options.php">
			 ', settings_fields( 'word-stats-settings-group' ), '

			<h3>', __( 'Diagnostics', 'word-stats' ), '</h3>
			<p>
				<input style="width: 70px; text-align:right;" type="number" min="0" max="90000" name="word_stats_diagnostic_too_short" value="', intval( $opt_diagnostic_thresholds[ 'too_short' ] ), '" />
				<label for="word_stats_diagnostic_too_short">', __( 'Posts below this word count will be flagged as too short.', 'word-stats' ), '</label>
			</p>
			<p>
				<input style="width: 70px; text-align:right;" type="number" min="140" max="90000" name="word_stats_diagnostic_too_long" value="', intval( $opt_diagnostic_thresholds[ 'too_long' ] ), '" />
				<label for="word_stats_diagnostic_too_long">', __( 'Posts above this word count will be flagged as too long.', 'word-stats' ), '</label>
			</p>
			<p>
				<input style="width: 30px; text-align:right;" type="number" min="1" max="150" name="word_stats_diagnostic_too_difficult" value="', intval( $opt_diagnostic_thresholds[ 'too_difficult' ] ), '" />
				<label for="word_stats_diagnostic_too_difficult">', __( 'Posts above this average readability level will be flagged as too difficult.', 'word-stats' ), '</label>
			</p>
			<p>
				<input style="width: 30px; text-align:right;" type="number" min="0" max="150" name="word_stats_diagnostic_too_simple" value="', intval( $opt_diagnostic_thresholds[ 'too_simple' ] ), '" />
				<label for="word_stats_diagnostic_too_simple">', __( 'Posts below this average readability level will be flagged as too simple.', 'word-stats' ), '</label>
			</p>
			<p>
				<input style="width: 30px; text-align:right;" type="number"  min="0" max="50" name="word_stats_diagnostic_no_keywords" value="', intval( $opt_diagnostic_thresholds[ 'no_keywords' ] ), '" />
				<label for="word_stats_diagnostic_no_keywords">', __( 'Posts without any keyword count greater than this value will be flagged as having no relevant keywords.', 'word-stats' ), '</label>
			</p>
			<p>
				<input style="width: 30px; text-align:right;" type="number"  min="1" max="50" name="word_stats_diagnostic_spammed_keywords" value="', intval( $opt_diagnostic_thresholds[ 'spammed_keywords' ] ), '" />
				<label for="word_stats_diagnostic_spammed_keywords">', __( 'Posts with any keyword count above this value will be flagged for keyword spam.', 'word-stats' ), '</label>
			</p>


 			<h3>', __( 'Readability', 'word-stats' ), '</h3>
			<p>
				<input type="hidden" name="word_stats_RI_column" value="0" />
				<input type="checkbox" name="word_stats_RI_column" value="1" '; if ( $opt_RI_column ) { echo 'checked="checked"'; } echo ' /> ',
				__( 'Display aggregate readability column in the manage posts list.', 'word-stats' ), '
			</p>
			<p>
				<input type="hidden" name="word_stats_show_keywords" value="0" />
				<input type="checkbox" name="word_stats_show_keywords" value="1" '; if ( $opt_show_keywords ) { echo 'checked="checked"'; } echo ' /> ',
				__( 'Display live keyword count.', 'word-stats' ), '
			</p>

			<h3>', __( 'Total word counts', 'word-stats' ), '</h3>
			<p>
				<input type="hidden" name="word_stats_totals" value="0" />
				<input type="checkbox" name="word_stats_totals" value="1" ';  if ( $opt_totals ) { echo 'checked="checked"'; } echo ' /> ',
				__( 'Enable total word counts (dashboard, widget and shortcode).', 'word-stats' ), ' ',
				__( 'This may slow down post saving in large blogs.', 'word-stats' ), '
			</p>
			<p>
				<input type="hidden" name="word_stats_count_unpublished" value="0" />
				<input type="checkbox" name="word_stats_count_unpublished" value="1" ';  if ( $opt_count_unpublished ) { echo 'checked="checked"'; } echo ' /> ',
				__( 'Count words from drafts and posts pending review.', 'word-stats' ),  '
			</p>

			<h3>', __( 'Live stats', 'word-stats' ), '</h3>
			<p>
				<input type="hidden" name="word_stats_replace_word_count" value="0" />
				<input type="checkbox" name="word_stats_replace_word_count" value="1" '; if ( $opt_replace_wc ) { echo 'checked="checked"';  } echo ' /> ',
				__( 'Replace WordPress live word count with Word Stats word count.', 'word-stats' ), '
			</p>
			<p>
				<input type="hidden" name="word_stats_averages" value="0" />
				<input type="checkbox" name="word_stats_averages" value="1" '; if ( $opt_averages ) { echo 'checked="checked"'; } echo ' /> ',
				__( 'Display live character/word/sentence averages.', 'word-stats' ), '
			</p>

			<h3>', __( 'Keywords', 'word-stats' ), '</h3>
			<p>
				<input type="hidden" name="word_stats_add_tags" value="0" />
				<input type="checkbox" name="word_stats_add_tags" value="1" '; if ( $opt_add_tags ) { echo 'checked="checked"'; } echo ' /> ',
				__( 'Add the last saved tags to the live keyword count.', 'word-stats' ), '
			</p>

			<h4 style="padding: 0;margin: 0;">Ignore these keywords:</h4>
			<p>
				', sprintf( __( 'One %1$sregular expression%2$s per line, without slashes.', 'word-stats' ), '<a href="https://developer.mozilla.org/en/JavaScript/Guide/Regular_Expressions">', '</a>' ),  '<br />
				 <small><em>', __( 'Example: ^apples?$ = good, /^apples?$/ = bad.', 'word-stats' ), '</em></small><br />


<div style="float: left; margin-right: 20px;">
				<textarea name="word_stats_ignore_keywords" cols="40" rows="25">', esc_attr( strip_tags( $opt_ignore_keywords ) ) ,'</textarea></div>

				<div style="float: left;">

				<strong>', __( 'Writing basic regular expressions:', 'word-stats' ), '</strong><br /><br /><ul><li>',
				__( '^ matches the beggining of the word.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^where" matches "wherever" but not "anywhere".', 'word-stats' ), '</em></small></li><li>',
				__( '$ matches the end of the word.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "where$" matches "anywhere" but not "wherever".', 'word-stats' ), '</em></small></li><li>',
				__( '^keyword$ matches only the whole keyword.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^where$" matches "where" but not "anywhere" or "wherever".', 'word-stats' ), '</em></small></li><li>',
				__( '? matches the previous character zero or one time.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^apples?$" matches "apple" and "apples".', 'word-stats' ), '</em></small></li><li>',
				__( '* matches the previous character zero or more times.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^10*$" matches "1", "10", "1000000", etc.', 'word-stats' ), '</em></small></li><li>',
				__( '+ matches the previous character one or more times.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^10+$" matches "10", "1000000", etc., but not "1".', 'word-stats' ), '</em></small></li><li>',
				__( '[] matches any of the characters between the brackets.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^take[ns]?$" matches "take", "taken" and "takes".', 'word-stats' ), '</em></small></li></ul>',

				'</div><br style="clear:both;">',

			'</p>
			<p class="submit">
				<input type="submit" class="button-primary" value="' ,__( 'Save Changes' ), '" />
			</p>
		</form>
		<div id="ws-feedback-links" style="font-size: 16px; clear:both;"><img style="float:left; margin-top: -2px; margin-right: 4px;" src="', plugins_url(), '/word-stats/img/pin-blue.png" /> Feedback, questions, bugs? Send them to the <a href="http://wordpress.org/tags/word-stats">plugin support forum</a> or <a href="mailto:email@franontanaya.com?subject=Word Stats support">email</a> the author.</div>
	</div>';

?>
