<?php

// require_once "../vendor/autoload.php";

if (!function_exists("civique_get_theme_mod")):
	function civique_get_theme_mod()
	{
		$key = implode("__", ["civique", ...func_get_args()]);
		$value = get_theme_mod($key);
		if (defined("WP_DEBUG") && WP_DEBUG) {
			error_log(
				sprintf("civique_get_theme_mod: key='%s' => '%s'", $key, $value),
			);
		}
		return $value;
	}
endif;

if (!function_exists("civique_automatic_updates_complete")):
	function civique_automatic_updates_complete($results)
	{
		error_log(json_encode($results, JSON_PRETTY_PRINT));
		// clear theme mod cache after theme update
		wp_cache_delete("theme_mods_" . get_option("stylesheet"), "theme-mods");
	}

	// automatic_updates_complete
	add_action("after_switch_theme", "civique_automatic_updates_complete");
endif;

if (!function_exists("civique_custom_css")):
	function civique_custom_css()
	{
		$normalize_hex = static function ($value, $fallback) {
			$value = is_string($value) ? trim($value) : "";
			if ($value === "") {
				return strtoupper($fallback);
			}
			if ($value[0] !== "#") {
				$value = "#" . $value;
			}
			if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value) !== 1) {
				return strtoupper($fallback);
			}
			if (strlen($value) === 4) {
				$value =
					"#" .
					$value[1] .
					$value[1] .
					$value[2] .
					$value[2] .
					$value[3] .
					$value[3];
			}
			return strtoupper($value);
		};

		$hex_to_rgb = static function (string $hex): array {
			$hex = ltrim($hex, "#");
			return [
				hexdec(substr($hex, 0, 2)),
				hexdec(substr($hex, 2, 2)),
				hexdec(substr($hex, 4, 2)),
			];
		};

		$rgb_to_hsl = static function (int $r, int $g, int $b): array {
			$r /= 255;
			$g /= 255;
			$b /= 255;
			$max = max($r, $g, $b);
			$min = min($r, $g, $b);
			$l = ($max + $min) / 2;
			if ($max === $min) {
				return [0.0, 0.0, $l];
			}
			$d = $max - $min;
			$s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
			if ($max === $r) {
				$h = ($g - $b) / $d + ($g < $b ? 6 : 0);
			} elseif ($max === $g) {
				$h = ($b - $r) / $d + 2;
			} else {
				$h = ($r - $g) / $d + 4;
			}
			$h /= 6;
			return [$h, $s, $l];
		};

		$hsl_to_rgb = static function (float $h, float $s, float $l): array {
			if ($s === 0.0) {
				$val = (int) round($l * 255);
				return [$val, $val, $val];
			}
			$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
			$p = 2 * $l - $q;
			$convert = static function (float $p, float $q, float $t): float {
				if ($t < 0) {
					$t += 1;
				}
				if ($t > 1) {
					$t -= 1;
				}
				if ($t < 1 / 6) {
					return $p + ($q - $p) * 6 * $t;
				}
				if ($t < 1 / 2) {
					return $q;
				}
				if ($t < 2 / 3) {
					return $p + ($q - $p) * (2 / 3 - $t) * 6;
				}
				return $p;
			};
			$r = $convert($p, $q, $h + 1 / 3);
			$g = $convert($p, $q, $h);
			$b = $convert($p, $q, $h - 1 / 3);
			return [
				(int) round($r * 255),
				(int) round($g * 255),
				(int) round($b * 255),
			];
		};

		$adjust_lightness = static function (string $hex, float $delta) use (
			$hex_to_rgb,
			$rgb_to_hsl,
			$hsl_to_rgb,
		): string {
			[$r, $g, $b] = $hex_to_rgb($hex);
			[$h, $s, $l] = $rgb_to_hsl($r, $g, $b);
			$l = max(0.0, min(1.0, $l + $delta));
			[$nr, $ng, $nb] = $hsl_to_rgb($h, $s, $l);
			return sprintf("#%02X%02X%02X", $nr, $ng, $nb);
		};

		$with_alpha = static function (string $hex, float $alpha) use (
			$hex_to_rgb,
		): string {
			[$r, $g, $b] = $hex_to_rgb($hex);
			$alpha = max(0.0, min(1.0, $alpha));
			return sprintf("rgba(%d, %d, %d, %.2F)", $r, $g, $b, $alpha);
		};

		$default_header_color = "#2F4784";
		$default_logo_url =
			get_stylesheet_directory_uri() . "/css/img/brand_logo.png";
		$default_donate_color = "#D0B87B";

		$header_background_color = $normalize_hex(
			civique_get_theme_mod("header", "background_color"),
			$default_header_color,
		);
		$header_logo_url = civique_get_theme_mod("header", "logo");
		$header_logo_url =
			$header_logo_url !== "" ? $header_logo_url : $default_logo_url;
		$header_logo_url = esc_url_raw($header_logo_url);

		$donate_button_color = $normalize_hex(
			civique_get_theme_mod("non_profit", "donate_button_color"),
			$default_donate_color,
		);

		$default_logo_width = "95px";
		$default_logo_path = str_replace(
			WP_CONTENT_URL,
			WP_CONTENT_DIR,
			$default_logo_url,
		);
		if (
			strpos($default_logo_path, WP_CONTENT_DIR) === 0 &&
			file_exists($default_logo_path)
		) {
			$default_dimensions = @getimagesize($default_logo_path);
			if (
				is_array($default_dimensions) &&
				isset($default_dimensions[0]) &&
				$default_dimensions[0] > 0
			) {
				$default_logo_width = (int) $default_dimensions[0] . "px";
			}
		}

		$logo_width = $default_logo_width;
		$logo_path = str_replace(
			WP_CONTENT_URL,
			WP_CONTENT_DIR,
			$header_logo_url,
		);
		if (strpos($logo_path, WP_CONTENT_DIR) === 0 && file_exists($logo_path)) {
			$dimensions = @getimagesize($logo_path);
			if (
				is_array($dimensions) &&
				isset($dimensions[0]) &&
				$dimensions[0] > 0
			) {
				$logo_width = (int) $dimensions[0] . "px";
			}
		}

		$vars = [
			"--civique-header-background-color" => $header_background_color,
			"--civique-header-background-dark-10" => $adjust_lightness(
				$header_background_color,
				-0.1,
			),
			"--civique-header-background-dark-20" => $adjust_lightness(
				$header_background_color,
				-0.2,
			),
			"--civique-header-background-light-10" => $adjust_lightness(
				$header_background_color,
				0.1,
			),
			"--civique-header-background-transparent-07" => $with_alpha(
				$header_background_color,
				0.93,
			),
			"--civique-header-logo-image" =>
			$header_logo_url !== ""
				? sprintf("url(%s)", $header_logo_url)
				: "none",
			"--civique-header-logo-width" => $logo_width,
			"--civique-donate-button-color" => $donate_button_color,
		];

		$default_vars = [
			"--civique-header-background-color" => strtoupper(
				$default_header_color,
			),
			"--civique-header-background-dark-10" => $adjust_lightness(
				$default_header_color,
				-0.1,
			),
			"--civique-header-background-dark-20" => $adjust_lightness(
				$default_header_color,
				-0.2,
			),
			"--civique-header-background-light-10" => $adjust_lightness(
				$default_header_color,
				0.1,
			),
			"--civique-header-background-transparent-07" => $with_alpha(
				$default_header_color,
				0.93,
			),
			"--civique-header-logo-image" => sprintf(
				"url(%s)",
				esc_url_raw($default_logo_url),
			),
			"--civique-header-logo-width" => $default_logo_width,
			"--civique-donate-button-color" => strtoupper($default_donate_color),
		];

		$overrides = [];
		foreach ($vars as $name => $value) {
			$default_value = $default_vars[$name] ?? null;
			if ($default_value === null || $value !== $default_value) {
				$overrides[$name] = $value;
			}
		}

		if (empty($overrides)) {
			return;
		}

		$lines = [];
		foreach ($overrides as $name => $value) {
			$lines[] = sprintf("\t%s: %s;", esc_html($name), esc_html($value));
		}

		echo "<style>:root {\n" . implode("\n", $lines) . "\n}</style>";
	}
	add_action("wp_head", "civique_custom_css", 100);
endif;

if (!function_exists("civique_customizer")):
	function civique_customizer($wp_customize)
	{
		$civique_offset = 200;

		$sections = [
			"header" => [
				"title" => __("Civique Header (colors, logo, etc)", "civique"),
				"controls" => [
					"background_color" => [
						"cls" => WP_Customize_Color_Control::class,
						"label" => __("Background Color", "civique"),
						"default" => "#2F4784",
					],
					"logo" => [
						"cls" => WP_Customize_Image_Control::class,
						"label" => __("Header Logo", "civique"),
						"default" =>
						get_stylesheet_directory_uri() . "/css/img/brand_logo.png",
					],
					"display_text" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Header Text (blank for Site Name)", "civique"),
						"default" => "",
					],
				],
			],

			"contact" => [
				"title" => __("Civique Contact", "civique"),
				"controls" => [
					"contact_phone" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Phone Number", "civique"),
						"default" => "3075551212",
					],
				],
			],

			"social" => [
				"title" => __("Civique Social", "civique"),
				"controls" => [
					"facebook" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Facebook URL", "civique"),
						"default" => "",
					],
					"twitter" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Twitter Username", "civique"),
						"default" => "",
					],
					"instagram" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Instagram Username", "civique"),
						"default" => "",
					],
					"email" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Contact Email Address", "civique"),
						"default" => "",
					],
				],
			],

			"location" => [
				"title" => __("Civique Location", "civique"),
				"controls" => [
					"address_1" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Address 1", "civique"),
						"default" => "Box 350",
					],
					"address_2" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Address 2", "civique"),
						"default" => "",
					],
					"city" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("City", "civique"),
						"default" => "Boston",
					],
					"state" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("State", "civique"),
						"default" => "MA",
					],
					"postal_code" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Postal Code", "civique"),
						"default" => "02134",
					],
				],
			],

			"non_profit" => [
				"title" => __("Civique Non-Profit", "civique"),
				"controls" => [
					"donate_url" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("Donation URL (blank to hide)", "civique"),
						"default" => "/donate",
					],
					"donate_button_color" => [
						"cls" => WP_Customize_Color_Control::class,
						"label" => __("Donation Button Color", "civique"),
						"default" => "#d0b87b",
					],
					"ein" => [
						"cls" => WP_Customize_Control::class,
						"label" => __("EIN", "civique"),
						"default" => "831111111",
					],
				],
			],
		];

		$theme_slug = "civique";

		// add settings
		foreach ($sections as $section_slug => $section) {
			foreach ($section["controls"] as $setting_slug => $control) {
				$setting_default = $control["default"];
				$fully_qualified_setting_slug = implode("__", [
					"civique",
					$section_slug,
					$setting_slug,
				]);
				if (defined("WP_DEBUG") && WP_DEBUG) {
					error_log(
						sprintf(
							"civique_customizer: adding setting %s => '%s'",
							$fully_qualified_setting_slug,
							$setting_default,
						),
					);
				}
				$wp_customize->add_setting($fully_qualified_setting_slug, [
					"default" => $setting_default,
					"transport" => "refresh",
				]);
			}
		}
		$section_idx = 0;
		foreach ($sections as $section_slug => $section) {
			$fully_qualified_section_slug = implode("__", [
				$theme_slug,
				$section_slug,
			]);
			$wp_customize->add_section($fully_qualified_section_slug, [
				"title" => $section["title"],
				"priority" => $section_idx * 10 + $civique_offset,
			]);

			$setting_idx = 0;
			foreach ($section["controls"] as $setting_slug => $control) {
				$cls = $control["cls"] ?? WP_Customize_Control::class;
				$label =
					$control["label"] ??
					ucfirst(str_replace("_", " ", $setting_slug));

				$fully_qualified_setting_slug = implode("__", [
					$theme_slug,
					$section_slug,
					$setting_slug,
				]);

				$fully_qualified_control_slug = implode("__", [
					$theme_slug,
					$section_slug,
					"ctl",
					$setting_slug,
				]);

				$wp_customize->add_control(
					new $cls($wp_customize, $fully_qualified_control_slug, [
						"label" => $label,
						"section" => $fully_qualified_section_slug,
						"settings" => $fully_qualified_setting_slug,
						"priority" => $setting_idx,
					]),
				);
				$setting_idx++;
			}

			$section_idx++;
		}

		// $wp_customize->add_section("civique_colors", [
		// 	"title" => __("Civique Header (colors, logo, etc)", "civique"),
		// 	"priority" => 1 + $civique_offset,
		// ]);
		// $wp_customize->add_section("civique_contact", [
		// 	"title" => __("Civique Contact", "civique"),
		// 	"priority" => 2 + $civique_offset,
		// ]);
		// $wp_customize->add_section("civique_social", [
		// 	"title" => __("Civique Social", "civique"),
		// 	"priority" => 4 + $civique_offset,
		// ]);
		// $wp_customize->add_section("civique_location", [
		// 	"title" => __("Civique Location", "civique"),
		// 	"priority" => 3 + $civique_offset,
		// ]);
		// $wp_customize->add_section("civique_non_profit", [
		// 	"title" => __("Civique Non-Profit", "civique"),
		// 	"priority" => 5 + $civique_offset,
		// ]);
	}
	add_action("customize_register", "civique_customizer");
endif;
