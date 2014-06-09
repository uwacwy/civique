<?php

//
//	content-none.php
//	--
//	template to display when no content is available to output
//

?>
<?php if( !is_search() ): ?>
<div class="content-none">
	<h3>Nothing to display&hellip;</h3>
	<p><em>Although this was a valid request, there is nothing to display here at this time.</em></p>
</div>
<?php else : ?>
<div class="content-none">
	<p>There were no results found for <em><?php the_search_query(); ?></em>.</p>
</div>
<?php endif; ?>