<?php
/*
	admin-settings.php
	--
	contains the code that generates and processes site options for The Library
*/
?>

<?php 

function print_text_setting_row($label, $field_name, $description = null )
{ ?>
	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr($field_name); ?>"><?php echo $label; ?></label>
		</th>
		<td>
			<input name="<?php echo esc_attr($field_name); ?>" type="text" id="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr( get_option($field_name) ); ?>" class="regular-text" />
			<?php if( !empty($description) ) { ?><p class="description"><?php echo htmlspecialchars( $description ); ?></p><?php } ?>
		</td>
	</tr>
<?php

}

function print_list_setting_row($label, $field_name, $values, $description = null)
{ ?>
	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr($field_name); ?>"><?php echo $label; ?></label>
		</th>
		<td>
			<select id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>">
				<?php
					$current = get_option($field_name);
					foreach($values as $value => $label)
					{
						echo sprintf('<option value="%1$s"%3$s>%2$s</option>',
							esc_attr($value),
							htmlspecialchars($label),
							( $value == $current ) ? ' selected="selected"' : ""
						);
					}
				?>
			</select>
			<?php if( !empty($description) ) { ?><p class="description"><?php echo $description; ?></p><?php } ?>
		</td>
	</tr>
<?php }

foreach ( get_pages() as $page )
{
	$pages[$page->post_name] = $page->post_title;
}

?>

<div class="wrap">

	<h1><?php bloginfo('name'); ?></h1>
	<p>This page will let you configure some custom settings for your site.</p>

	<form method="post" action="options.php">

		<?php settings_fields('lhs-settings'); ?>

		<h2>Front Page</h2>
		<table class="form-table">

		</table>

		<h3>Location</h3>
		<table class="form-table">
			<?php print_text_setting_row( 'Address 1', 'lhs_address_1' ); ?>
			<?php print_text_setting_row( 'Address 2', 'lhs_address_2' ); ?>
			<?php print_text_setting_row( 'City', 'lhs_city' ); ?>
			<?php print_text_setting_row( 'State', 'lhs_state' ); ?>
			<?php print_text_setting_row( 'Zip Code', 'lhs_zip_code' ); ?>
		</table>

		<h3>Basic</h3>
		<table class="form-table">
			<?php print_text_setting_row( 'Phone Number', 'lhs_phone_number',
				'This is the unformatted phone number for this location.  Omit country code.  10 digits exactly.  For example "3075551212"'); ?>
		</table>

		<h3>Non-Profit</h3>
		<table class="form-table">
			<?php print_text_setting_row( 'EIN', 'omega_ein',
				'This is your organization\'s EIN.  You can display information about your non-profit using the [omega_non_profit] shortcode.'); ?>
		</table>

		<h3>Social</h3>
		<table class="form-table">
			<?php print_text_setting_row( 'Facebook URL', 'lhs_facebook', 
				'The full link to this location\'s Facebook Page'); ?>
			<?php print_text_setting_row( 'Twitter Username', 'lhs_twitter', 
				'The username for the location\'s Twitter Profile.  No @-sign.') ?>
		</table>

		<h3 class="title">Intro Text</h3>
		<p>This appears on the home page under the "Welcome to The Library" header.</p>
		<textarea id="lhs_home_intro" class="large-text code" rows="7" name="lhs_home_intro"><?php echo htmlspecialchars( get_option('lhs_home_intro') ); ?></textarea>		

		<h2 class="title">Advanced</h2>
		<h3 class="title">Custom CSS</h3>
		<p>Place custom CSS code here.  This allows you to adjust template presentation without logging into the server.</p>
		<textarea id="lhs_custom_css" class="large-text code" rows="10" name="lhs_custom_css"><?php echo htmlspecialchars( get_option('lhs_custom_css') ); ?></textarea>


		<?php submit_button(); ?>

	</form>

</div>