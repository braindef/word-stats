<?php

# Exit if not called by uninstall process
if ( !WP_UNINSTALL_PLUGIN ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

 # Don't stop if the user browses away. Probably not needed.
ignore_user_abort( true );
ini_set( 'max_execution_time', 600 );

# Stop worker
wp_clear_scheduled_hook( 'word_stats_worker' );

# Purge settings
delete_option( 'word_stats_options' );
delete_option( 'word_stats_state' );

# Purge metadata
global $wpdb;
$query = "
	DELETE FROM $wpdb->postmeta
	WHERE $wpdb->postmeta.meta_key = 'readability_ARI'
	OR $wpdb->postmeta.meta_key = 'readability_CLI'
	OR $wpdb->postmeta.meta_key = 'readability_LIX'
	OR $wpdb->postmeta.meta_key = 'word_stats_cached'
	OR $wpdb->postmeta.meta_key = 'word_stats_keywords'
	OR $wpdb->postmeta.meta_key = 'word_stats_word_count'
";
$posts = $wpdb->query( $query, OBJECT );

/* EOF */
