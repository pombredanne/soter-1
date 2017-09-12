<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<?php if ( $this->section( 'title' ) ) : ?>
			<title><?php echo $this->section( 'title' ); ?></title>
		<?php endif; ?>
	</head>
	<body>
		<style type="text/css">
			@media only screen and (max-width: 600px) {
				.email-body_inner,
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

		<?php if ( $this->section( 'preheader' ) ) : ?>
			<span class="preheader">
				<?php echo $this->section( 'preheader' ); ?>
			</span>
		<?php endif; ?>

		<table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td align="center">
					<table class="email-content" width="100%" cellpadding="0" cellspacing="0">
						<!-- Header -->
						<?php $this->insert( 'emails/partials/header.php' ); ?>

						<!-- Body -->
						<tr>
							<td class="email-body" width="100%" cellpadding="0" cellspacing="0">
								<table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0">

									<!-- Body content -->
									<tr>
										<td class="content-cell">
											<?php echo $this->section( 'body-content' ); ?>

											<!-- Sub copy -->
											<?php if ( $this->section( 'body-sub' ) ) : ?>
												<table class="body-sub">
													<tr>
														<td>
															<?php echo $this->section( 'body-sub' ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>
										</td>
									</tr>
								</table>
							</td>
						</tr>

						<!-- Footer -->
						<?php $this->insert( 'emails/partials/footer.php' ); ?>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>
