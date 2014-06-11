<?php

//
//	front-page.php
//	--
//	a blog front-end that makes use of the front-page
//

?><?php get_header(); ?>
<?php if( get_header_image() != "") : ?>
<section class="stripe stripe-image-cover">
	<div class="image-cover" style="background-image: url('<?php header_image() ; ?>')">
		<div class="container">
			<div class="row">
				<div class="col-md-8">
					<div class="height-100 position-relative">
						<div class="panel bottom">
							<h2><?php bloginfo('name'); ?></h2>
							<p><?php bloginfo('description'); ?></p>
						</div>
					</div>
				</div>
				<div class="col-md-4 hide-sm">
					<div class="height-100 position-relative">
						<div class="panel bottom">
							<h3>Welcome</h3>
							<ul class="minimize">
								<?php wp_nav_menu( array('theme_location' => 'front-page-quick-menu', 'container' => false, 'items_wrap' => '%3$s') ); ?>
							</ul>
							<?php if( get_theme_mod('social_twitter') || get_theme_mod('social_facebook') ) : ?>
							<ul class="social">
								<?php if( ($twitter = get_theme_mod('social_twitter', null) ) && $twitter != null)
										echo sprintf('<li><a href="https://twitter.com/%1$s" class="twitter-follow-button" data-show-count="false">Follow @%1$s</a></li>', $twitter); ?>
								<?php if( ($facebook = get_theme_mod('social_facebook', null) ) && $facebook != null)
									echo sprintf('<li><div class="fb-like" data-href="%s" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div></li>', $facebook); ?>
							</ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</section>
<?php endif; ?>
<section class="stripe stripe-blog padded">
	<div class="container">
		<div class="col-md-8">
			<h2>News and Updates</h2>
			<?php get_template_part('loop'); ?>
		</div>
		<div class="col-md-4">
			<ul class="sidebar">
				<?php dynamic_sidebar('right-sidebar'); ?>
			</ul>
		</div>

	</div>
</section>

<?php get_footer(); ?>