<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// @todo Move to external stylesheet?
?><style>.soter-danger { color: #a00; } .soter-meta:not(:first-child):before { content: " | "; }</style>
<div class="notice notice-warning">
	<h2><?php echo esc_html( $count ) ?> <?php echo esc_html( $label ) ?> detected!</h2>
	<?php foreach ( $vulnerabilities as $vulnerability ) : ?>
		<p>
			<strong><?php echo esc_html( $vulnerability['title'] ) ?></strong>
		</p>
		<p>
			<?php if ( $vulnerability['published'] ) : ?>
				<span class="soter-meta">
					Published <?php echo esc_html( $vulnerability['published']->format( 'd M Y' ) ) ?>
				</span>
			<?php endif; ?>

			<?php if ( $vulnerability['fixed_in'] ) : ?>
				<span class="soter-meta">
					Fixed in v<?php echo esc_html( $vulnerability['fixed_in'] ) ?>
				</span>
			<?php else : ?>
				<span class="soter-meta soter-danger">
					Not fixed yet
				</span>
			<?php endif; ?>

			<span class="soter-meta">
				<a
					href="https://wpvulndb.com/vulnerabilities/<?php echo esc_attr( $vulnerability['id'] ) ?>"
					target="_blank"
				>
					More Info
				</a>
			</span>
		</p>
	<?php endforeach ?>
</div>
