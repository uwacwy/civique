<?

//
//	content-aside.php
//	--
//	display for regular blog posts
//

?>
<article <?php post_class(); ?>>
	<div class="the-content">
		<?php the_content(); ?>
		<?php get_template_part('post', 'meta'); ?>
	</div>

	
</article>