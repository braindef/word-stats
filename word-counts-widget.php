<?php
// Widget to output word counts
class widget_ws_word_counts extends WP_Widget {
	function widget_ws_word_counts() {
		// widget actual processes
		parent::WP_Widget(false, $name = __( 'Total Word Counts', 'word-stats' ), array('description' => __( 'Displays the word counts of all public post types', 'word-stats' ) ) );
	}

	function form($instance) {
		// outputs the options form on admin
		$title = esc_attr( $instance[ 'title' ] );
		echo '<p><label for="', $this->get_field_id( 'title' ), '">', __( 'Title:', 'word-stats' ), ' <input class="widefat" id="', $this->get_field_id( 'title' ), '" name="', $this->get_field_name( 'title' ), '" type="text" value="', $title, '"" /></label></p>';
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
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '
			<ul class="word-stats-counts">',
				word_stats_counts::get_word_counts( 'list' ),
			'</ul>';
		echo $after_widget;
	}
} // end class

/* EOF */
