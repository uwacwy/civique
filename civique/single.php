<?php get_header(); ?>

<?php if( have_posts() ):
	while( have_posts() ):
		the_post();
?>

<?php if( has_post_thumbnail() ): ?>
<section class="stripe stripe-image-cover">
	<?php 
		echo sprintf(
			'<div class="image-cover" style="background-image: url(\'%s\');"></div>',
			wp_get_attachment_url(get_post_thumbnail_id() ),
			get_the_title(),
			get_the_excerpt()
		);
	?>
</section>
<?php endif; ?>

<section class="stripe stripe-content padded">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php get_template_part('content', get_post_format() ); ?>
				<?php get_template_part('post', 'nav'); ?>
				<?php get_template_part('post', 'related'); ?>
				<?php comments_template(); ?>
			</div>
			<div class="col-md-4">
				<ul class="sidebar">
					<?php dynamic_sidebar('right-sidebar'); ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php endwhile;
endif;
?>

<?php get_footer(); ?>