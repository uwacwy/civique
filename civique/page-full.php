<?php
/*
Template Name: Page (full-width)
*/

get_header(); ?>

<section class="stripe stripe-content padded">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php if( have_posts() ) : the_post();?>
					<?php get_template_part('content', 'page'); ?>
				<?php else: ?>
					<?php get_template_part('content', 'none'); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>