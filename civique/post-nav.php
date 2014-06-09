<?php
//
//	post-nav.php
//	--
//	shows next/previous post navigation
//
?>
<div class="row next-prev">
	<div class="col-md-6">
		<?php
			$next_link = get_next_post_link('%link', '&laquo; %title');
			if($next_link)
				echo $next_link;
			else
				echo ('<em>This is the latest post.</em>');
		?>
	</div>
	<div class="col-md-6">
		<?php
			$prev_link = get_previous_post_link('%link', '%title &raquo;');
			if($prev_link)
				echo $prev_link;
			else
				echo '<em>This is the earliest post</em>';
		?>
	</div>
</div>