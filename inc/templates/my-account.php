<form class="woocommerce-EditAccountForm edit-account bwcpp-my-account" action="" method="post" enctype="multipart/form-data">
	<h3><?php _e( 'Profile Pictures', BWCPP_TEXT_DOMAIN ); ?></h3>

	<?php
	$user_pictures   = new \BWCPP\User_Pictures();
	$pictures        = $user_pictures->get_pictures();
	$primary_picture = $user_pictures->get_primary();
	?>

	<fieldset>
		<legend><?php _e( 'Choose your primary picture', BWCPP_TEXT_DOMAIN ); ?></legend>

		<p><?php _e( 'Click to choose a picture, then click "Save Changes"', BWCPP_TEXT_DOMAIN ); ?></p>

		<div class="bwcpp-pictures">
			<?php foreach ( $pictures as $picture ) : ?>
				<label>
					<input type="radio" name="primary_picture" value="<?php echo $picture['id']; ?>" <?php checked( $primary_picture, $picture['id'] ); ?>>
					<img src="<?php echo $picture['url']; ?>">
				</label>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Upload new pictures', BWCPP_TEXT_DOMAIN ); ?></legend>

		<p class="woocommerce-form-row form-row form-row-wide">
			<label for="wcpp_upload_profile_picture"><?php _e( 'Select pictures from your computer, then click "Save Changes"', BWCPP_TEXT_DOMAIN ); ?></label>
			<input type="file" id="wcpp_upload_profile_picture" name="pictures[]" multiple accept=".jpg,.png,.jpeg">
		</p>
	</fieldset>

	<?php wp_nonce_field( $this->save_pictures_action, $this->save_pictures_nonce ); ?>

	<input type="hidden" name="action" value="<?php echo $this->save_pictures_action; ?>">

	<p>
		<input type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php _e( 'Save Changes', BWCPP_TEXT_DOMAIN ); ?>">
	</p>
</form>
