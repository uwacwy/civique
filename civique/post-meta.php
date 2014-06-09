<?php

	$post_obj = get_post_type_object( get_post_type() );


?><ul class="inline post-meta">
	<li class="icon-author"><?php if(is_page()) : ?>last edited <?php endif; ?>by <cite><?php the_author_posts_link(); ?></cite></li>
	<?php if ( get_the_time() != get_the_modified_time() && get_the_date() != get_the_modified_date() ) : ?> 
		<li class="text-alert icon-time"><a href="<?php the_permalink(); ?>">updated <?php the_modified_time('F j, Y g:i A'); ?></a></li>
	<?php else: ?>
		<li class="icon-time"><a href="<?php the_permalink(); ?>"><?php the_time('F j, Y g:i A'); ?></a></li>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<li class="icon-comments"><a href="<?php comments_link(); ?>"><?php comments_number("0&nbsp;comments", "1&nbsp;comment", "%&nbsp;comments"); ?></a></li>
	<?php else : ?>
		<li class="icon-comments">Comments Closed</li>
	<?php endif; ?>

	<?php if( is_single() && count( get_the_category()) < 10) : // if there are too many tags, don't show them until after the post ?>
		<li class="icon-folder">Filed <?php the_category(', '); ?></li>
	<?php endif; ?>
	

	<?php edit_post_link( $post_obj->labels->edit_item, '<li class="icon-edit">', '</li>'); ?>
</ul>