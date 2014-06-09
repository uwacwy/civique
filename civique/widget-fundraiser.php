<?php
//
//	widget-fundraiser.php
//	--
//	a widget to display a fundraiser in progress
//

class Civique_Fundraiser_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'civique_fundraiser', // Base ID
			__('Civique Fundraiser', 'civique'), // Name
			array( 'description' => __( 'This widget will show a fundraiser, and the current progress for the fundraiser.', 'civique' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$goal = $instance['goal'];
		$progress = $instance['progress'];
		$pct = ($progress/$goal) * 100;

		$donate_link = false;
		if(isset($instance['donate_link']) )
		{
			$donate_link = $instance['donate_link'];
		}

		$pct_str = "";
		if( $pct > 5 )
		{
			$pct_str = sprintf("%s%%", number_format($pct, 0) );
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		if( $progress > $goal )
		{
			echo sprintf('<div class="fundraiser"><div class="progress" style="width: 100%%;">%2$s</div></div>', $pct, $pct_str );
		}
		else
		{
			echo sprintf('<div class="fundraiser"><div class="progress" style="width: %1$f%%;">%2$s</div></div>', $pct, $pct_str );
		}
		

		echo sprintf('<p>Currently <strong>$%s</strong> of $%s goal</p>', number_format($progress, 0), number_format($goal, 0) );
		if($donate_link != false)
		{
			echo sprintf('<p><a href="%s" class="btn btn-donate">Donate to %s</a></p>', $donate_link, $title);
		}

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		extract($instance);

		if( !isset($title) )
			$title = "Fundraiser";
		if( !isset($goal) )
			$goal = 1000000;
		if( !isset($progress) )
			$progress = 10000;
		if( !isset($donate_link) )
			$donate_link = '/donate';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'goal' ); ?>"><?php _e( 'Goal:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'goal' ); ?>" name="<?php echo $this->get_field_name( 'goal' ); ?>" type="text" value="<?php echo esc_attr( $goal ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'progress' ); ?>"><?php _e( 'Progress:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'progress' ); ?>" name="<?php echo $this->get_field_name( 'progress' ); ?>" type="text" value="<?php echo esc_attr( $progress ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'donate_link' ); ?>"><?php _e( 'Link to Donate:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'donate_link' ); ?>" name="<?php echo $this->get_field_name( 'donate_link' ); ?>" type="text" value="<?php echo esc_attr( $donate_link ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['goal'] = ( ! empty( $new_instance['goal'] ) ) ? strip_tags( $new_instance['goal'] ) : '';
		$instance['progress'] = ( ! empty( $new_instance['progress'] ) ) ? strip_tags( $new_instance['progress'] ) : '';
		$instance['donate_link'] = ( ! empty( $new_instance['donate_link'] ) ) ? strip_tags( $new_instance['donate_link'] ) : '';

		return $instance;
	}

}