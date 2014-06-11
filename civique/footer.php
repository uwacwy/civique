<section class="stripe stripe-footer padded stripe-rule">
	<div class="container">
		<div class="row">
			<div class="col-md-3">
				<?php
					echo sprintf('<p><a href="%s"><img src="%s" title="%s logo"></a></p>',
						get_bloginfo('url'),
						get_theme_mod('header_logo') ? get_theme_mod('header_logo') : get_stylesheet_directory_uri() . '/css/img/brand_logo.png',
						get_bloginfo('name')
					);
				?>
				<address>
					<strong><?php bloginfo('name'); ?></strong><br>
					<?php echo h( get_theme_mod('location_address_1')); ?>
					<?php echo ( get_theme_mod('location_address_2') != "" )? '<br>'.h(get_theme_mod('location_address_2') ) : '' ?><br>
					<?php echo h(sprintf('%s, %s %s', get_theme_mod('location_city'), get_theme_mod('location_state'), get_theme_mod('location_zipcode'))); ?>
				</address>
				<?php 
					if ( get_theme_mod('contact_phone') != "" )
						echo sprintf('<p>(%s) %s-%s</p>', 
							substr(get_theme_mod('contact_phone'), 0, 3),
							substr(get_theme_mod('contact_phone'), 3, 3),
							substr(get_theme_mod('contact_phone'), 6, 4)
						);
				?>
				<p><?php wp_loginout(); ?></p>
			</div>
			<div class="col-md-9">				
				<h3><?php bloginfo('name'); ?></h3>
				<p><?php bloginfo('description'); ?></p>
				<ul class="inline">
					<?php wp_nav_menu( array('theme_location' => 'footer-menu', 'container' => false, 'items_wrap' => '%3$s') ); ?>
				</ul>
				<hr>
				<?php if( get_theme_mod('social_twitter') || get_theme_mod('social_facebook') ) : ?>
				<ul class="social">
					<?php if( ($twitter = get_theme_mod('social_twitter', null) ) && $twitter != null)
						echo sprintf('<li><a href="https://twitter.com/%1$s" class="twitter-follow-button" data-show-count="false">Follow @%1$s</a></li>', $twitter); ?>
					<?php if( ($facebook = get_theme_mod('social_facebook', null) ) && $facebook != null)
						echo sprintf('<li><div class="fb-like" data-href="%s" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div></li>', $facebook); ?>
				</ul>
				<?php endif; ?>
				<p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></p>
				<p>Powered by <a href="http://www.wordpress.org">WordPress</a> and Civique for WordPress</p>
			</div>
		</div>
	</div>
</section>
<?php wp_footer(); ?>
</body>

</html>