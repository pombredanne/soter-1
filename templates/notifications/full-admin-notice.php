<?php // @todo Don't print styles here! ?>
<style>.soter-danger { color: #a00; } .soter-meta:not(:first-child):before { content: " | "; }</style>
<div class="notice notice-warning">
	<h2><?php echo $count ?> <?php echo $label ?> detected!</h2>
	<?php foreach ( $messages as $message ) : ?>
		<p><strong><?php echo esc_html( $message['title'] ) ?></strong></p>
		<p>
			<?php foreach ( $message['meta'] as $meta ) : ?>
				<span class="soter-meta<?php echo false !== strpos( $meta, 'Not fixed' ) ? ' soter-danger' : '' ?>"><?php echo esc_html( $meta ) ?></span>
			<?php endforeach ?>

			<?php foreach ( $message['links'] as $url => $label ) : ?>
				<span class="soter-meta">
					<a href="<?php echo esc_attr( $url ) ?>" target="_blank"><?php echo esc_html( $label ) ?></a>
				</span>
			<?php endforeach ?>
		</p>
	<?php endforeach ?>
</div>
