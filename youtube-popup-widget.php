<?php
/**
 * Plugin Name: Youtube popup widget
 * Description: Add card with youtube video info and play this video in popup.
 * Plugin URI:  https://github.com/ValikPavlenko/wp-plugin-youtube-popup-widget
 * Author URI:  http://valik.pavlenko.org.ua/
 * Author:      Valik Pavlenko
 * Version:     1.0
 *
 * Requires PHP: 7.4
 *
 */

function vv_youTubeIdByUrl( $youtube_url ) {
	preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtube_url, $match );

	return $match[1];
}

function vv_get_youtube_info( $ref ) {
	$json = file_get_contents( 'https://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $ref . '&format=json' );

	return json_decode( $json, true );
}

function video_youTube_scripts() {
	wp_enqueue_style(
		'youtube-popup-widget',
		plugins_url( 'youtube-popup-widget.css', __FILE__ ),
		array(),
		1.0
	);
}

add_action( 'wp_enqueue_scripts', 'video_youTube_scripts' );

class Video_youTube extends WP_Widget {
	function __construct() {
		parent::__construct(
			'youtube-popup-widget',
			'Youtube popup widget',
			array(
				'classname' => 'youtube-popup-widget',
				'description' => 'Add card with youtube video info and play this video in popup.',
			)
		);
	}

	public function widget( $args, $instance ) {
		$title   = apply_filters( 'widget_title', $instance['title'] );
		$youtube = apply_filters( 'widget_youtube', $instance['youtube'] );
		$sizeImg = apply_filters( 'widget_youtube', $instance['sizeImg'] );

		if ( $youtube ) :

			$youtube_id = vv_youTubeIdByUrl( $youtube );
			$info_video = vv_get_youtube_info( $youtube_id );

			echo $args['before_widget'];

			if ( ! empty( $title ) ) :
				echo '<h2 class="youtube-popup-widget__title">' . $title . '</h2>';
			endif; ?>
			<a href="https://www.youtube.com/embed/<?php echo $youtube_id; ?>"
			   class="youtube-popup-widget__link js-fancybox"
			   data-rel="media"
			>
				<div class="youtube-popup-widget__block-img">
					<img
                        class="youtube-popup-widget__img"
						src="https://img.youtube.com/vi/<?php echo $youtube_id; ?>/<?php echo $sizeImg; ?>"
						loading="lazy"
                        alt="<?php echo str_replace('"', '', $info_video['title']); ?>"
					>
					<svg class="youtube-popup-widget__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
						<path
							d="M31.67 9.179s-.312-2.353-1.271-3.389c-1.217-1.358-2.58-1.366-3.205-1.443C22.717 4 16.002 4 16.002 4h-.015s-6.715 0-11.191.347c-.625.077-1.987.085-3.205 1.443C.633 6.826.32 9.179.32 9.179S0 11.94 0 14.701v2.588c0 2.763.32 5.523.32 5.523s.312 2.352 1.271 3.386c1.218 1.358 2.815 1.317 3.527 1.459 2.559.262 10.877.343 10.877.343s6.722-.012 11.199-.355c.625-.08 1.988-.088 3.205-1.446.958-1.034 1.271-3.386 1.271-3.386s.32-2.761.32-5.523v-2.588c0-2.762-.32-5.523-.32-5.523z"
							fill="#E02F2F"
						></path>
						<path
							d="M12 10v12l10-6z"
							fill="#FFF"
						></path>
					</svg>
				</div>
				<h3 class="youtube-popup-widget__name-video"><?php echo $info_video['title']; ?></h3>
			</a>
			<?php

			echo $args['after_widget'];

		endif;
	}

	function form( $instance ) {
		$title   = @ $instance['title'] ?: 'Title';
		$youtube = @ $instance['youtube'] ?: 'Link to Youtube';
        $size_img = @ $instance['sizeImg']?: 'mqdefault.jpg';
		?>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'title' ); ?>"
			><?php _e( 'Title:' ); ?></label>
			<input
				id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>"
				type="text"
				required
				class="widefat"
				value="<?php echo esc_attr( $title ); ?>"
			/>
		</p>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'youtube' ); ?>"
			><?php _e( 'Link to youTube:' ); ?></label>
			<input
				id="<?php echo $this->get_field_id( 'youtube' ); ?>"
				name="<?php echo $this->get_field_name( 'youtube' ); ?>"
				type="text"
				required
				class="widefat"
				value="<?php echo esc_attr( $youtube ); ?>"
			/>
		</p>
        <p>
            <label
                    for="<?php echo $this->get_field_id( 'sizeImg' ); ?>"
            ><?php _e( 'Size images youTube:' ); ?></label>
            <select
                    id="<?php echo $this->get_field_id( 'sizeImg' ); ?>"
                    name="<?php echo $this->get_field_name( 'sizeImg' ); ?>"
                    type="text"
                    required
                    class="widefat"
                    value="<?php echo esc_attr( $size_img ); ?>"
            >
                <option <?php if( $size_img == 'mqdefault.jpg') echo 'selected'; ?> value="mqdefault.jpg">320x180px</option>
                <option <?php if( $size_img == '3.jpg') echo 'selected'; ?> value="3.jpg">120x90px</option>
                <option <?php if( $size_img == 'hqdefault.jpg') echo 'selected'; ?> value="hqdefault.jpg">480x360px</option>
                <option <?php if( $size_img == 'sddefault.jpg') echo 'selected'; ?> value="sddefault.jpg">640x480px</option>
            </select>
        </p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance            = array();
		$instance['title']   = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['youtube'] = ( ! empty( $new_instance['youtube'] ) ) ? strip_tags( $new_instance['youtube'] ) : '';
		$instance['sizeImg'] = ( ! empty( $new_instance['sizeImg'] ) ) ? strip_tags( $new_instance['sizeImg'] ) : 'mqdefault.jpg';

		return $instance;
	}
}

function register_widgets() {
    register_widget( 'Video_youTube' );
}

add_action( 'widgets_init', 'register_widgets' );
?>
