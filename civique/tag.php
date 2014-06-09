<?php get_header(); ?>

<section class="stripe stripe-content padded">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<h2>
					<small>Tag</small><br>
					<?php single_cat_title(); ?>
				</h2>
				<?php get_template_part('loop'); ?>
			</div>
			<div class="col-md-4">

			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>