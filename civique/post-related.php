<ul class="post-meta inline">
	<?php the_tags('<li class="icon-tag">Tagged ', ', ', '</li>'); ?>
	<?php if(count(get_the_category()) >= 10) : ?>
		<li class="icon-folder">Filed <?php the_category(', '); ?></li>
	<?php endif; ?>
</ul>
