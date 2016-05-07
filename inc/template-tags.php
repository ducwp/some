<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Some
 */

/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function some_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( 'Posted on %s', 'post date', 'some' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	$byline = sprintf(
		esc_html_x( 'by %s', 'post author', 'some' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

}

/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function some_entry_footer() {
	// Hide category and tag text for pages.
	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( esc_html__( ', ', 'some' ) );
		if ( $categories_list && some_categorized_blog() ) {
			printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'some' ) . '</span>', $categories_list ); // WPCS: XSS OK.
		}

		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', esc_html__( ', ', 'some' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'some' ) . '</span>', $tags_list ); // WPCS: XSS OK.
		}
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		/* translators: %s: post title */
		comments_popup_link( sprintf( wp_kses( __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'some' ), array( 'span' => array( 'class' => array() ) ) ), get_the_title() ) );
		echo '</span>';
	}

	edit_post_link(
		sprintf(
			/* translators: %s: Name of current post */
			esc_html__( 'Edit %s', 'some' ),
			the_title( '<span class="screen-reader-text">"', '"</span>', false )
		),
		'<span class="edit-link">',
		'</span>'
	);
}

/**
 * Return SVG markup.
 *
 * @param  array  $args {
 *     Parameters needed to display an SVG.
 *
 *     @param string $icon Required. Use the icon filename, e.g. "facebook-square".
 *     @param string $title Optional. SVG title, e.g. "Facebook".
 *     @param string $desc Optional. SVG description, e.g. "Share this post on Facebook".
 * }
 * @return string SVG markup.
 */
function some_get_svg( $args = array() ) {

	// Make sure $args are an array.
	if ( empty( $args ) ) {
		return esc_html__( 'Please define default parameters in the form of an array.', 'some' );
	}
	
	// Define an icon.
	if ( false === array_key_exists( 'icon', $args ) ) {
		return esc_html__( 'Please define an SVG icon filename.', 'some' );
	}
	
	// Set defaults.
	$defaults = array(
		'icon'        => '',
		'title'       => '',
		'desc'        => '',
		'aria_hidden' => true, // Hide from screen readers.
	);
	
	// Parse args.
	$args = wp_parse_args( $args, $defaults );
	
	// Set aria hidden.
	if ( true === $args['aria_hidden'] ) {
		$aria_hidden = ' aria-hidden="true"';
	} else {
		$aria_hidden = '';
	}
	
	// Set ARIA.
	if ( $args['title'] && $args['desc'] ) {
		$aria_labelledby = ' aria-labelledby="title desc"';
	} else {
		$aria_labelledby = '';
	}
	
	// Begin SVG markup
	$svg = '<svg class="icon icon-' . esc_html( $args['icon'] ) . '"' . $aria_hidden . $aria_labelledby . ' role="img">';
		// If there is a title, display it.
		if ( $args['title'] ) {
			$svg .= '	<title>' . esc_html( $args['title'] ) . '</title>';
		}
		// If there is a description, display it.
		if ( $args['desc'] ) {
			$svg .= '	<desc>' . esc_html( $args['desc'] ) . '</desc>';
		}
	$svg .= '	<use xlink:href="' . get_template_directory_uri() . '/assets/images/svg-icons.svg'. '#icon-' . esc_html( $args['icon'] ) . '"></use>';
	$svg .= '</svg>';
	return $svg;
}
/**
 * Display an SVG.
 *
 * @param  array  $args  Parameters needed to display an SVG.
 */
function some_do_svg( $args = array() ) {
	echo some_get_svg( $args );
}

/**
 * Displays the optional custom logo.
 *
 * Does nothing if the custom logo is not available.
 *
 */
function some_the_custom_logo() {
	
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
	
}

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function some_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'some_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'some_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so some_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so some_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in some_categorized_blog.
 */
function some_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'some_categories' );
}
add_action( 'edit_category', 'some_category_transient_flusher' );
add_action( 'save_post',     'some_category_transient_flusher' );
