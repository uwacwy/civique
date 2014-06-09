<?php
//
//	comments.php
//	--
//	the comments display
//
?>
<section id="comments">
	<h3>Discussion</h3>
	<?php if ( have_comments() ) : ?>
		<h4>
			<?php printf( 
				_nx(
					'One comment on this entry', '%s comments on this entry', get_comments_number(), 'comments title', 'civique'
				),
				number_format_i18n( get_comments_number() )
			); ?>
		</h4>

		<ol class="comments-list">
			<?php wp_list_comments( array('style' => 'ol', 'short_ping' => true, 'avatar_size' => 50) ); ?>
		</ol>

	<?php else: // post does not have comments ?>
		<p><em>There are no comments on this post.</em></p>
	<?php endif; // have_comments ?>
	<?php if( comments_open() ) : ?>
		<?php comment_form(); ?>
	<?php else : ?>
		<p><em>Comments are currently closed for this post.</em></p>
	<?php endif; ?>
</section>