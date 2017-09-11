<?php
/**
 * Template for vulnerable site HTML email notiication.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo esc_html( $count ); ?> <?php echo esc_html( $label ); ?> Detected!</title>
	</head>
	<body style="-webkit-text-size-adjust: none; box-sizing: border-box; color: #74787E; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; height: 100%; line-height: 1.4; margin: 0; width: 100% !important;" bgcolor="#F2F4F6">
		<style type="text/css">
			body {
				width: 100% !important; height: 100%; margin: 0; line-height: 1.4; background-color: #F2F4F6; color: #74787E; -webkit-text-size-adjust: none;
			}
			@media only screen and (max-width: 600px) {
				.email-body_inner {
					width: 100% !important;
				}
				.email-footer {
					width: 100% !important;
				}
			}
			@media only screen and (max-width: 500px) {
				.button {
					width: 100% !important;
				}
			}
		</style>

		<span class="preheader" style="box-sizing: border-box; display: none !important; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
			Vulnerabilities were detected on <?php echo esc_html( $site_name ); ?>. We've included some details to help you fix the problem.
		</span>

		<table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;" bgcolor="#F2F4F6">
			<tr>
				<td align="center" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
					<table class="email-content" width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;">
						<tr>
							<td class="email-masthead" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 25px 0;" align="center">
								<a href="https://example.com" class="email-masthead_name" style="box-sizing: border-box; color: #bbbfc3; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 16px; font-weight: bold; text-decoration: none; text-shadow: 0 1px 0 white;">
									[<?php echo esc_html( $site_name ); ?>] Soter Security Digest
								</a>
							</td>
						</tr>

						<tr>
							<td class="email-body" width="100%" cellpadding="0" cellspacing="0" style="-premailer-cellpadding: 0; -premailer-cellspacing: 0; border-bottom-color: #EDEFF2; border-bottom-style: solid; border-bottom-width: 1px; border-top-color: #EDEFF2; border-top-style: solid; border-top-width: 1px; box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;" bgcolor="#FFFFFF">
								<table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin: 0 auto; padding: 0; width: 570px;" bgcolor="#FFFFFF">
									<tr>
										<td class="content-cell" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 35px;">
											<h1 style="box-sizing: border-box; color: #2F3133; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 19px; font-weight: bold; margin-top: 0;" align="left">
												<?php echo esc_html( $count ); ?> <?php echo esc_html( $label ); ?> Detected!
											</h1>
											<p style="box-sizing: border-box; color: #74787E; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 16px; line-height: 1.5em; margin-top: 0;" align="left">
												A recent scan by the Soter security check plugin flagged <?php echo esc_html( $count ); ?> <?php echo esc_html( $label ); ?> on your WordPress site.
											</p>
											<p style="box-sizing: border-box; color: #74787E; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 16px; line-height: 1.5em; margin-top: 0;" align="left">
												Please ensure your WordPress install, plugins and themes are all up-to-date from your dashboard:
											</p>
											<table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin: 30px auto; padding: 0; text-align: center; width: 100%;">
												<tr>
													<td align="center" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
														<table width="100%" border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
															<tr>
																<td align="center" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
																	<table border="0" cellspacing="0" cellpadding="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
																		<tr>
																			<td style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
																				<a href="<?php echo esc_url( $action_url ); ?>" class="button button--" target="_blank" style="-webkit-text-size-adjust: none; background: #3869D4; border-color: #3869d4; border-radius: 3px; border-style: solid; border-width: 10px 18px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); box-sizing: border-box; color: #FFF; display: inline-block; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; text-decoration: none;">
																					Go To Dashboard
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

											<p style="box-sizing: border-box; color: #74787E; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 16px; line-height: 1.5em; margin-top: 0;" align="left">
												For reference, here are the details of the flagged vulnerabilities:
											</p>

											<?php foreach ( $messages as $message ) : ?>
												<table class="attributes" width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin: 0 0 21px;">
													<tr>
														<td class="attributes_content" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 16px;" bgcolor="#EDEFF2">
															<table width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
																<tr>
																	<td class="attributes_item" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 0;">
																		<strong style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
																			<?php echo esc_html( $message['title'] ); ?>
																		</strong>
																	</td>
																</tr>
																<?php foreach ( $message['links'] as $url => $host ) : ?>
																	<tr>
																		<td class="attributes_item" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 0;">
																			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $host ); ?></a>
																		</td>
																	</tr>
																<?php endforeach ?>
																<?php foreach ( $message['meta'] as $meta ) : ?>
																	<tr>
																		<td class="attributes_item" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 0;">
																			<?php echo esc_html( $meta ); ?>
																		</td>
																	</tr>
																<?php endforeach ?>
															</table>
														</td>
													</tr>
												</table>
											<?php endforeach ?>

											<table class="body-sub" style="border-top-color: #EDEFF2; border-top-style: solid; border-top-width: 1px; box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin-top: 25px; padding-top: 25px;">
												<tr>
													<td style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
														<p class="sub" style="box-sizing: border-box; color: #74787E; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 12px; line-height: 1.5em; margin-top: 0;" align="left">
															If you’re having trouble with the button above, copy and paste the URL below into your web browser.
														</p>
														<p class="sub" style="box-sizing: border-box; color: #74787E; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 12px; line-height: 1.5em; margin-top: 0;" align="left">
															<?php echo esc_html( $action_url ); ?>
														</p>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
								<table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; margin: 0 auto; padding: 0; text-align: center; width: 570px;">
									<tr>
										<td class="content-cell" align="center" style="box-sizing: border-box; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; padding: 35px;">
											<p class="sub align-center" style="box-sizing: border-box; color: #AEAEAE; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 12px; line-height: 1.5em; margin-top: 0;" align="center">
												Site check performed by the <a href="https://github.com/ssnepenthe/soter">Soter</a> plugin.
												<br>Powered by the <a href="https://wpvulndb.com/">WPScan Vulnerability Database</a> API.
											</p>
											<p class="sub align-center" style="box-sizing: border-box; color: #AEAEAE; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 12px; line-height: 1.5em; margin-top: 0;" align="center">
												This plugin does not verify the integrity of files on your site.
												<br>It only checks installed packages against a list of known vulnerabilities.
											</p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>
