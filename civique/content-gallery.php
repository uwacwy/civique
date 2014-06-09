<?php

//
//	content-gallery.php
//	--
//	template to display a gallery
//

?>
<article <?php post_class(); ?>>
	<?php if( is_single() ) : ?>
		<h2 class="the-title"><?php the_title(); ?></h2>
	<?php else: ?>
		<h3 class="the-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	<?php endif; ?>

	<?php get_template_part('post', 'meta'); ?>

	<div class="the-content gallery gallery-columns-4">
		<?php 
			if( is_single() )
				the_content();
			else
			{
				the_excerpt();

				$images = get_post_gallery_images();
				$i = 0;
				foreach ($images as $image)
				{
					if( $i < 8 )
					{
						echo sprintf('<figure class="gallery-item"><img src="%s" alt="Gallery Image %s" class="gallery-icon"></figure>',
							esc_attr($image), 
							esc_attr(get_the_title())
						);
					}
					$i++;
				}
			}
		?>
	</div>

	<?php if( is_single() ) : ?>
		<?php get_template_part('post', 'related'); ?>
	<?php endif; ?>

</article>