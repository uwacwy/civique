<?php
//
//  widget-fundraiser-bloomerang.php
//  --
//  a widget to display Bloomerang campaign metrics
//

require_once "components/progress-bar.component.php";

class HtmlHelpers
{
	static function tag($name, $attributes = [], $content = "")
	{
		$attr_strs = [];
		foreach ($attributes as $key => $value) {
			// if boolean, like 'disabled' or 'checked'
			if (is_bool($value)) {
				if ($value) {
					$attr_strs[] = esc_attr($key);
				}
				continue;
			}

			$attr_strs[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
		}
		return sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			esc_html($name),
			implode(" ", $attr_strs),
			$content,
		);
	}

	static function comment($text)
	{
		$is_multiline = strpos($text, "\n") !== false;
		$template = $is_multiline
			? implode("\n", ["<!--", "%s", "-->"])
			: "<!-- %s -->";

		// replace all '--' with typographic em dash to avoid breaking comment
		$text = str_replace("--", "—", $text);

		// replace '-->' with '==>' to avoid breaking comment
		$text = str_replace("-->", "==>", $text);

		return sprintf($template, $text);
	}
}

function labeled_text_field($label, $id, $name, $value)
{
	return HtmlHelpers::tag(
		"p",
		[],
		HtmlHelpers::tag("label", ["for" => $id], esc_html($label)) .
			HtmlHelpers::tag("input", [
				"class" => "widefat",
				"id" => $id,
				"name" => $name,
				"type" => "text",
				"value" => $value,
			]),
	);
}

function control_reset_button(string $label, string $id, $value)
{
	return HtmlHelpers::tag(
		"p",
		[],
		HtmlHelpers::tag(
			"button",
			[
				"class" => "button button-secondary",
				"id" => $id,
				"type" => "button",
				"data-default-value" => $value,
			],
			esc_html($label),
		),
	);
}

if (!defined("CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_WIDGET_TITLE")) {
	define(
		"CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_WIDGET_TITLE",
		__("Fundraiser", "civique"),
	);
}

if (!defined("CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CTA_TEXT")) {
	define(
		"CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CTA_TEXT",
		__("Donate to %s", "civique"),
	);
}

if (!defined("CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CACHE_LENGTH")) {
	define("CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CACHE_LENGTH", 30); // minutes
}

class Civique_Bloomerang_Campaign_Widget extends WP_Widget
{
	/**
	 * Register widget with WordPress.
	 */
	function __construct()
	{
		parent::__construct(
			"civique_bloomerang_campaign",
			__("Civique Bloomerang Campaign", "civique"),
			[
				"description" => __(
					"Displays fundraising progress for a Bloomerang campaign.",
					"civique",
				),
			],
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance)
	{
		// check if campaign_id and api_key are set
		// if not, return comment string for site owner
		if (empty($instance["campaign_id"]) || empty($instance["api_key"])) {
			echo HtmlHelpers::comment(
				__(
					"Bloomerang Campaign Widget: Please set Campaign ID and API Key in widget settings.",
					"civique",
				),
			);
			return;
		}

		// $title = apply_filters("widget_title", );
		$campaign_id = isset($instance["campaign_id"])
			? trim($instance["campaign_id"])
			: "";
		$api_key = isset($instance["api_key"]) ? trim($instance["api_key"]) : "";
		$donate_link = isset($instance["donate_link"])
			? trim($instance["donate_link"])
			: "";
		$title = !empty($instance["title"])
			? $instance["title"]
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_WIDGET_TITLE;
		$custom_cta = isset($instance["cta_text"])
			? trim($instance["cta_text"])
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CTA_TEXT;
		$cache_length = isset($instance["cache_length"])
			? (int) $instance["cache_length"]
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CACHE_LENGTH;

		// validations and filters
		$title = apply_filters("widget_title", $title);

		$cache_length =
			$cache_length > 0
			? $cache_length
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CACHE_LENGTH;

		echo $args["before_widget"];

		if (!empty($title)) {
			echo $args["before_title"] . $title . $args["after_title"];
		}

		$campaign_data = $this->get_campaign_data(
			$campaign_id,
			$api_key,
			$cache_length,
		);

		if (empty($campaign_data)) {
			printf(
				"<p>%s</p>",
				esc_html__(
					"Campaign data is currently unavailable. Please try again later.",
					"civique",
				),
			);
			echo $args["after_widget"];
			return;
		}

		$goal = isset($campaign_data["Goal"])
			? (float) $campaign_data["Goal"]
			: 0.0;
		$progress = isset($campaign_data["Raised"])
			? (float) $campaign_data["Raised"]
			: 0.0;
		$pct = $goal > 0 ? min(100, ($progress / $goal) * 100) : 0;

		$pct_str = "";
		if ($pct >= 5) {
			$pct_str = sprintf("%s%%", number_format($pct, 0));
		}

		$bar_width = $goal > 0 ? $pct : 100;

		HtmlHelpers::tag(
			"div",
			["class" => "fundraiser"],
			implode("", [
				HtmlHelpers::tag(
					"div",
					[
						"class" => "progress",
						"style" => sprintf('width: %1$f%%;', $bar_width),
					],
					esc_html($pct_str),
				),
			]),
		);

		echo civique_progress_bar($pct, $pct_str);

		printf(
			"<p>%s</p>",
			sprintf(
				esc_html__('Currently raised %1$s of %2$s goal', "civique"),
				'<strong>$' . number_format($progress, 0) . "</strong>",
				'$' . number_format($goal, 0),
			),
		);

		$title_for_cta = trim(wp_strip_all_tags($title));
		$cta_text =
			$custom_cta !== ""
			? $custom_cta
			: ($title_for_cta !== ""
				? sprintf(esc_html__("Donate to %s", "civique"), $title_for_cta)
				: esc_html__("Donate", "civique"));

		if (!empty($donate_link)) {
			printf(
				'<p><a href="%1$s" class="">%2$s</a></p>',
				esc_url($donate_link),
				esc_html($cta_text),
			);
		}

		echo $args["after_widget"];
	}

	/**
	 * Retrieve campaign data with transient caching.
	 *
	 * @param string $campaign_id Campaign identifier.
	 * @param string $api_key API key for Bloomerang.
	 * @param int    $cache_length Cache duration in minutes.
	 *
	 * @return array
	 */
	private function get_campaign_data($campaign_id, $api_key, $cache_length)
	{
		// $campaign_id should be numeric
		if (!is_numeric($campaign_id)) {
			return [];
		}

		if (empty($api_key)) {
			return [];
		}

		$transient_key = sprintf(
			"civique_bloomerang_campaign_%s",
			md5($campaign_id),
		);
		$cached = get_transient($transient_key);

		if (false !== $cached) {
			return $cached;
		}

		$url = sprintf(
			"https://api.bloomerang.co/v2/campaign/%s",
			rawurlencode($campaign_id),
		);
		$args = [
			"headers" => [
				"accept" => "application/json",
				"X-API-KEY" => $api_key,
			],
			"timeout" => 10,
		];

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			return [];
		}

		$code = wp_remote_retrieve_response_code($response);
		if (200 !== $code) {
			return [];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (!is_array($data)) {
			return [];
		}

		// Cache API payload for the provided number of minutes to avoid rate limits.
		set_transient($transient_key, $data, $cache_length * MINUTE_IN_SECONDS);

		return $data;
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance)
	{
		$title = isset($instance["title"])
			? $instance["title"]
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_WIDGET_TITLE;
		$cta_text = isset($instance["cta_text"])
			? $instance["cta_text"]
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CTA_TEXT;
		$cache_length = isset($instance["cache_length"])
			? $instance["cache_length"]
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CACHE_LENGTH;
		$donate_link = isset($instance["donate_link"])
			? $instance["donate_link"]
			: "";
		$campaign_id = $instance["campaign_id"];
		$api_key = $instance["api_key"];

		echo implode("", [
			labeled_text_field(
				__("Title:", "civique"),
				$this->get_field_id("title"),
				$this->get_field_name("title"),
				$title,
			),
			labeled_text_field(
				__("Campaign ID:", "civique"),
				$this->get_field_id("campaign_id"),
				$this->get_field_name("campaign_id"),
				$campaign_id,
			),
			labeled_text_field(
				__("API Key:", "civique"),
				$this->get_field_id("api_key"),
				$this->get_field_name("api_key"),
				$api_key,
			),
			labeled_text_field(
				__("Link to Donate:", "civique"),
				$this->get_field_id("donate_link"),
				$this->get_field_name("donate_link"),
				$donate_link,
			),
			labeled_text_field(
				__("Call to Action Text:", "civique"),
				$this->get_field_id("cta_text"),
				$this->get_field_name("cta_text"),
				$cta_text,
			),
			labeled_text_field(
				__("Cache Duration (minutes):", "civique"),
				$this->get_field_id("cache_length"),
				$this->get_field_name("cache_length"),
				$cache_length,
			),
		]);
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = [];

		$instance["title"] = !empty($new_instance["title"])
			? sanitize_text_field($new_instance["title"])
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_WIDGET_TITLE;

		$instance["cta_text"] = !empty($new_instance["cta_text"])
			? sanitize_text_field($new_instance["cta_text"])
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CTA_TEXT;

		$instance["cache_length"] = !empty($new_instance["cache_length"])
			? absint($new_instance["cache_length"])
			: CIVIQUE__BLOOMERANG_WIDGET__DEFAULT_CACHE_LENGTH;

		$instance["campaign_id"] = !empty($new_instance["campaign_id"])
			? sanitize_text_field($new_instance["campaign_id"])
			: "";

		$instance["api_key"] = !empty($new_instance["api_key"])
			? trim($new_instance["api_key"])
			: "";

		$instance["donate_link"] = !empty($new_instance["donate_link"])
			? esc_url_raw($new_instance["donate_link"])
			: "";

		return $instance;
	}
}
