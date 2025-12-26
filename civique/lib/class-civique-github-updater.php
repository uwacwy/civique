<?php
//
//  class-civique-github-updater.php
//  --
//  provides GitHub-backed updates for the Civique theme
//

if (!class_exists("Civique_Github_Updater")):
	class Civique_Github_Updater
	{
		private $slug = "civique";

		private $repository = "uwacwy/civique";

		private $cache_key = "civique_github_release";

		private $cache_ttl = 6 * HOUR_IN_SECONDS;

		private $api_base = "https://api.github.com/repos";

		public function __construct()
		{
			add_filter("pre_set_site_transient_update_themes", [
				$this,
				"inject_update",
			]);
			add_filter("themes_api", [$this, "themes_api"], 10, 3);
			add_filter(
				"http_request_args",
				[$this, "authorize_github_requests"],
				10,
				2,
			);
		}

		public function inject_update($transient)
		{
			if (!is_object($transient)) {
				$transient = new stdClass();
			}

			if (
				empty($transient->checked) ||
				!isset($transient->checked[$this->slug])
			) {
				return $transient;
			}

			$release = $this->get_release_data();
			if (!$release) {
				return $transient;
			}

			$remote_version = $this->normalize_version($release["tag_name"] ?? "");
			if (!$remote_version) {
				return $transient;
			}

			$current_version = $this->normalize_version($this->get_theme_version());
			if ($current_version === "") {
				return $transient;
			}

			if (version_compare($remote_version, $current_version, "<=")) {
				return $transient;
			}

			$package_url = $this->resolve_package_url($release);
			if (!$package_url) {
				return $transient;
			}

			$transient->response[$this->slug] = [
				"theme" => $this->slug,
				"new_version" => $remote_version,
				"url" => $release["html_url"] ?? "",
				"package" => $package_url,
			];

			return $transient;
		}

		public function themes_api($result, $action, $args)
		{
			if (
				$action !== "theme_information" ||
				empty($args->slug) ||
				$args->slug !== $this->slug
			) {
				return $result;
			}

			$release = $this->get_release_data();
			if (!$release) {
				return $result;
			}

			$version = $this->normalize_version($release["tag_name"] ?? "");
			$download_link = $this->resolve_package_url($release);

			$theme = $this->get_theme(
				$args->stylesheet ?? null,
				$args->theme_root ?? null,
			);

			return (object) [
				"name" => $theme->get("Name"),
				"slug" => $this->slug,
				"version" => $version,
				"author" => sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url($theme->get("AuthorURI")),
					esc_html($theme->get("Author")),
				),
				"homepage" => $release["html_url"] ?? $theme->get("ThemeURI"),
				"requires" => $theme->get("RequiresWP"),
				"tested" => $theme->get("TestedWP"),
				"download_link" => $download_link,
				"last_updated" => $release["published_at"] ?? "",
				"sections" => [
					"description" => wpautop($release["body"] ?? ""),
				],
			];
		}

		public function authorize_github_requests($args, $url)
		{
			$url_host = parse_url($url, PHP_URL_HOST);
			if (
				$url_host !== "api.github.com" &&
				$url_host !== "github.com" &&
				$url_host !== "raw.githubusercontent.com"
			) {
				return $args;
			}

			if (empty($args["headers"]) || !is_array($args["headers"])) {
				$args["headers"] = [];
			}

			if (empty($args["headers"]["User-Agent"])) {
				$args["headers"]["User-Agent"] = "civique-theme-updater";
			}

			return $args;
		}

		private function get_theme($stylesheet = null, $theme_root = null)
		{
			$stylesheet =
				is_string($stylesheet) && $stylesheet !== ""
					? $stylesheet
					: $this->slug;
			$theme_root = is_string($theme_root) ? $theme_root : "";

			$theme = wp_get_theme($stylesheet, $theme_root);
			if (!$theme->exists() && $stylesheet !== $this->slug) {
				return wp_get_theme($this->slug);
			}

			return $theme;
		}

		private function get_theme_version($stylesheet = null, $theme_root = null)
		{
			$theme = $this->get_theme($stylesheet, $theme_root);
			$version = $theme->get("Version");

			return is_string($version) ? trim($version) : "";
		}

		private function get_release_data()
		{
			$cached = get_transient($this->cache_key);
			if ($cached !== false) {
				return $cached;
			}

			$release = $this->fetch_release_data();
			if ($release) {
				set_transient($this->cache_key, $release, $this->cache_ttl);
			}

			return $release;
		}

		private function fetch_release_data()
		{
			$request_url = sprintf(
				"%s/%s/releases/latest",
				$this->api_base,
				$this->repository,
			);
			$args = [
				"timeout" => 10,
				"headers" => [
					"Accept" => "application/vnd.github+json",
					"User-Agent" => "civique-theme-updater",
				],
			];

			$response = wp_remote_get($request_url, $args);
			if (is_wp_error($response)) {
				return null;
			}

			$code = wp_remote_retrieve_response_code($response);
			if ($code !== 200) {
				return null;
			}

			$body = wp_remote_retrieve_body($response);
			$decoded = json_decode($body, true);

			return is_array($decoded) ? $decoded : null;
		}

		private function resolve_package_url($release)
		{
			if (empty($release["assets"]) || !is_array($release["assets"])) {
				return null;
			}

			foreach ($release["assets"] as $asset) {
				if (empty($asset["browser_download_url"]) || empty($asset["name"])) {
					continue;
				}

				if (preg_match('/^civique-.*\.zip$/', $asset["name"])) {
					return $asset["browser_download_url"];
				}
			}

			return null;
		}

		private function normalize_version($tag)
		{
			$tag = trim($tag);
			if ($tag === "") {
				return "";
			}

			return ltrim($tag, "v");
		}

		// private function get_token()
		// {
		// 	if (defined("CIVIQUE_GITHUB_TOKEN") && CIVIQUE_GITHUB_TOKEN) {
		// 		return CIVIQUE_GITHUB_TOKEN;
		// 	}

		// 	return "";
		// }
	}
endif;
