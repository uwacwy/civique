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

				// should be fully qualified links
				$links = [
					"twitter" => civique_get_theme_mod("social", "twitter"),
					"facebook" => civique_get_theme_mod("social", "facebook"),
					"instagram" => civique_get_theme_mod("social", "instagram"),
					"email" => civique_get_theme_mod("social", "email"),
				];

				$labels = [
					"twitter" => __("Twitter", "civique"),
					"facebook" => __("Facebook", "civique"),
					"instagram" => __("Instagram", "civique"),
					"email" => __("Email", "civique"),
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
							echo HtmlHelpers::tag(
								"li",
								[
									"class" => "social__item"
								],
								HtmlHelpers::tag(
									"a",
									[
										"href" => esc_url(
											$type === "email" ? "mailto:" . antispambot($value) : $value
										),
										"class" => sprintf("social__link social__link--%s", $type),
										"target" => "_blank",
										"rel" => "noopener noreferrer",
									],
									implode('', [
										HtmlHelpers::tag("span", ["class" => sprintf("social__icon dashicons dashicons-%s", $type)], ""),
										HtmlHelpers::tag("span", ["class" => "label"], esc_html($labels[$type])),
									])
								)
							);
						} ?>
					</ul>
				<?php endif; ?>
				<p>&copy; <?php echo date("Y"); ?> <?php bloginfo("name"); ?></p>
				<?php echo HtmlHelpers::tag(
					'p',
					[],
					sprintf(
						__('Proudly powered by %s and %s.', 'civique'),
						HtmlHelpers::tag('a', ['href' => 'http://www.wordpress.org', 'target' => '_blank'], 'WordPress'),
						HtmlHelpers::tag('a', ['href' => 'https://github.com/uwacwy/civique', 'target' => '_blank'], 'Civique for WordPress'),
					)
				); ?>
			</div>
		</div>
	</div>
</section>
<?php wp_footer(); ?>
</body>

</html>