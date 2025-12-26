<section class="stripe stripe-footer padded stripe-rule">
	<div class="container">
		<div class="row">
			<div class="col-md-3">
				<?php echo sprintf(
					'<p><a href="%s"><img src="%s" title="%s logo"></a></p>',
					get_bloginfo("url"),
					civique_get_theme_mod("header", "logo")
						? civique_get_theme_mod("header", "logo")
						: get_stylesheet_directory_uri() . "/css/img/brand_logo.png",
					get_bloginfo("name"),
				); ?>
				<address>
					<strong><?php bloginfo("name"); ?></strong><br>
					<?php
					$address_parts = [
						civique_get_theme_mod("location", "address_1"),
						civique_get_theme_mod("location", "address_2"),
						sprintf(
							"%s, %s %s",
							civique_get_theme_mod("location", "city"),
							civique_get_theme_mod("location", "state"),
							civique_get_theme_mod("location", "postal_code"),
						),
					];
					echo implode("<br>", array_filter(array_map("h", $address_parts)));
					?>
				</address>
				<?php
				$contact_phone = civique_get_theme_mod("contact", "contact_phone");
				if ($contact_phone != "") {
					echo sprintf(
						"<p>(%s) %s-%s</p>",
						substr($contact_phone, 0, 3),
						substr($contact_phone, 3, 3),
						substr($contact_phone, 6, 4),
					);
				}
				?>
				<p><?php wp_loginout(); ?></p>
			</div>
			<div class="col-md-9">
				<h3><?php bloginfo("name"); ?></h3>
				<p><?php
					$description = get_bloginfo("description");
					echo wptexturize($description); ?></p>
				<ul class="inline">
					<?php wp_nav_menu([
						"theme_location" => "footer-menu",
						"container" => false,
						"items_wrap" => '%3$s',
					]); ?>
				</ul>
				<hr>
				<?php
				$links = [
					"twitter" => civique_get_theme_mod("social", "twitter"),
					"facebook" => civique_get_theme_mod("social", "facebook"),
					"instagram" => civique_get_theme_mod("social", "instagram"),
					"email" => civique_get_theme_mod("social", "email"),
				];

				// if any social links are set, show the social section
				if (
					!empty($links["twitter"]) ||
					!empty($links["facebook"]) ||
					!empty($links["instagram"]) ||
					!empty($links["email"])
				): ?>
					<ul class="social">
						<?php foreach ($links as $type => $value) {
							if (empty($value)) {
								continue;
							}
							switch ($type) {
								case "twitter":
									echo sprintf(
										'<li><a href="https://twitter.com/%s" class="icon-twitter" aria-label="Twitter"></a></li>',
										h($value),
									);
									break;
								case "facebook":
									echo sprintf(
										'<li><a href="%s" class="icon-facebook" aria-label="Facebook"></a></li>',
										h($value),
									);
									break;
								case "instagram":
									echo sprintf(
										'<li><a href="https://instagram.com/%s" class="icon-instagram" aria-label="Instagram"></a></li>',
										h($value),
									);
									break;
								case "email":
									echo sprintf(
										'<li><a href="mailto:%s" class="icon-email" aria-label="Email"></a></li>',
										h($value),
									);
									break;
							}
						} ?>
					</ul>
				<?php endif; ?>
				<p>&copy; <?php echo date("Y"); ?> <?php bloginfo("name"); ?></p>
				<p>Powered by <a href="http://www.wordpress.org">WordPress</a> and Civique for WordPress</p>
			</div>
		</div>
	</div>
</section>
<?php wp_footer(); ?>
</body>

</html>