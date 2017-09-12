<?php $this->layout( 'emails/layout.php' ); ?>

<?php $this->start( 'title' ); ?>
	Error Checking For Vulnerabilities
<?php $this->stop(); ?>

<?php $this->start( 'preheader' ); ?>
	There was an HTTP error while checking <?php echo esc_html( $site_name ); ?> for vulnerabilities. We've included some details to help you fix the problem.
<?php $this->stop(); ?>

<?php $this->start( 'body-content' ); ?>
	<h1>Error!</h1>
	<p>
		A recent scan by the Soter plugin could not be completed due to an HTTP error with the message:
	</p>
	<p>
		<strong><?php echo esc_html( $message ); ?></strong>
	</p>
	<p>
		Please notify your site admin.
	</p>
<?php $this->stop(); ?>
