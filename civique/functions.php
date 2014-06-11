<?php
/*
	functions.php
	--
	contains server-side programming logic to be used in theme construction
*/

	require 'customizer.php';

/*
	h
	--
	alias for htmlspecialchars()
*/
if( !function_exists('h') ) :

	function h($input)
	{
		return htmlspecialchars($input);
	}

endif;

if( !function_exists('lhs_theme_features') ) :

	function lhs_theme_features()  {
		global $wp_version;

		// Add theme support for Automatic Feed Links
		add_theme_support( 'automatic-feed-links' );

		// Add theme support for Post Formats
		$formats = array( 'gallery', 'image', 'aside', 'link' );
		add_theme_support( 'post-formats', $formats );  

		// Add theme support for Featured Images
		add_theme_support( 'post-thumbnails' ); 

		// Add theme support for Custom Header
		$header_args = array(
		'default-image'          => 'http://lorempixel.com/1280/400/nature',
		'width'                  => 1280,
		'height'                 => 400,
		'flex-width'             => false,
		'flex-height'            => false,
		'random-default'         => true,
		'header-text'            => false,
		'default-text-color'     => '',
		'uploads'                => true,
		'wp-head-callback'       => '',
		'admin-head-callback'    => '',
		'admin-preview-callback' => '',
		);
		add_theme_support( 'custom-header', $header_args );

		// Add theme support for Semantic Markup
		$markup = array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', );
		add_theme_support( 'html5', $markup );  

	}

	add_action( 'after_setup_theme', 'lhs_theme_features' );

endif;

if( !function_exists('civique_register_widgets') ) :
// register Foo_Widget widget
	function civique_register_widgets() {
		require 'widget-fundraiser.php';
		register_widget( 'Civique_Fundraiser_Widget' );
	}
	add_action( 'widgets_init', 'civique_register_widgets' );
endif;

if( !function_exists('lhs_navigation_menus') ) :

	// Register Navigation Menus
	function lhs_navigation_menus() {

		$locations = array(
			'main-menu' => __( 'Main Menu', 'lhs' ),
			'footer-menu' => __( 'Footer Menu', 'lhs'),
			'front-page-quick-menu' => __('Front Page Quick Menu', 'lhs')
		);
		register_nav_menus( $locations );

	}

	// Hook into the 'init' action
	add_action( 'init', 'lhs_navigation_menus' );

endif;

if( !function_exists('lhs_enqueue_js_css') ):

	function lhs_enqueue_js_css()
	{
		wp_enqueue_style('lhs', get_template_directory_uri() . '/css/lhs.css');
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script('lhs', get_template_directory_uri() . '/js/lhs.js', array('jquery') );
		if ( is_singular() ) wp_enqueue_script( 'comment-reply' );
	}

	add_action('wp_enqueue_scripts', 'lhs_enqueue_js_css');

endif;

if( !function_exists('lhs_custom_sidebar') ) :

	// Register Sidebar
	function lhs_custom_sidebar() {

	$args = array(
		'id'            => 'right-sidebar',
		'name'          => __( 'Right Sidebar', 'lhs' ),
		'description'   => __( 'Include widgets here to provide convenient supplementary information to your visitors.', 'lhs' ),
		'before_widget' => __('<li id="%1$s" class="widget %2$s">'),
		'after_widget' => __("</li>\n"),
		'before_title' => __("<h3>"),
		'after_title' => __('</h3>')
	);
	register_sidebar( $args );

	}

	// Hook into the 'widgets_init' action
	add_action( 'widgets_init', 'lhs_custom_sidebar' );

endif;

/*


	lhs_admin_init()
	--
	registers custom settings
*/

if ( !function_exists('lhs_admin_init') ) :

	function lhs_admin_init()
	{
		$settings = array(
			'lhs_facebook',
			'lhs_twitter',
			'lhs_address_1',
			'lhs_address_2',
			'lhs_city',
			'lhs_state',
			'lhs_zip_code',
			'lhs_phone_number',
			'lhs_custom_css',
			'lhs_home_intro',
			'omega_ein'
		);

		foreach ($settings as $setting)
		{
			register_setting( 'lhs-settings', $setting );
		}
	}

	add_action( 'admin_init', 'lhs_admin_init' );

endif;

/*
	omega_non_profit_shortcode()
	--
	prints information about a non-profit
*/
function omega_non_profit_shortcode()
{
	$dt = "<dt>%s</dt><dd>%s</dd>";
	$td_right = '<td class="text-right">%s</td>';

	$url = sprintf('https://projects.propublica.org/nonprofits/api/v1/organizations/%s.json', get_theme_mod('np_ein') );

	$response = wp_remote_get($url);
	$json = json_decode($response['body']);

	$rtn = "";

	$rtn = "<dl>";
	$rtn .= sprintf($dt, 'Name', $json->organization->name);
	$rtn .= sprintf($dt, "EIN", sprintf("%s-%s", substr($json->organization->ein, 0, 2), substr($json->organization->ein, 2) ) );
	$rtn .= sprintf($dt, "Section", sprintf('501(c)(%s)', $json->organization->subsection_code) );
	$rtn .= sprintf($dt, "Address", sprintf('%s<br>%s, %s %s', 
		$json->organization->address,
		$json->organization->city,
		$json->organization->state,
		$json->organization->zipcode
		)
	);
	$rtn .= "</dl>";
	
	if( !empty($json->filings_with_data) )
	{	
		$rtn .= sprintf('<h3>%s</h3><p>%s</p>', 'Filings with Data' , 'The following data are extracted from IRS Form 990 filings.  Click the year to see the entire filing.');
		$rtn .= '<table>';
		$rtn .= '<thead> <tr> <th>Tax Year</th> <th>Revenue</th> <th>Expenses</th> <th>Ending Assets</th> <th>Ending Liabilities</th> </tr> </thead>';
		foreach ($json->filings_with_data as $filing)
		{
			$rtn .= "<tr>";
			$rtn .= sprintf('<td><a href="%s">%s (pdf)</a></td>', $filing->pdf_url, $filing->tax_prd_yr);
			$rtn .= sprintf($td_right, number_format($filing->totrevenue) );
			$rtn .= sprintf($td_right, number_format($filing->totfuncexpns));
			$rtn .= sprintf($td_right, number_format($filing->totassetsend));
			$rtn .= sprintf($td_right, number_format($filing->totliabend));
			$rtn .= "</tr>";
		}
		$rtn .= '</table>';
	}

	if( !empty($json->filings_without_data) )
	{	
		$rtn .= sprintf('<h3>%s</h3><p>%s</p>', 'Filings without Data' , 'The following filings have not had data extracted.  Click the year to see the entire filing.');
		$rtn .= '<ul>';
		foreach ($json->filings_without_data as $filing)
		{
			$rtn .= sprintf('<li><a href="%s">%s (pdf)</a></li>', $filing->pdf_url, $filing->tax_prd_yr);
		}
		$rtn .= '</ul>';
	}

	$rtn .= sprintf('<h4>Data Provided By</h4><p>%s</p>', make_clickable(nl2br(($json->data_source))) );

	//return sprintf('<pre>%s</pre> %s', print_r($json, true), $rtn );

	return $rtn;
}
add_shortcode( 'civique_summary', 'omega_non_profit_shortcode' );

// Add Quicktags
function civique_np_shortcode() {

	if ( wp_script_is( 'quicktags' ) ) {
	?>
	<script type="text/javascript">
	QTags.addButton( 'civique_np_summary', 'IRS 990', '[civique_summary]', '', '', 'Insert non-profit IRS 990 statement summary.', 9 );
	</script>
	<?php
	}

}

// Hook into the 'admin_print_footer_scripts' action
add_action( 'admin_print_footer_scripts', 'civique_np_shortcode' );


/*
	omega_archive_pagination
	--
	displays pagination for the current archive
*/
if( !function_exists('omega_archive_pagination') ):

function omega_archive_pagination($pages = '', $range = 3)
{ 
	 $showitems = ($range * 2)+1; 
 
	 global $paged;
	 if(empty($paged)) $paged = 1;
 
	 if($pages == '')
	 {
		 global $wp_query;
		 $pages = $wp_query->max_num_pages;
		 if(!$pages)
		 {
			 $pages = 1;
		 }
	 }  
 
	 if(1 != $pages)
	 {
	 	echo '<div class="pagination">';

	 		echo sprintf('<div class="row next-prev"><div class="col-md-6">%s</div><div class="col-md-6">%s</div></div>',
	 			($paged > 1) ? get_previous_posts_link('Previous Page') : '<em>This is the first page</em>',
	 			($paged < $pages) ? get_next_posts_link('Next Page') : '<em>This is the last page</em>'
	 		);
		 echo sprintf('<span class="meta">Page&nbsp;%u&nbsp;of&nbsp;%u</span> ', $paged, $pages);

		 if($paged > 2 && $paged > $range+1 && $showitems < $pages)
		 {
			echo sprintf('<span class="inactive"><a href="%1$s">%2$s</a></span> ', get_pagenum_link(1), "&laquo;&nbsp;First");
		 }

		 if($paged > 1 && $showitems < $pages)
		 {
			echo sprintf('<span class="inactive"><a href="%1$s">%2$s</a></span> ', get_pagenum_link($paged - 1), "&lsaquo;&nbsp;Previous");
		 }
 
		 for ($i=1; $i <= $pages; $i++)
		 {
			 if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
			 {
				if ($paged == $i)
				{
					echo sprintf('<span class="%1$s">%2$u</span> ', 'active', $i);
				}
				else
				{
					echo sprintf('<span class="%1$s"><a href="%3$s">%2$u</a></span> ', 'inactive', $i, get_pagenum_link($i) );
				}
			 }
		 }
 
		 if ($paged < $pages && $showitems < $pages)
		{
			echo sprintf('<span class="inactive"><a href="%1$s">%2$s</a></span> ', get_pagenum_link($paged + 1), "Next&nbsp;&rsaquo;");
		}

		 if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages)
		 {
			echo sprintf('<span class="inactive"><a href="%1$s">%2$s</a></span> ', get_pagenum_link($pages), "Last&nbsp;&raquo;");
		 }

		 echo "</div>\n";
	 }
}

endif;