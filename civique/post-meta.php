<?php

$post_obj = get_post_type_object(get_post_type());

$created_date = get_the_date('U');
$modified_date = get_the_modified_date('U');
$created_time = get_the_time();
$modified_time = get_the_modified_time();

$created_author = get_the_author();
$author_posts_link = get_the_author_posts_link();
$modified_author = get_the_modified_author();;

$last_edited_by_sprint = sprintf(
	/* translators: %s: author name */
	esc_html__('last edited by %s', 'civique'),
	'<cite>' . esc_html($modified_author) . '</cite>'
);



?><ul class="inline post-meta">
	<li>
		Created: <a href="<?php the_permalink(); ?>"><?php echo $created_date . " " . $created_time; ?></a>
	</li>
	<li>
		Modified: <a href="<?php the_permalink(); ?>"><?php echo $modified_date . " " . $modified_time; ?></a>
	</li>

	<li class="icon-author"><?php if (is_page()) : ?>last edited <?php endif; ?>by <cite><?php the_author_posts_link(); ?></cite></li>

	<?php if (get_the_time() != get_the_modified_time() && get_the_date() != get_the_modified_date()) : ?>
		<li class="text-alert icon-time"><a href="<?php the_permalink(); ?>">updated <?php the_modified_time('F j, Y g:i A'); ?></a></li>
	<?php else: ?>
		<li class="icon-time"><a href="<?php the_permalink(); ?>"><?php the_time('F j, Y g:i A'); ?></a></li>
	<?php endif; ?>

	<?php if (comments_open()) : ?>
		<li class="icon-comments"><a href="<?php comments_link(); ?>"><?php comments_number("0&nbsp;comments", "1&nbsp;comment", "%&nbsp;comments"); ?></a></li>
	<?php else : ?>
		<li class="icon-comments">Comments Closed</li>
	<?php endif; ?>

	<?php if (is_single() && count(get_the_category()) < 10) : // if there are too many tags, don't show them until after the post
	?>
		<li class="icon-folder">Filed <?php the_category(', '); ?></li>
	<?php endif; ?>


	<?php edit_post_link($post_obj->labels->edit_item, '<li class="icon-edit">', '</li>'); ?>
</ul>