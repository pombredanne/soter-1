<?php $this->layout( 'emails/layout.php' ); ?>

<?php $this->start( 'title' ); ?>
	Vulnerabilities Detected
<?php $this->stop(); ?>

<?php $this->start( 'preheader' ); ?>
	Vulnerabilities were detected on <?php echo esc_html( $site_name ); ?>. We've included some details to help you fix the problem.
<?php $this->stop(); ?>

<?php $this->start( 'body-content' ); ?>
	<h1>Vulnerabilities Detected!</h1>
	<p>
		A recent scan by the Soter plugin flagged one or more vulnerabilities on your site.
	</p>
	<p>
		Please ensure WordPress as well as all plugins and themes are up-to-date from your dashboard.
	</p>

	<?php echo $this->button()->blue( 'Go To Dashboard', $action_url ); ?>

	<p>
		For reference, here are the details of the detected vulnerabilities:
	</p>

	<?php foreach ( $vulnerabilities as $vulnerability ) : ?>
		<?php $this->insert( 'emails/partials/vulnerability-card.php', compact( 'vulnerability' ) ); ?>
	<?php endforeach; ?>
<?php $this->stop(); ?>

<?php $this->start( 'body-sub' ); ?>
	<?php echo $this->button()->fallback( $action_url ); ?>
<?php $this->stop(); ?>
