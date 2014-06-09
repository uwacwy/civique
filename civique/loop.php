<?php

//
//	loop.php
//	--
//	include to initiate and construct an appropriate loop
//


if( have_posts() ):

	while( have_posts() ):
		the_post();

		get_template_part('content', get_post_format() );

	endwhile;

	omega_archive_pagination();

else : // !have_posts();
	get_template_part('content', 'none');
endif;

?>