<?php
/**
 * Breadcrumb navigation
 *
 * Not all pages will have a `the_title` value; these can be defined here.
 * 1. Home, the first item in a breadcrumb list
 * 2. A Category archive
 * 3. A Taxonomy archive
 * 4. The Search results page
 * 5. A Tag archive
 * 6. An Author archive
 * 7. The 404 error page
 *
 * Other constants
 * 8. show current post/page title in breadcrumbs
 * 9. show breadcrumbs on the homepage
 * 10. delimiter between crumbs
 * 11. tag before the current crumb
 * 12. tag after the current crumb
 *
 * @link http://dimox.net/wordpress-breadcrumbs-without-a-plugin/
 * @return string link hierarchy
 */
function om_the_breadcrumbs() {
	$text['home']     = 'Home'; // [1]
	$text['category'] = 'Archive by Category "%s"'; // [2]
	$text['tax']      = 'Archive for "%s"'; // [3]
	$text['search']   = 'Search Results for "%s" Query'; // [4]
	$text['tag']      = 'Posts Tagged "%s"'; // [5]
	$text['author']   = 'Articles Posted by %s'; // [6]
	$text['404']      = 'Error 404'; // [7]

	$show_current = true; // [8]
	$show_on_home = false; // [9]
	$separator    = ' &raquo; '; // [10]
	$pre_current  = '<span class="current">'; // [11]
	$post_current = '</span>'; // [12]


	global $post;

	$home_url = get_bloginfo( 'url' ) . '/';
	$pre_link = '<span typeof="v:Breadcrumb">';
	$post_link = '</span>';
	$link_attr = ' rel="v:url" property="v:title"';
	$link = $pre_link . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $post_link;


	if ( is_home() || is_front_page() ) {

		if ( $show_on_home ) {

			echo '<div id="crumbs"><a href="' . $home_url . '">' . $text['home'] . '</a></div>';

		}

	} else {

		echo '<div id="crumbs" xmlns:v="http://rdf.data-vocabulary.org/#">' . sprintf( $link, $home_url, $text['home'] ) . $separator;

		if ( is_category() ) {

			$this_cat = get_category( get_query_var( 'cat' ), false );

			if ( $this_cat->parent != 0 ) {

				$cats = get_category_parents( $this_cat->parent, TRUE, $separator );
				$cats = str_replace( '<a', $pre_link . '<a' . $link_attr, $cats );
				$cats = str_replace( '</a>', '</a>' . $post_link, $cats );
				echo $cats;

			}

			echo $pre_current . sprintf( $text['category'], single_cat_title( '', false ) ) . $post_current;

		} elseif ( is_tax() ) {

			$this_cat = get_category( get_query_var( 'cat' ), false );

			if ( $this_cat->parent != 0 ) {

				$cats = get_category_parents( $this_cat->parent, TRUE, $separator );
				$cats = str_replace( '<a', $pre_link . '<a' . $link_attr, $cats );
				$cats = str_replace( '</a>', '</a>' . $post_link, $cats );
				echo $cats;

			}

			echo $pre_current . sprintf( $text['tax'], single_cat_title( '', false ) ) . $post_current;

		} elseif ( is_search() ) {

			echo $pre_current . sprintf( $text['search'], get_search_query() ) . $post_current;

		} elseif ( is_day() ) {

			echo sprintf( $link, get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) ) . $separator;
			echo sprintf( $link, get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ), get_the_time( 'F') ) . $separator;
			echo $pre_current . get_the_time( 'd' ) . $post_current;

		} elseif ( is_month() ) {

			echo sprintf( $link, get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) ) . $separator;
			echo $pre_current . get_the_time( 'F' ) . $post_current;

		} elseif ( is_year() ) {

			echo $pre_current . get_the_time( 'Y' ) . $post_current;

		} elseif ( is_single() && ! is_attachment() ) {

			if ( get_post_type() != 'post' ) {

				$post_type = get_post_type_object( get_post_type() );
				$slug = $post_type->rewrite;
				printf( $link, $home_url . '/' . $slug['slug'] . '/', $post_type->labels->singular_name );

				if ( 1 == $show_current ) {

					echo $separator . $pre_current . get_the_title() . $post_current;

				}

			} else {

				$cat = get_the_category(); $cat = $cat[0];
				$cats = get_category_parents( $cat, TRUE, $separator );

				if ( $show_current == 0) {

					$cats = preg_replace( "#^(.+)$separator$#", "$1", $cats );

				}

				$cats = str_replace( '<a', $pre_link . '<a' . $link_attr, $cats );
				$cats = str_replace( '</a>', '</a>' . $post_link, $cats );
				echo $cats;

				if ( $show_current == 1 ) {

					echo $pre_current . get_the_title() . $post_current;

				}

			}

		} elseif ( ! is_single() && ! is_page() && get_post_type() != 'post' && ! is_404() ) {

			$post_type = get_post_type_object( get_post_type() );
			echo $pre_current . $post_type->labels->singular_name . $post_current;

		} elseif ( is_attachment() ) {

			$parent = get_post( $post->post_parent );
			$cat = get_the_category( $parent->ID ); $cat = $cat[0];
			$cats = get_category_parents( $cat, TRUE, $separator );
			$cats = str_replace( '<a', $pre_link . '<a' . $link_attr, $cats );
			$cats = str_replace( '</a>', '</a>' . $post_link, $cats );
			echo $cats;
			printf( $link, get_permalink( $parent ), $parent->post_title );

			if ( $show_current == 1 ) {

				echo $separator . $pre_current . get_the_title() . $post_current;

			}

		} elseif ( is_page() && ! $post->post_parent ) {

			if ( $show_current == 1 ) {

				echo $pre_current . get_the_title() . $post_current;

			}

		} elseif ( is_page() && $post->post_parent ) {

			$parent_id  = $post->post_parent;
			$breadcrumbs = array();

			while ( $parent_id ) {
				$page = get_page( $parent_id );
				$breadcrumbs[] = sprintf( $link, get_permalink( $page->ID ), get_the_title( $page->ID ) );
				$parent_id  = $page->post_parent;
			}

			$breadcrumbs = array_reverse( $breadcrumbs );

			for ( $i = 0; $i < count( $breadcrumbs ); $i++ ) {
				echo $breadcrumbs[$i];

				if ( $i != count( $breadcrumbs ) - 1 ) {

					echo $separator;

				}
			}

			if ( $show_current ) {

				echo $separator . $pre_current . get_the_title() . $post_current;

			}

		} elseif ( is_tag() ) {

			echo $pre_current . sprintf( $text['tag'], single_tag_title( '', false ) ) . $post_current;

		} elseif ( is_author() ) {

	 		global $author;

			$userdata = get_userdata( $author );
			echo $pre_current . sprintf( $text['author'], $userdata->display_name ) . $post_current;

		} elseif ( is_404() ) {

			echo $pre_current . $text['404'] . $post_current;

		}

		if ( get_query_var( 'paged' ) ) {

			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {

				echo ' (';

			}

			echo __( 'Page' ) . ' ' . get_query_var( 'paged' );

			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {

				echo ')';

			}

		}

		echo '</div>';

	}

}
