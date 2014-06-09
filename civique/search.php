<?php get_header(); ?>

<section class="stripe stripe-content padded">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<h2>
					<small>Search Results</small><br>
					<?php the_search_query(); ?>
				</h2>
				<?php get_search_form(); ?>
				<?php get_template_part('loop'); ?>
			</div>
			<div class="col-md-4">
				<ul class="sidebar">
					<?php dynamic_sidebar('right-sidebar'); ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>