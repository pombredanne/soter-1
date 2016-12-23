<?php
/**
 * Don't mess with the spacing/indentation!
 */

?>Vulnerabilities were detected on <?php echo esc_html( $site_name ) ?>. We've included some details to help you fix the problem.

[<?php echo esc_html( $site_name ) ?>] Security Digest

******************
<?php echo esc_html( $count ) ?> Vulnerabilities Detected!
******************

A recent scan by the Soter security check plugin flagged <?php echo esc_html( $count ) ?> vulnerabilities on your WordPress site.

Please ensure your WordPress install as well as all plugins and themes are up-to-date from your dashboard:

Go To Dashboard ( <?php echo $action_url ?> )

For reference, here are the details of the flagged vulnerabilities:

<?php foreach ( $messages as $message ) : ?>
<?php echo $message['title'] ?>

<?php foreach ( $message['links'] as $url => $host ) : ?>
<?php echo $url ?>

<?php endforeach ?>
<?php echo implode( ' | ', $message['meta'] ) ?>


<?php endforeach ?>
