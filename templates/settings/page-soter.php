<div class="wrap">
	<h1>Soter Configuration</h1>
	<form action="options.php" method="post">

		<?php settings_fields( 'soter_settings_group' ); ?>

		<?php do_settings_sections( 'soter' ); ?>

		<p class="submit">
			<input class="button button-primary" id="submit" name="submit" type="submit" value="Save Changes">
		</p>

	</form>
</div>
