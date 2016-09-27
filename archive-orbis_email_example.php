<?php get_header(); ?>

<div class="panel">
	<?php get_template_part( 'templates/search_form' ); ?>

	<?php if ( have_posts() ) : ?>

		<div class="table-responsive">
			<table class="table table-striped table-condense table-hover">
				<thead>
					<tr>
						<th><?php _e( 'Title'  , 'orbis' ); ?></th>
						<th><?php _e( 'Content'  , 'orbis' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php while ( have_posts() ) : the_post(); ?>

						<tr id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<td>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</td>
							<td>
								<?php the_content(); ?>
							</td>
							<td>
								<div class="actions">
									<div class="nubbin">
										<?php orbis_edit_post_link(); ?>
									</div>
								</div>
							</td>
						</tr>
	
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>

	<?php else : ?>

		<div class="content">
			<p class="alt">
				<?php _e( 'No results found.', 'orbis' ); ?>
			</p>
		</div>

	<?php endif; ?>
</div>

<?php orbis_content_nav(); ?>

<?php get_footer(); ?>
