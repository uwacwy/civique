<?php
	get_header();

	$children = get_pages( array('child_of'=>$post->ID) );

	$main_offset = 'col-md-offset-2';
	if( !empty($children) )
	{
		$main_offset = '';
	}

?>


<section class="stripe stripe-content padded">
	<div class="container">
		<div class="row">
			<div class="col-md-8 <?php echo $main_offset; ?>">
				<?php if( have_posts() ) : the_post();?>
					<?php get_template_part('content', 'page'); ?>
				<?php else: ?>
					<?php get_template_part('content', 'none'); ?>
				<?php endif; ?>
			</div>
			<?php if( !empty($children) ) : ?>
			<div class="col-md-4">
				<h3><?php echo $post->post_title; ?></h3>
				<ul>
					<?php foreach($children as $child) : ?>
						<?php echo sprintf('<li><a href="%s">%s</a></li>', get_permalink($child->ID), h($child->post_title) ); ?>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php get_footer(); ?>