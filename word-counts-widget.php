<?php
/*
	Widget functions:
	form(): Outputs the widget options form in the admin interface.
	update(): Saves the options.
	widget(): Outputs the widget.
*/

/*
	Output total word counts.
*/
class widget_ws_word_counts extends WP_Widget {
	function widget_ws_word_counts() {
		parent::WP_Widget( false, $name = __( 'Total Word Counts', 'word-stats' ), array( 'description' => __( 'Displays the word counts of all public post types', 'word-stats' ) ) );
	}

	function form( $instance ) {
		$title = esc_attr( $instance[ 'title' ] );
		echo '<p><label for="', $this->get_field_id( 'title' ), '">', __( 'Title:', 'word-stats' ), ' <input class="widefat" id="', $this->get_field_id( 'title' ), '" name="', $this->get_field_name( 'title' ), '" type="text" value="', $title, '"" /></label></p>';
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		return $instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance[ 'title' ] );
		if ( !$title ) $title = __( 'Total Word Counts', 'word-stats' );
		$title = esc_attr( strip_tags( $title ) );
		echo $before_widget, $before_title,  $title, $after_title, '	<ul class="word-stats-counts">', Word_Stats_Core::get_word_counts( 'list' ), '</ul>', $after_widget;
	}
}

/* EOF */
