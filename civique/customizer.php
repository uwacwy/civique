<?php

require 'lib/scss.inc.php';

if( !function_exists('civique_custom_css') ) :
	function civique_custom_css()
	{
		$renderer = new scssc();
		$header_background_color = get_theme_mod('header_background_color');
		$header_logo_url = get_theme_mod('header_logo');
		$donate_button_color = get_theme_mod('np_donate_button_color');
		if($header_logo_url == "")
		{
			$header_logo_url = get_stylesheet_directory_uri() . '/css/img/brand_logo.png';
		}
		$uploads_dir = wp_upload_dir();
		$header_logo_path = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $header_logo_url);
		$logo_dimensions = getimagesize($header_logo_path);
		$logo_width = $logo_dimensions[0]."px";

		// echo sprintf('<pre>%s</pre>', print_r($logo_dimensions, true) );

$scss = <<<SCSS
\$header-background-color: $header_background_color;
\$header-logo: '$header_logo_url';
\$header-logo-width: $logo_width;
\$donate-button-color: $donate_button_color;

@media screen and (min-width: 641px)
{

	header.main-header section.brand h1
	{
		
		a
		{
			background-image: URL(\$header_logo);
			padding-left: \$header-logo-width + 20px;
			@media screen and (max-width: 1300)
			{
				padding-left: \$header-logo-width;
			}
		}
	}
	header.main-header
	{
		background-color: \$header_background_color;
		border-bottom: darken(\$header_background_color, 20%);

		.rgba &
		{
			background-color: transparentize(\$header-background-color, 0.07);
		}
	}
	header.main-header nav.menu > ul > li > .sub-menu
	{
		border-bottom: 3px solid darken(\$header-background-color, 10%);
		border-left: solid 1px darken(\$header-background-color, 10%);
		border-right: solid 1px darken(\$header-background-color, 10%);
	}

	nav.menu a
	{

	}

	nav.menu > ul > li > a
	{
		.no-js & a:hover,
		.js & a.hover
		{
			color: \$header-background-color;
		}
	}

	nav.menu > ul > li.donate
	{
		background-color: \$donate-button-color;
	}

	header.main-header nav.search .search-li .search-field
	{
		&:hover
		{
			color: #fff;
			background-color: lighten(\$header-background-color, 10%);
		}
		&:focus, &:focus:hover
		{
			color: #000;
			border-bottom-color: \$header-background-color;
		}
	}
}
SCSS;
		//echo '<!-- ' . print_r($scss, false) . ' -->';
	echo sprintf("<style>%s</style>", $renderer->compile($scss) );
	}
	add_action( 'wp_head' , 'civique_custom_css', 100);
endif;

if( !function_exists('civique_customizer') ) :
	function civique_customizer( $wp_customize )
	{
		$civique_offset = 200;

$wp_customize->add_setting('header_background_color', array('default' => '#2F4784', 'transport' => 'refresh') );
$wp_customize->add_setting('header_logo', array('default' => get_stylesheet_directory_uri() . '/css/img/brand_logo.png', 'transport' => 'refresh') );
$wp_customize->add_setting('social_facebook', array('default' => '', 'transport' => 'refresh') );
$wp_customize->add_setting('social_twitter', array('default' => '', 'transport' => 'refresh') );
$wp_customize->add_setting('location_address_1', array('default' => '123 Test Street', 'transport' => 'refresh') );
$wp_customize->add_setting('location_address_2', array('default' => '', 'transport' => 'refresh') );
$wp_customize->add_setting('location_city', array('default' => 'Laramie', 'transport' => 'refresh') );
$wp_customize->add_setting('location_state', array('default' => 'WY', 'transport' => 'refresh') );
$wp_customize->add_setting('location_zip', array('default' => '2134', 'transport' => 'refresh') );
$wp_customize->add_setting('contact_phone', array('default' => '3075551212', 'transport' => 'refresh') );
$wp_customize->add_setting('np_ein', array('default' => '8311111111', 'transport' => 'refresh') );
$wp_customize->add_setting('np_donate', array('default' => '/donate', 'transport' => 'refresh') );
$wp_customize->add_setting('np_donate_button_color', array('default' => '#d0b87b', 'transport' => 'refresh') );
$wp_customize->add_setting('header_display_text', array('default' => '', 'transport' => 'refresh') );



$wp_customize->add_section( 'civique_colors', 
	array('title' => __('Civique Header (colors, logo, etc)', 'civique'), 'priority' => 1 + $civique_offset) );
$wp_customize->add_section( 'civique_contact', 
	array('title' => __('Civique Contact', 'civique'), 'priority' => 2 + $civique_offset) );
$wp_customize->add_section( 'civique_social', 
	array('title' => __('Civique Social', 'civique'), 'priority' => 4 + $civique_offset) );
$wp_customize->add_section( 'civique_location', 
	array('title' => __('Civique Location', 'civique'), 'priority' => 3 + $civique_offset) );
$wp_customize->add_section( 'civique_non_profit', 
	array('title' => __('Civique Non-Profit', 'civique'), 'priority' => 5 + $civique_offset) );


$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'civique_ctl_header_background_color', 
	array('label' => __('Header Color', 'civique'), 'section' => 'civique_colors', 'settings' => 'header_background_color', 'priority' => 1 )));
$wp_customize->add_control( new WP_Customize_Image_Control($wp_customize, 'civique_ctl_header_logo', 
	array('label' => __('Header Logo', 'civique'), 'section' => 'civique_colors', 'settings' => 'header_logo', 'priority' => 2 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_social_facebook', 
	array('label' => __('Facebook URL', 'civique'), 'section' => 'civique_social', 'settings' => 'social_facebook', 'priority' => 1 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_social_twitter', 
	array('label' => __('Twitter Username', 'civique'), 'section' => 'civique_social', 'settings' => 'social_twitter', 'priority' => 2 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_location_address_1', 
	array('label' => __('Address 1', 'civique'), 'section' => 'civique_location', 'settings' => 'location_address_1', 'priority' => 1 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_location_address_2', 
	array('label' => __('Address 2', 'civique'), 'section' => 'civique_location', 'settings' => 'location_address_2', 'priority' => 2 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_location_city', 
	array('label' => __('City', 'civique'), 'section' => 'civique_location', 'settings' => 'location_city', 'priority' => 3 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_location_state', 
	array('label' => __('State', 'civique'), 'section' => 'civique_location', 'settings' => 'location_state', 'priority' => 4 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_location_zip', 
	array('label' => __('Zip Code', 'civique'), 'section' => 'civique_location', 'settings' => 'location_zip', 'priority' => 5 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_contact_phone', 
	array('label' => __('Phone Number', 'civique'), 'section' => 'civique_contact', 'settings' => 'contact_phone', 'priority' => 1 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_np_ein', 
	array('label' => __('EIN', 'civique'), 'section' => 'civique_non_profit', 'settings' => 'np_ein', 'priority' => 1 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_np_donate', 
	array('label' => __('Donation URL (blank to hide)', 'civique'), 'section' => 'civique_non_profit', 'settings' => 'np_donate', 'priority' => 2 )));
$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'civique_ctl_np_donate_button_color', 
	array('label' => __('Donation Button Color', 'civique'), 'section' => 'civique_non_profit', 'settings' => 'np_donate_button_color', 'priority' => 3 )));
$wp_customize->add_control( new WP_Customize_Control($wp_customize, 'civique_ctl_header_display_text', 
	array('label' => __('Header Text (blank for Site Name)', 'civique'), 'section' => 'civique_colors', 'settings' => 'header_display_text', 'priority' => 4 )));


	}
	add_action( 'customize_register', 'civique_customizer' );
endif;