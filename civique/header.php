<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7 no-js" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8 no-js" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html class="not-ie no-js" <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo("charset"); ?>">
<meta name="viewport" content="width=device-width">
<title><?php
wp_title("&laquo;", true, "right");
bloginfo("name");
?></title>

<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,700italic,400italic,300,300italic' rel='stylesheet' type='text/css'>

<?php wp_head(); ?>

</head>
<body <?php body_class(""); ?>>

<header class="main-header headroom">
	<div class="mobile-wrap">
		<button class="mobile-menu-toggle" data-target=".mobile-wrap">
			<span class="hamburger-bar"></span>
			<span class="hamburger-bar"></span>
			<span class="hamburger-bar"></span>
		</button>
		<section class="brand">
			<h1><a href="<?php bloginfo("url"); ?>"><?php
$header_display_text = civique_get_theme_mod("header", "display_text");
if ($header_display_text == "") {
	bloginfo("name");
} else {
	echo $header_display_text;
}
?></a></h1>
		</section>

		<nav class="menu">
			<ul>
				<li class="icon-home hide-lg"><a href="<?php bloginfo("url"); ?>">Home</a></li>
				<?php wp_nav_menu([
    	"theme_location" => "main-menu",
    	"container" => false,
    	"items_wrap" => '%3$s',
    ]); ?>
			</ul>
		</nav>

		<?php
  $donate = civique_get_theme_mod("non_profit", "donate_url");
  if (!empty($donate)): ?>
			<nav class="right menu">
				<ul>
					<li class="donate"><?php echo sprintf(
     	'<a href="%s">Donate</a>',
     	$donate,
     ); ?></li>
				</ul>
			</nav>
		<?php endif;
  ?>

		<nav class="search">
			<ul>
				<?php get_template_part("search", "li"); ?>
			</ul>
		</nav>
	</div>
</header>
