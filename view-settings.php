<?php

	if ( !WS_CURRENT_VERSION ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

	echo '
	<div class="wrap">
		<h2>' , __( 'Word Stats Options', 'word-stats' ), '</h2>
		<form method="post" action="options.php">';
			/*
				We don't bother here with adding the sections and fields with the Settings API,
				since it's a lot clunkier than using our own callback.
				options_field( $name, $type, $value, $default, $checked, $min, $max, $width, $align, $label )
			*/
			echo settings_fields( 'word-stats-settings-group' ); # Echo WP's built in hidden fields
			Word_Stats_Admin::options_section( __( 'Diagnostics', 'word-stats' ) );
			$width = '100px'; $align = 'right';
			Word_Stats_Admin::options_field( 'word_stats_options[diagnostic_too_short]', 'number', intval( $word_stats_options[ 'diagnostic_too_short' ] ), false, false, '0', '90000', $width, $align, __( 'Posts below this word count will be flagged as too short.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[diagnostic_too_long]', 'number', intval( $word_stats_options[ 'diagnostic_too_long' ] ), false, false, '140', '90000', $width, $align, __( 'Posts above this word count will be flagged as too long.', 'word-stats' ) );
			$width = '70px';
			Word_Stats_Admin::options_field( 'word_stats_options[diagnostic_too_difficult]', 'number', intval( $word_stats_options[ 'diagnostic_too_difficult' ] ), false, false, '1', '150', $width, $align, __( 'Posts above this average readability level will be flagged as too difficult.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[diagnostic_too_simple]', 'number', intval( $word_stats_options[ 'diagnostic_too_simple' ] ), false, false, '0', '150', $width, $align, __( 'Posts below this average readability level will be flagged as too simple.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[diagnostic_no_keywords]', 'number', intval( $word_stats_options[ 'diagnostic_no_keywords' ] ), false, false, '0', '50', $width, $align, __( 'Posts without any keyword count per 1000 words greater than this value, and not flagged already as too short, will be diagnosed as having no relevant keywords.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[diagnostic_spammed_keywords]', 'number', intval( $word_stats_options[ 'diagnostic_spammed_keywords' ] ), false, false, '1', '50', $width, $align, __( 'Posts with any keyword count per 1000 words greater than this value will be diagnosed as having keyword spam.', 'word-stats' ) );
echo '<br /><br />';
			Word_Stats_Admin::options_section( __( 'Readability', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[RI_column]', 'checkbox', '1', '0', $word_stats_options[ 'RI_column' ], false, false, false, false, __( 'Display aggregate readability column in the manage posts list.', 'word-stats' ) );
echo '<br /><br />';
			Word_Stats_Admin::options_section( __( 'Total word counts', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[totals]', 'checkbox', '1', '0', $word_stats_options[ 'totals' ], false, false, false, false, __( 'Display total word counts in the dashboard.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[count_unpublished]', 'checkbox', '1', '0', $word_stats_options[ 'count_unpublished' ], false, false, false, false, __( 'Count words from drafts and posts pending review.', 'word-stats' ) );
echo '<br /><br />';
			Word_Stats_Admin::options_section( __( 'Live stats', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[replace_word_count]', 'checkbox', '1', '0', $word_stats_options[ 'replace_word_count' ], false, false, false, false, __( 'Replace WordPress live word count with Word Stats word count.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[live_averages]', 'checkbox', '1', '0', $word_stats_options[ 'live_averages' ], false, false, false, false, __( 'Display live character/word/sentence averages.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[live_keywords]', 'checkbox', '1', '0', $word_stats_options[ 'live_keywords' ], false, false, false, false, __( 'Display live keyword count.', 'word-stats' ) );
			Word_Stats_Admin::options_field( 'word_stats_options[add_tags]', 'checkbox', '1', '0', $word_stats_options[ 'add_tags' ], false, false, false, false, __( 'Add the last saved tags to the live keyword count.', 'word-stats' ) );
echo '<br /><br />';
			Word_Stats_Admin::options_section( __( 'Ignored keywords', 'word-stats' ) );
			echo '<p>',
				 __( 'Ignore common words for:', 'word-stats' ), '
				<select name="word_stats_options[ignore_common]" />
					<option value="en" '; if ( $word_stats_options[ 'ignore_common' ] == 'en' ) { echo 'selected="selected" '; } echo ' />', __( 'English', 'word-stats' ), '</option>
					<option value="es" '; if ( $word_stats_options[ 'ignore_common' ] == 'es' ) { echo 'selected="selected" '; } echo ' />', __( 'Spanish', 'word-stats' ), '</option>
					<option value="0" '; if ( $word_stats_options[ 'ignore_common' ] == '0' ) { echo 'selected="selected" '; } echo ' />', __( '(disabled)', 'word-stats' ), '</option>
				</select>
			</p>
			<h4>', __( 'Ignore also these keywords:', 'word-stats' ), '</h4>
			<p>
				', sprintf( __( 'One %1$sregular expression%2$s per line, without slashes.', 'word-stats' ), '<a href="https://developer.mozilla.org/en/JavaScript/Guide/Regular_Expressions">', '</a>' ),  '<br />
				 <small><em>', __( 'Example: ^apples?$ = good, /^apples?$/ = bad.', 'word-stats' ), '</em></small><br />
				 <small><em>', __( 'Note: A long ignore list can impact performance.', 'word-stats' ), '</em></small><br />
<div style="float: left; margin-right: 20px;">
				<textarea name="word_stats_options[ignore_keywords]" cols="40" rows="23">', esc_attr( strip_tags( $word_stats_options[ 'ignore_keywords' ] ) ) ,'</textarea></div>
				<div style="float: left;">
				<strong>', __( 'Writing basic regular expressions:', 'word-stats' ), '</strong><br /><ul><li>',
				__( '^ matches the beggining of the word.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^where" matches "wherever" but not "anywhere".', 'word-stats' ), '</em></small></li><li>',
				__( '$ matches the end of the word.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "where$" matches "anywhere" but not "wherever".', 'word-stats' ), '</em></small></li><li>',
				__( '^keyword$ matches only the whole keyword.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^where$" matches "where" but not "anywhere" or "wherever".', 'word-stats' ), '</em></small></li><li>',
				__( '? matches the previous character zero or one time.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^apples?$" matches "apple" and "apples".', 'word-stats' ), '</em></small></li><li>',
				__( '* matches the previous character zero or more times.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^10*$" matches "1", "10", "1000000", etc.', 'word-stats' ), '</em></small></li><li>',
				__( '+ matches the previous character one or more times.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^10+$" matches "10", "1000000", etc., but not "1".', 'word-stats' ), '</em></small></li><li>',
				__( '() matches groups of characters.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^silver(light)?$" matches "silver" and "silverlight".', 'word-stats' ), '</em></small></li><li>',
				__( '| matches either side of it. Use it instead of [] for unicode characters ', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^qu(e|é)$" matches "que" and "qué".', 'word-stats' ), '</em></small></li><li>',
				__( '[] matches any of the characters between the brackets.', 'word-stats' ), ' <br /><small><em>', __( 'Example: "^take[ns]?$" matches "take", "taken" and "takes".', 'word-stats' ), '</em></small></li></ul>',
				'</div><br style="clear:both;">',
			'</p>
			<p class="submit">
				<input type="submit" class="button-primary" value="' ,__( 'Save Changes' ), '" />
			</p>
		</form>';

	include 'view-feedback-links.php';
	echo '</div>';

/* EOF */
