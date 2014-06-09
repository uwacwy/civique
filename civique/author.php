<?php
//
//	author.php
//	--
//	Displays a single author's archive
//

    $author_obj = $wp_query->get_queried_object();

?>
<?php get_header(); ?>

<section class="stripe stripe-content padded">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<h2>
					<small>Author</small><br>
					<?php echo h($author_obj->nickname); ?>
				</h2>
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