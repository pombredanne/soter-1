<?php
/**
 * Template for an HTML email button.
 *
 * @link https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design Border based button
 *
 * @package soter
 */

?><table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td align="center">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="center">
						<table border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td>
									<a href="<?php echo esc_url( $url ); ?>" class="button button--<?php echo esc_attr( $color ?: 'blue' ); ?>" target="_blank">
										<?php echo esc_html( $text ); ?>
									</a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
