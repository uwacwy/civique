<?

//
//	content.php
//	--
//	display for regular blog posts
//

?>
<article <?php post_class(); ?>>
	<section class="row">

		

		<?php if( has_post_thumbnail() && !is_single() ): ?>
			<div class="col-md-9">
		<?php else : ?>
			<div class="col-md-12">
		<?php endif; ?>

			<?php if( is_single() ) : ?>
				<h2 class="the-title"><?php the_title(); ?></h2>
			<?php else: ?>
				<h3 class="the-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php endif; ?>

			<?php if( !is_search() ) : ?>
				<?php get_template_part('post', 'meta'); ?>
			<?php endif; ?>

			<div class="the-content">
				<?php 
					if( is_single() )
						the_content();
					else
						the_excerpt();

					wp_link_pages(
						array(
						'before' => '<div class="pagination"><span class="meta">Pages:</span>',
						'after' => '</div>'
						)
					);
				?>
			</div>

		</div>
		<?php get_template_part('post', 'thumb'); ?>
	</section>
</article>


