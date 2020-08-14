<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="row">
			<div class="col-md-8">
				<?php do_action( 'orbis_before_main_content' ); ?>

				<div class="panel">
					<header>
						<h3><?php _e( 'Description', 'orbis_pronamic' ); ?></h3>
					</header>

					<div class="content clearfix">
						<?php if ( has_post_thumbnail() ) : ?>
				
							<div class="thumbnail">
								<?php the_post_thumbnail( 'thumbnail' ); ?>
							</div>

						<?php endif; ?>

						<?php the_content(); ?>
					</div>
				</div>

				<?php do_action( 'orbis_after_main_content' ); ?>

				<?php get_template_part( 'orbis_subscription_timesheet' ); ?>

				<?php comments_template( '', true ); ?>
			</div>

			<div class="col-md-4">
				<?php do_action( 'orbis_before_side_content' ); ?>

				<div class="panel">
					<header>
						<h3><?php _e( 'Additional Information', 'orbis_pronamic' ); ?></h3>
					</header>

					<div class="content">
						<dl>
							<dt><?php _e( 'Posted on', 'orbis_pronamic' ); ?></dt>
							<dd><?php echo get_the_date(); ?></dd>

							<dt><?php _e( 'Posted by', 'orbis_pronamic' ); ?></dt>
							<dd><?php echo get_the_author(); ?></dd>

							<?php

							$edit_post_link = get_edit_post_link();

							if ( null !== $edit_post_link ) :

							?>

								<dt><?php _e( 'Actions', 'orbis_pronamic' ); ?></dt>
								<dd><?php edit_post_link( __( 'Edit', 'orbis_pronamic' ) ); ?></dd>

							<?php endif; ?>
						</dl>
					</div>
				</div>

				<?php do_action( 'orbis_after_side_content' ); ?>
			</div>
		</div>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>
