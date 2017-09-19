<?php
/*
 * Content display template, used for both single and index/category/search pages.
 * Iconic One uses custom excerpts on search, home, category and tag pages.
 * File Last updated: Iconic One 1.7.2
 */
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( is_sticky() && is_home() && ! is_paged() ) : // for top sticky post with blue border ?>
		<div class="featured-post">
			<?php _e( 'Featured Article', 'iconic-one' ); ?>
		</div>
		<?php endif; ?>
		<header class="entry-header">
			<?php if ( is_single() ) : ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<?php else : ?>
			<h2 class="entry-title">
				<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'iconic-one' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
			<?php endif; // is_single() ?>
					<?php if ( is_single() || ( get_theme_mod( 'iconic_one_date_home' ) == '1' ) ): //for date on single page ?>	
		<div class="below-title-meta">
		<div class="adt">
		<?php _e('By','iconic-one'); ?>
        <span class="vcard author">
			<span class="fn"><?php echo the_author_posts_link(); ?></span>
        </span>
        <span class="meta-sep">|</span> 
			<span class="date updated"><?php echo get_the_date(); ?></span>		 
        </div>
		<div class="adt-comment">
		<a class="link-comments" href="<?php  comments_link(); ?>"><?php comments_number(__('0 Comment','iconic-one'),__('1 Comment','iconic-one'),__('% Comments','iconic-one')); ?></a> 
        </div>       
		</div><!-- below title meta end -->
			
			<?php endif; // display meta-date on single page() ?>
			
		</header><!-- .entry-header -->

		<?php if ( is_home() && ( get_theme_mod( 'iconic_one_full_post' , '1' ) == '1' ) ) : // Check Live Customizer for Full/Excerpts Post Settings ?>
			<?php iconic_one_excerpts() ?>	
				<?php elseif( is_search() || is_category() || is_tag() || is_author() || is_archive()  ): ?>
					<?php iconic_one_excerpts() ?>	
				<?php else : ?>
					<div class="entry-content">
						<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'iconic-one' ) ); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'iconic-one' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->
		<?php endif; ?>

	<footer class="entry-meta">
		<?php if ( is_home() && ( get_theme_mod( 'iconic_one_catg_home' , '1' ) == '1' ) ) : ?>
			<span><?php _e('Category:','iconic-one'); ?> <?php the_category(' '); ?></span>
		<?php elseif( !is_home() ): ?>
			<span><?php _e('Category:','iconic-one'); ?> <?php the_category(' '); ?></span>
		<?php endif; ?>
		<?php if ( is_home() && ( get_theme_mod( 'iconic_one_tag_home' , '1' ) == '1' ) ) : ?>
				<span><?php the_tags(); ?></span>
		<?php elseif( !is_home() ): ?>
				<span><?php the_tags(); ?></span>
		<?php endif; ?>	
           	<?php edit_post_link( __( 'Edit', 'iconic-one' ), '<span class="edit-link">', '</span>' ); ?>
			<?php if ( is_singular() && get_the_author_meta( 'description' ) && is_multi_author() ) : // If a user has filled out their description and this is a multi-author blog, show a bio on their entries. ?>
				<div class="author-info">
					<div class="author-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'themonic_author_bio_avatar_size', 68 ) ); ?>
					</div><!-- .author-avatar -->
					<div class="author-description">
						<h2><?php printf( __( 'About %s', 'iconic-one' ), get_the_author() ); ?></h2>
						<p><?php the_author_meta( 'description' ); ?></p>
						<div class="author-link">
							<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
								<?php printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'iconic-one' ), get_the_author() ); ?>
							</a>
						</div><!-- .author-link	-->
					</div><!-- .author-description -->
				</div><!-- .author-info -->
			<?php endif; ?>
		</footer><!-- .entry-meta -->
	</article><!-- #post -->
