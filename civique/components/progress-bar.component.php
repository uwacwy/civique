<?php

function civique_progress_bar($percentage, $label = "")
{
	$percentage = max(0, min(100, intval($percentage)));
	$label_html = esc_html($label ?: "{$percentage}%");

	$bar_html = sprintf(
		'<div class="civique-progress-bar">
			<div class="civique-progress-bar__progress" style="width: %d%%;">%s</div>
		</div>',
		$percentage,
		$label_html,
	);

	return $bar_html;
}
