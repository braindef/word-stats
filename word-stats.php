<?php
/*
Plugin Name: Word Stats
Plugin URI: http://bestseller.franontanaya.com/?p=101
Description: Adds total words counts to the dashboard, keyword count to edit post page and readability levels to edit post page and posts list.
Author: Fran Ontanaya
Version: 1.4.1
Author URI: http://www.franontanaya.com

Copyright (C) 2010 Fran Ontanaya
contacto@franontanaya.com
http://bestseller.franontanaya.com/?p=101

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

Thanks to Allan Ellegaard for testing and input. 
*/

/* # Word Counts
-------------------------------------------------------------- */
// # Count words from all post types and cache the output
load_plugin_textdomain( 'word-stats', '/wp-content/plugins/word-stats/languages/', 'word-stats/languages/' );

function ws_cache_word_counts() {
	$count = 0;
	$cache = '';

	// Get the post types
	$args=array(
		'public'   => true,
	);

	$post_types = get_post_types( $args );

	foreach( $post_types as $post_type ) {
		if ( $post_type != 'attachment' && $post_type != 'nav_menu_item' && $post_type != 'revision' ) {
			$count = 0;
			$posts = get_posts( array(
				'numberposts' => -1,
				'post_type' => array( $post_type )
			));
			foreach( $posts as $post ) {
				$count += str_word_count( strip_tags( get_post_field( 'post_content', $post->ID ) ) );
			}
			$num =  number_format_i18n( $count );

			// This adds the word count for each post type to the stats portion of the Right Now box
			$text = __( 'Words', 'word-stats' ) . ' (' . $post_type . ')';
			$cache = $cache . "::opentag::{$num}::separator::{$text}::closetag::";
			$total_num += $count;
		}
	}

	$text = __( 'Total words', 'word-stats' );
	$total_num =  number_format_i18n( $total_num );
	$cache = $cache . "::totalopentag::{$total_num}::separator::{$text}::closetag::";
	update_option( 'ws-counts-cache', $cache );
	return $cache;
}

// # Output the cached word counts with the proper HTML tags
function ws_get_word_counts( $mode ) {

	if ( !get_option( 'ws-counts-cache' ) ) {
		$cached = ws_cache_word_counts();
	} else {
		$cached = get_option( 'ws-counts-cache' );
	}

	if ( $mode == 'table' ) {
		$cached = str_replace( '::opentag::', '<tr><td class="first b"><a>', $cached );
		$cached = str_replace( '::totalopentag::', '<tr><td class="first b word-stats-dash-total"><a>', $cached );
		$cached = str_replace( '::separator::', '</a></td><td class="t"><a>', $cached );
		$cached = str_replace( '::closetag::', '</a></td></tr>', $cached );
	} else {
		$cached = str_replace( '::opentag::', '<li class="word-stats-count">', $cached );
		$cached = str_replace( '::totalopentag::', '<li class="word-stats-count word-stats-list-total">', $cached );
		$cached = str_replace( '::separator::', ' ', $cached );
		$cached = str_replace( '::closetag::', '</li>', $cached );
	}
	return $cached;
}

function ws_total_word_counts() {
	echo ws_get_word_counts( 'table' );
}

// # Widget to output word counts
class widget_ws_word_counts extends WP_Widget {
	function widget_ws_word_counts() {
		// widget actual processes
		parent::WP_Widget(false, $name = __( 'Total Word Counts', 'word-stats' ), array('description' => __( 'Displays the word counts of all public post types', 'word-stats' ) ) );	
	}

	function form($instance) {
		// outputs the options form on admin
		$title = esc_attr( $instance[ 'title' ] );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'word-stats' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		return $instance;
	}

	function widget( $args, $instance ) {
		// outputs the content of the widget
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( !$title ) $title = __( 'Total Word Counts', 'word-stats' );
		$title = esc_attr( strip_tags( $title ) );
		?>
		<li class="widget word-stats-counts-widget">
			<h2 class="widgettitle word-stats-counts-title"><?php echo $title; ?></h2>
			<ul class="word-stats-counts">
			<?php echo ws_get_word_counts( 'list' ); ?>
			</ul>
		</li>
	<?php
	}
} // end class

// # Shortcode to output word counts
function ws_word_counts_sc( $atts = null, $content = null ) {
	return '<ul class="word-stats-counts">' . ws_get_word_counts( 'list' ) . '</ul>'; 
}

add_shortcode( 'wordcounts', 'ws_word_counts_sc' );

// Hook the functions
if ( get_option( 'word_stats_totals' ) || get_option( 'word_stats_totals' ) == '' ) {
	add_action( 'save_post', 'ws_cache_word_counts' );
	add_action( 'right_now_content_table_end', 'ws_total_word_counts' );
	add_action( 'widgets_init', create_function( '', 'return register_widget( "widget_ws_word_counts" );' ) );
}

/* # Live post stats
-------------------------------------------------------------- */
// # Display post legibility
function ws_readability() { ?>

	<script type="text/javascript">
		function wsRefreshStats() {
			var statusInfo = document.getElementById( 'post-status-info' );
			var allWords = document.getElementById("content").value;
			allWords = allWords.replace( /\&lt;/gi, '<' );
			allWords = allWords.replace ( /\&gt;/gi, '>' );
			allWords = allWords.replace ( /\&nbsp;/gi, ' ' );
			allWords = allWords.replace(/<[^\s][^>]*[^\s]>/g , "");
			var totalCharacters = 0;
			var totalWords = 0;
			var totalSentences = 0;
			var wordArray = '';
			var temp = '';
			if ( allWords ) {
				totalCharacters = allWords.length;	
				temp = allWords.replace( /\W/gi, "");
				totalAlphanumeric = temp.length;
				temp = '';
				temp = allWords.replace( /\!/g, '.' );
				temp = temp.replace( /\?/g, '.' );
				temp = temp.replace( /;/g, '.' );
				temp = temp.replace ( /[A-Z]\./g, 'A' ); /* no dotted acronyms, thanks */
				sentenceArray = temp.split( '.' );
				totalSentences = sentenceArray.length;
				if ( sentenceArray[ sentenceArray.length - 1 ] == '' ) { totalSentences = totalSentences - 1; }
				temp = temp.replace( /,/g, ' ' );
				wordArray = temp.split( /[\s\.]+/ );
				totalWords = wordArray.length;
				if ( wordArray[ wordArray.length - 1 ] == '' ) { totalWords = totalWords - 1; }
			}
			if ( totalWords > 0 && totalSentences > 0 ) {
				var charsPerWord = ( totalAlphanumeric / totalWords );
				charsPerWord = charsPerWord.toFixed( 0 );
				var charsPerSentence = ( totalAlphanumeric / totalSentences );
				charsPerSentence = charsPerSentence.toFixed( 0 );
				var wordsPerSentence = ( totalWords / totalSentences );
				wordsPerSentence = wordsPerSentence.toFixed( 0 );
			
				/* Automated Readability Index */
				var ARI = 4.71 * ( totalAlphanumeric / totalWords ) + 0.5 * ( totalWords / totalSentences ) - 21.43;
				var ARItext;
				ARI = ARI.toFixed( 1 ); 
				if ( ARI < 8 ) { ARItext = '<span style="color: #0c0;">' + ARI + '</span>'; }
				if ( ARI > 7.9 && ARI < 12 ) { ARItext = '<span style="color: #aa0;">' + ARI + '</span>'; }
				if ( ARI > 11.9 && ARI < 16 ) { ARItext = '<span style="color: #c60;">' + ARI + '</span>'; }
				if ( ARI > 15.9 && ARI < 20 ) { ARItext = '<span style="color: #c00;">' + ARI + '</span>'; }
				if ( ARI > 19.9 ) { ARItext = '<span style="color: #a0a;">' + ARI + '</span>'; }

				/* Coleman-Liau Index */
				var CLI = 5.88 * ( totalAlphanumeric / totalWords ) - 29.6 * ( totalSentences / totalWords ) - 15.8;
				var CLItext;
				CLI = CLI.toFixed( 1 ); 
				if ( CLI < 8 ) { CLItext = '<span style="color: #0c0;">' + CLI + '</span>'; }
				if ( CLI > 7.9 && CLI < 12 ) { CLItext = '<span style="color: #aa0;">' + CLI + '</span>'; }
				if ( CLI > 11.9 && CLI < 16 ) { CLItext = '<span style="color: #c60;">' + CLI + '</span>'; }
				if ( CLI > 15.9 && CLI < 20 ) { CLItext = '<span style="color: #c00;">' + CLI + '</span>'; }
				if ( CLI > 19.9 ) { CLItext = '<span style="color: #a0a;">' + CLI + '</span>'; }

				/* LIX */
				var LIXlongwords = 0;
				for (var i = 0; i < wordArray.length; i=i+1 ) {
					if ( wordArray[ i ].length > 6 ) { LIXlongwords = LIXlongwords + 1; }
				}
				temp = allWords.split( /[,;\.\(\:]/ );
				var LIX = totalWords / temp.length + ( LIXlongwords * 100 ) / totalWords;
				var LIXtext;
				LIX = LIX.toFixed( 1 ); 
				if ( LIX < 30 ) { LIXtext = '<span style="color: #0c0;">' + LIX + '</span>'; }
				if ( LIX > 29.9 && LIX < 40 ) { LIXtext = '<span style="color: #aa0;">' + LIX + '</span>'; }
				if ( LIX > 39.9 && LIX < 50 ) { LIXtext = '<span style="color: #c60;">' + LIX + '</span>'; }
				if ( LIX > 49.9 && LIX < 60 ) { LIXtext = '<span style="color: #c00;">' + LIX + '</span>'; }
				if ( LIX > 59.9 ) { LIXtext = '<span style="color: #a0a;">' + LIX + '</span>'; }

				temp = '';
			<?php if ( get_option( 'word_stats_show_keywords' ) || get_option( 'word_stats_show_keywords' ) == '' ) { ?>
				/* Find keywords */
				var wordHash = new Array;
				var topCount = 0;
				var ignKeywords = "<?php echo '::', str_replace( ' ', '::', get_option( 'word_stats_ignore_keywords' ) ), '::';  ?>";
				for (var i = 0; i < wordArray.length; i = i + 1) {
					if ( ignKeywords.indexOf( wordArray[i] ) == '-1' ) {
						if ( wordArray[i].length > 3 ) {
							if ( !wordHash[ wordArray[i] ] ) { wordHash[ wordArray[i] ] = 0; }
							wordHash[ wordArray[i] ] = wordHash[ wordArray[i] ] + 1;
							if ( wordHash[ wordArray[i] ] > topCount ) { topCount = wordHash[ wordArray[i] ]; }
						}
					}
				}

				/* Relevant keywords must have at least three appareances and half the appareances of the top keyword */
				for ( var j in wordHash ) {
					if ( wordHash[j] >= topCount/5 && wordHash[j] > 2 ) {
						if ( wordHash[j] == topCount ) {
							temp = temp + '<span style="font-weight:bold; color:#0c0;">' + j + ' (' + wordHash[j] + ')</span> ';
						} else if ( wordHash[j] > topCount / 1.5 ) {
							temp = temp + '<span style="color:#3c0;">' + j + ' (' + wordHash[j] + ')</span> ';
						} else {
							temp = temp + j + ' (' + wordHash[j] + ') ';
						}
					}
				}
				if ( temp == '' ) { 
					temp = "<br><strong><?php _e( 'Keywords:', 'word-stats' ); ?></strong><br><?php _e( 'No relevant keywords.', 'word-stats' ); ?>"; 
				} else {
					temp = "<br><strong><?php _e( 'Keywords:', 'word-stats' ); ?></strong><br>" + temp;
				} 
			<?php } ?>

				if ( statusInfo.innerHTML.indexOf( 'edit-word-stats' ) < 1 ) { 
					statusInfo.innerHTML = statusInfo.innerHTML + "<tbody><tr><td id='edit-word-stats' style='padding-left:7px; padding-bottom:4px;' colspan='2'><strong><?php _e( 'Readability:', 'word-stats' ); ?></strong><br><a title='Automated Readability Index'>ARI<a>: " + ARItext + "&nbsp; <a title='Coleman-Liau Index'>CLI</a>: " + CLItext + "&nbsp; <a title='Läsbarhetsindex'>LIX</a>: " + LIXtext + "<br>" + totalCharacters + " <?php _e( 'characters', 'word-stats' ); ?>; " + totalAlphanumeric + " <?php _e( 'alphanumeric characters', 'word-stats' ); ?>; " + totalWords + " <?php _e( 'words', 'word-stats' ); ?>; " + totalSentences + " <?php _e( 'sentences', 'word-stats' ); ?>.<br>" + charsPerWord + " <?php _e( 'characters per word', 'word-stats' ); ?>; " + charsPerSentence + " <?php _e( 'characters per sentence', 'word-stats' ); ?>; " + wordsPerSentence + " <?php _e( 'words per sentence', 'word-stats' ); ?>." + temp + "</td></tr></tbody>";
				} else {
				 document.getElementById( "edit-word-stats").innerHTML = "<strong><?php _e( 'Readability:', 'word-stats' ); ?></strong><br><a title='Automated Readability Index'>ARI<a>: " + ARItext + "&nbsp; <a title='Coleman-Liau Index'>CLI</a>: " + CLItext + "&nbsp; <a title='Läsbarhetsindex'>LIX</a>: " + LIXtext + "<br>" + totalCharacters + " <?php _e( 'characters', 'word-stats' ); ?>; " + totalAlphanumeric + " <?php _e( 'alphanumeric characters', 'word-stats' ); ?>; "+ totalWords + " <?php _e( 'words', 'word-stats' ); ?>; " + totalSentences + " <?php _e( 'sentences', 'word-stats' ); ?>.<br>" + charsPerWord + " <?php _e( 'characters per word', 'word-stats' ); ?>; " + charsPerSentence + " <?php _e( 'characters per sentence', 'word-stats' ); ?>; " + wordsPerSentence + " <?php _e( 'words per sentence', 'word-stats' ); ?>." + temp; 
				}
			}
		}

		var statsTime = setInterval( "wsRefreshStats()", 10000 );
		wsRefreshStats();

	</script>

	<?php
}

// Load only when editing a post
if ( $_GET[ 'action' ] == 'edit' || !strpos( $_SERVER['SCRIPT_FILENAME'], 'post-new.php' ) === false ) {
	add_action('admin_footer', 'ws_readability');
}

/* # Static post stats
-------------------------------------------------------------- */
// Calculate stats the PHP way and store them.
function ws_cache_readability_stats( $id = null ) {
	if ( !$id ) {
		global $post;
		if ( !$post->ID ) {
			return null;
		}
		$id = $post->ID;
	}
	$thepost = get_post( $id );

	// Strip tags
	$allWords = strip_tags( $thepost->post_content );

	// Count
	if ( $allWords ) {
		$totalCharacters = strlen( $allWords );
		$temp = preg_replace( '/\W/', '', $allWords );
		$totalAlphanumeric = strlen( $temp );
		$temp = str_replace( '!', '.', $allWords );
		$temp = str_replace( '?', '.', $temp );
		$temp = str_replace( ';', '.', $temp );
		$temp = str_replace( '&nbsp;', ' ', $temp );
		$temp = preg_replace( '/[A-Z]\./', 'A', $temp );
		$sentenceArray = explode( '.', $temp );
		$totalSentences = count( $sentenceArray );
		if ( $sentenceArray[ $totalSentences - 1 ] == '' ) { $totalSentences = $totalSentences - 1; }
		$wordArray = preg_split( '/[\s\.]+/', $temp );
		$totalWords = count( $wordArray );
		if ( $wordArray[ $totalWords - 1 ] == '' ) { $totalWords = $totalWords - 1; }

		// Do the calcs if we aren't going to divide by zero
		if ( $totalWords > 0 && $totalSentences > 0 ) {
			$charsPerWord = intval( $totalAlphanumeric / $totalWords );
			$charsPerSentence = intval( $totalAlphanumeric / $totalSentences );
			$wordsPerSentence = intval( $totalWords / $totalSentences );

			// Automated Readability Index
			$ARI = round( 4.71 * ( $totalAlphanumeric / $totalWords ) + 0.5 * ( $totalWords / $totalSentences ) - 21.43, 1);

			// Coleman-Liau Index
			$CLI = round( 5.88 * ( $totalAlphanumeric / $totalWords ) - 29.6 * ( $totalSentences / $totalWords ) - 15.8, 1);

			// LIX
			$LIXlongwords = 0;
			for ($i = 0; $i < count( $wordArray ); $i = $i + 1 ) {
				if ( strlen( $wordArray[ $i ] ) > 6 ) { $LIXlongwords++; }
			}
			$temp = preg_split( '/[,;\.\(\:]/', $allWords );
			$LIX = round( $totalWords / count( $temp ) + ( $LIXlongwords * 100 ) / $totalWords, 1) ;
		} else {
			$ARI = '0';
			$CLI = '0';
			$LIX = '0';
		}
	} else {
		$ARI = '0';
		$CLI = '0';
		$LIX = '0';
	}

	// Create/update the post meta fields for readability
	update_post_meta( $id, 'readability_ARI', $ARI );
	update_post_meta( $id, 'readability_CLI', $CLI );
	update_post_meta( $id, 'readability_LIX', $LIX );
}

add_action( 'save_post', 'ws_cache_readability_stats' );

/* # Add a column to the post management list
-------------------------------------------------------------- */
function ws_readability_column( $defaults ) {
    $defaults[ 'readability' ] = __( 'R.I.', 'word-stats' );
    return $defaults;
}

function ws_custom_column() {
	global $post;
	$ARI = get_post_meta( $post->ID, 'readability_ARI', true );
	$CLI = get_post_meta( $post->ID, 'readability_CLI', true );
	$LIX = get_post_meta( $post->ID, 'readability_LIX', true );

	if ( !$ARI ) {
		// If there is no data or the post is blank
		echo '<span style="color:#999;">--</span>';
	} else {
		// Trying to aggregate the indexes in a meaningful way.
		$r_avg = ( floatval( $ARI ) + floatval( $CLI ) + ( ( floatval( $LIX ) - 10 ) / 2 ) ) / 3;
		if ( $r_avg < 8 ) { echo '<span style="color: #0c0;">', round( $r_avg, 1 ), '</span>'; }
		if ( $r_avg > 7.9 && $r_avg < 12 ) { echo '<span style="color: #aa0;">', round( $r_avg, 1 ), '</span>'; }
		if ( $r_avg > 11.9 && $r_avg < 16 ) { echo '<span style="color: #c60;">', round( $r_avg, 1 ), '</span>'; }
		if ( $r_avg > 15.9 && $r_avg < 20 ) { echo '<span style="color: #c00;">', round( $r_avg, 1 ), '</span>'; }
		if ( $r_avg > 19.9 ) { echo '<span style="color: #a0a;">', round( $r_avg, 1 ), '</span>'; }
	}
}

// Load style for the column
function ws_admin_init() {
	wp_register_style( 'word-stats-css', plugins_url() . '/word-stats/word-stats.css' );
	wp_enqueue_style( 'word-stats-css' );
}

if ( get_option( 'word_stats_RI_Column' ) || get_option( 'word_stats_RI_Column' ) == '' ) {
	add_filter( 'manage_posts_columns', 'ws_readability_column' );
	add_action( 'manage_posts_custom_column', 'ws_custom_column' );
	add_action( 'admin_init', 'ws_admin_init' );
}

/* § Construct the options page
-------------------------------------------------------------- */
// create custom plugin settings menu
add_action('admin_menu', 'word_stats_create_menu');

function word_stats_create_menu() {
	//create new settings menu
	add_options_page( 'Word Stats Plugin Settings', 'Word Stats', 'manage_options', 'word-stats-options', 'word_stats_settings_page' );
	//call register settings function
	add_action( 'admin_init', 'register_word_stats_settings' );
}

function register_word_stats_settings() {
	//register our settings
	register_setting( 'word-stats-settings-group', 'word_stats_RI_column' );
	register_setting( 'word-stats-settings-group', 'word_stats_totals' );
	register_setting( 'word-stats-settings-group', 'word_stats_show_keywords' );
	register_setting( 'word-stats-settings-group', 'word_stats_ignore_keywords' );
}

function word_stats_settings_page() {

	// Default values
	$opt_RI_column = get_option( 'word_stats_RI_column' );
	if ( $opt_RI_column == null ) { $opt_RI_column = 1; }
	$opt_totals = get_option( 'word_stats_totals' );
	if ( $opt_totals == null ) { $opt_totals = 1; }
	$opt_show_keywords = get_option( 'word_stats_show_keywords' );
	if ( $opt_show_keywords == null ) { $opt_show_keywords = 1; }

	$opt_ignore_keywords = get_option( 'word_stats_ignore_keywords' );

?>
<div class="wrap">
	<h2><?php _e( 'Word Stats Options', 'word-stats' ); ?></h2>

	<form method="post" action="options.php">
		 <?php settings_fields( 'word-stats-settings-group' ); ?>

		<p>
			<input type="hidden" name="word_stats_RI_column" value="0" />
			<input type="checkbox" name="word_stats_RI_column" value="1" <?php if ( $opt_RI_column ) { ?>checked="checked"<?php } echo ' '; ?>/>
			<?php _e( 'Display aggregate readability column in the manage posts list', 'word-stats' ); ?>	 	
		</p>

		<p>
			<input type="hidden" name="word_stats_totals" value="0" />
			<input type="checkbox" name="word_stats_totals" value="1" <?php if ( $opt_totals ) { ?>checked="checked"<?php } echo ' '; ?>/>
			<?php _e( 'Enable total word counts.', 'word-stats' ); ?> 
			<?php _e( 'This may slow down post saving in large blogs.', 'word-stats' ); ?> 
		</p>

		<p>
			<input type="hidden" name="word_stats_show_keywords" value="0" />
			<input type="checkbox" name="word_stats_show_keywords" value="1" <?php if ( $opt_show_keywords ) { ?>checked="checked"<?php } echo ' '; ?>/>
			<?php _e( 'Enable live keyword count.', 'word-stats' ); ?> 
		</p>

		<p>
			<?php _e( 'Ignore these keywords (space separated):', 'word-stats' ); ?><br /> 
			<textarea name="word_stats_ignore_keywords" cols="50" rows="10"><?php echo esc_attr( strip_tags( $opt_ignore_keywords ) ); ?></textarea>
		</p>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
<?php } ?>

<?php
// TODO:
// Allow to select algorythms
// Enable live readability
// Enable extra readability stats
// Calculate all posts now
// Delete readability metadata now
// Paragraph counts
// Word Stats Custom Index
?>