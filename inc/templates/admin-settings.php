<?php
/**
 * @package bwcpp
 */
?>
<div class="wrap">
	<h2><?php _e( 'Better WC Profile Pictures Settings', BWCPP_TEXT_DOMAIN ); ?></h2>

	<form method="post" action="options.php">
		<?php settings_fields( 'bwcpp_settings' ); ?>
		<?php do_settings_sections( 'bwcpp_settings' ); ?>

		<?php submit_button(); ?>
	</form>
</div>
