<?php if ( has_post_thumbnail() && !is_single() ) : ?>
	<div class="col-md-3 text-center">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
	</div>
<?php endif; ?>