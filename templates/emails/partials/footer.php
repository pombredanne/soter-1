<?php
/**
 * Template for the HTML email footer.
 *
 * @package soter
 */

?><tr>
	<td>
		<table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0">
			<tr>
				<td class="content-cell" align="center">
					<p class="sub align-center">
						Scan performed by the <a href="<?php echo esc_url( $plugin_url ); ?>">Soter Plugin</a> against the <a href="https://wpvulndb.com/">WPScan Vulnerability Database</a> API.
					</p>
					<p class="sub align-center">
						DISCLAIMER: Soter does not verify the integrity of individual packages on your site - It checks installed packages by name and version against a list of known vulnerabilities.
					</p>
				</td>
			</tr>
		</table>
	</td>
</tr>
