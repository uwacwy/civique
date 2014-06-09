<?php
/*
Template Name: Page w/ Sidebar
*/

get_header();

	$children = get_pages( array('child_of'=>$post->ID) );

?>

<section class="stripe stripe-content padded">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php if( have_posts() ) : the_post();?>
					<?php get_template_part('content', 'page'); ?>
				<?php else: ?>
					<?php get_template_part('content', 'none'); ?>
				<?php endif; ?>
			</div>
			<div class="col-md-4">
				<?php if( !empty($children) ) : ?>
				<div>
					<h3><?php echo $post->post_title; ?></h3>
					<ul>
						<?php foreach($children as $child) : ?>
							<?php echo sprintf('<li><a href="%s">%s</a></li>', get_permalink($child->ID), h($child->post_title) ); ?>
						<?php endforeach; ?>
					</ul>
					<hr>
				</div>
				<?php endif; ?>
				<ul class="sidebar">
					<?php dynamic_sidebar('right-sidebar'); ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>