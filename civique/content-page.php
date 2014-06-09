<?

//
//	content.php
//	--
//	display for regular blog posts
//

?>
<article <?php post_class(); ?>>
	<h2 class="the-title"><?php the_title(); ?></h2>

	<div class="the-content">
		<?php the_content(); ?>
	</div>

	<hr>

	<?php get_template_part('post', 'meta'); ?>
</article>