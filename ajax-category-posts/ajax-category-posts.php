<?php
/*
Plugin Name: AJAX Category Posts
Description: Display posts from a specific WordPress category using AJAX-based pagination. Easily embeddable via a shortcode.
Version: 1.2
Author: Durgesh chander
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class AJAX_Category_Posts_Plugin {

    public function __construct() {
        add_shortcode( 'ajax_category_posts', [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_ajax_load_category_posts', [ $this, 'handle_ajax' ] );
        add_action( 'wp_ajax_nopriv_load_category_posts', [ $this, 'handle_ajax' ] );
    }

    public function enqueue_scripts() {
        if (shortcode_exists('ajax_category_posts')){
        wp_enqueue_script( 'ajax-category-posts', plugin_dir_url( __FILE__ ) . 'ajax-category-posts.js', ['jquery'], null, true );

        wp_localize_script( 'ajax-category-posts', 'ajax_cat_posts', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ]);
        }
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts([
            'category' => '',
            'posts_per_page' => 6,
        ], $atts );

        // Dynamically detect category from archive page if not provided
        if ( empty( $atts['category'] ) && is_category() ) {
            $queried_obj = get_queried_object();
            if ( isset($queried_obj->slug) ) {
                $atts['category'] = $queried_obj->slug;
            }
        }

        $cat = get_category_by_slug( $atts['category'] );
        if ( ! $cat ) return '<p>Invalid or missing category slug.</p>';

        $paged = 1;
        ob_start();

        $this->render_posts( $cat->term_id, $paged, $atts['posts_per_page'] );

        return ob_get_clean();
    }

    public function render_posts( $cat_id, $paged = 1, $ppp = 6 ) {

        $posts_per_page = $ppp;
        $initial_offset = 6;
        $current_page = max(1, $paged);
        $offset = $initial_offset + ( ($current_page - 1) * $posts_per_page );

        $query = new WP_Query([
            'cat' => $cat_id,
            'offset' => $offset,
            'posts_per_page' => $posts_per_page,
        ]);
       

        if ( $query->have_posts() ) {
            echo '<div class="block-content ajax-posts-wrapper" data-cat="' . esc_attr($cat_id) . '" data-ppp="' . esc_attr($ppp) . '">';
            echo '<div class="loop loop-list loop-sep  grid grid-1 md:grid-1 sm:grid-1 post-list">';
            while ( $query->have_posts() ) : $query->the_post();
                echo '<div class="l-post list-post grid-on-sm m-pos-left ajax-post">';
                if ( has_post_thumbnail() ) {
                $img_url = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                $img_full = get_the_post_thumbnail_url( get_the_ID(), 'full' );

                echo '<div class="media">';
                echo '<a href="' . get_permalink() . '" class="image-link media-ratio ar-bunyad-list" title="' . esc_attr( get_the_title() ) . '">';
                echo '<span data-bgsrc="' . esc_url( $img_url ) . '" class="img bg-cover wp-post-image attachment-medium size-medium lazyloaded"';
                echo ' data-bgset="' . esc_url( $img_url ) . ' 300w, ' . esc_url( $img_full ) . ' 1024w"';
                echo ' data-sizes="(max-width: 241px) 100vw, 241px"';
                echo ' style="background-image: url(' . esc_url( $img_full ) . ');"></span>';
                echo '</a>';
                echo '</div>';
            }
            else{
                echo '<div class="media">';
                echo '<a href="' . get_permalink() . '" class="image-link media-ratio ar-bunyad-list" title="' . esc_attr( get_the_title() ) . '">';
                echo '<span class="img bg-cover wp-post-image attachment-medium size-medium lazyloaded"';
                echo ' style="background-image: url();"></span>';
                echo '</a>';
                echo '</div>';
            }
                $categories = get_the_category();
    
                echo '<div class="content"><div class="post-meta post-meta-a has-below"><h3 class="is-title post-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
               echo '<div class="post-meta-items meta-below">';
              
                if ( ! empty( $categories ) ) {
                    $first_category = $categories[0];
                    echo '<span class="meta-item post-cat"><a href="' . esc_url( get_category_link( $first_category->term_id ) ) . '">' . esc_html( $first_category->name ) . '</a></span>';
                }    

                echo '<span class="meta-item date">' . get_the_date() . '</span>';
                echo '</div></div>';
                
                echo '<div class="excerpt"><p>' . get_the_excerpt() . '</p></div></div>';
                echo '</div>';
            endwhile;
            echo '</div>';

      $total_posts = max(0, $query->found_posts - $initial_offset);
      $total_pages = ceil($total_posts / $posts_per_page);

$pagination = paginate_links([
    'total' => $total_pages,
    'current' => $paged,
    'format' => '#',
    'prev_text' => '«',
    'next_text' => '»',
    'type' => 'array',
]);

if ( $pagination ) {
    echo '<div class="main-pagination pagination-numbers ajax-pagination">';
    foreach ( $pagination as $link ) {
        // Extract the page number from the link text (e.g., "2")
        if ( preg_match('/>(\\d+)<\\/a>/', $link, $matches) ) {
            $page_num = intval($matches[1]);
            $link = str_replace('<a ', '<a data-page="' . $page_num . '" ', $link);
        } elseif ( strpos($link, 'next') !== false ) {
            $page_num = $paged + 1;
            $link = str_replace('<a ', '<a data-page="' . $page_num . '" ', $link);
        } elseif ( strpos($link, 'prev') !== false ) {
            $page_num = max(1, $paged - 1);
            $link = str_replace('<a ', '<a data-page="' . $page_num . '" ', $link);
        }
        echo $link;
    }
    echo '</div>';
}

            echo '</div>';
        } else {
            echo '<p>No posts found.</p>';
        }

        wp_reset_postdata();
    }

    public function handle_ajax() {
        $cat_id = intval( $_POST['cat_id'] ?? 0 );
        $paged = intval( $_POST['paged'] ?? 1 );
        $ppp   = intval( $_POST['ppp'] ?? 6 );

        if ( ! $cat_id ) wp_die();

        $this->render_posts( $cat_id, $paged, $ppp );
        wp_die();
    }

}

new AJAX_Category_Posts_Plugin();

