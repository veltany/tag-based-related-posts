<?php
/**
 * Plugin Name: Tag-Based Related Posts
 * Description: Automatically inserts a list of related posts within your content based on shared tags.
 * Version: 1.5
 * Author: Samuel Chukwu 
 * License: GPL2
 * Text Domain: tb_related_posts
 * Author URI: https://github.com/veltany 
 * GitHub Plugin URI: https://github.com/veltany/tag-based-related-posts
 * GitHub Branch: main
 * Requires at least: 6.6
 * Requires PHP: 8.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// PLUGIN UPDATES

define('TB_RELATED_POSTS_VERSION', '2.1');
define('TB_RELATED_POSTS_DIR', plugin_dir_path(__FILE__));
define('TB_RELATED_POSTS_URL', plugin_dir_url(__FILE__));


require TB_RELATED_POSTS_DIR.'update/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/veltany/tag-based-related-posts',
	TB_RELATED_POSTS_DIR.'tag-based-related-posts.php', //Full path to the main plugin file or functions.php.,
	'tb_related_posts'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');



// Define default options.
function tb_related_posts_default_options() {
    return array(
        'number_of_posts' => 5,
        'excluded_tags'   => '',
        'display_location' => 'after_content',
        'style_template'   => 'list',
        'nth_paragraph'    => 1,  
    );
}
 

// Add admin menu item.
function tb_related_posts_add_settings_page() {
    add_options_page(
        'Tag-Based Related Posts Settings',
        'Related Posts',
        'manage_options',
        'tb-related-posts',
        'tb_related_posts_settings_page'
    );
}
add_action( 'admin_menu', 'tb_related_posts_add_settings_page' );

// Render settings page.
function tb_related_posts_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Tag-Based Related Posts Settings', 'tag-based-related-posts' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'tb_related_posts_options_group' );
            do_settings_sections( 'tb-related-posts' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings.
function tb_related_posts_register_settings() {
    register_setting(
        'tb_related_posts_options_group',
        'tb_related_posts_options',
        'tb_related_posts_validate_options'
    );

    add_settings_section(
        'tb_related_posts_main_section',
        __( 'Main Settings', 'tag-based-related-posts' ),
        'tb_related_posts_main_section_cb',
        'tb-related-posts'
    );

    add_settings_field(
        'number_of_posts',
        __( 'Number of Related Posts', 'tag-based-related-posts' ),
        'tb_related_posts_number_field_cb',
        'tb-related-posts',
        'tb_related_posts_main_section'
    );

    add_settings_field(
        'excluded_tags',
        __( 'Excluded Tags (comma-separated)', 'tag-based-related-posts' ),
        'tb_related_posts_excluded_tags_field_cb',
        'tb-related-posts',
        'tb_related_posts_main_section'
    );

    add_settings_field(
        'display_location',
        __( 'Display Location', 'tag-based-related-posts' ),
        'tb_related_posts_display_location_field_cb',
        'tb-related-posts',
        'tb_related_posts_main_section'
    );
    
    add_settings_field(
    'style_template',
    __( 'Style Template', 'tag-based-related-posts' ),
    'tb_related_posts_style_template_field_cb',
    'tb-related-posts',
    'tb_related_posts_main_section'
);

add_settings_field(
    'nth_paragraph',
    __( 'Display After Nth Paragraph', 'tag-based-related-posts' ),
    'tb_related_posts_nth_paragraph_field_cb',
    'tb-related-posts',
    'tb_related_posts_main_section'
);
}
add_action( 'admin_init', 'tb_related_posts_register_settings' );

// Callback functions for the settings.
function tb_related_posts_main_section_cb() {
    echo '<p>' . esc_html__( 'Customize the behavior of the related posts section.', 'tag-based-related-posts' ) . '</p>';
}

function tb_related_posts_number_field_cb() {
    $options = get_option( 'tb_related_posts_options', tb_related_posts_default_options() );
    ?>
    <input type="number" name="tb_related_posts_options[number_of_posts]" value="<?php echo esc_attr( $options['number_of_posts'] ); ?>" min="1" />
    <?php
}

function tb_related_posts_excluded_tags_field_cb() {
    $options = get_option( 'tb_related_posts_options', tb_related_posts_default_options() );
    ?>
    <input type="text" name="tb_related_posts_options[excluded_tags]" value="<?php echo esc_attr( $options['excluded_tags'] ); ?>" placeholder="e.g., tag1,tag2" />
    <p class="description"><?php esc_html_e( 'Enter tags to exclude, separated by commas.', 'tag-based-related-posts' ); ?></p>
    <?php
}

function tb_related_posts_display_location_field_cb() {
    $options = get_option( 'tb_related_posts_options', tb_related_posts_default_options() );
    ?>
    <select name="tb_related_posts_options[display_location]">
        <option value="after_content" <?php selected( $options['display_location'], 'after_content' ); ?>>
            <?php esc_html_e( 'After Content', 'tag-based-related-posts' ); ?>
        </option>
        <option value="before_content" <?php selected( $options['display_location'], 'before_content' ); ?>>
            <?php esc_html_e( 'Before Content', 'tag-based-related-posts' ); ?>
        </option>
        <option value="inside_content" <?php selected( $options['display_location'], 'inside_content' ); ?>>
            <?php esc_html_e( 'Inside Content', 'tag-based-related-posts' ); ?>
        </option>
        <option value="manual" <?php selected( $options['display_location'], 'manual' ); ?>>
            <?php esc_html_e( 'Manual (use shortcode)', 'tag-based-related-posts' ); ?>
        </option>
    </select>
    <?php
}
function tb_related_posts_style_template_field_cb() {
    $options = get_option( 'tb_related_posts_options', tb_related_posts_default_options() );
    ?>
    <select name="tb_related_posts_options[style_template]">
        <option value="list" <?php selected( $options['style_template'], 'list' ); ?>>
            <?php esc_html_e( 'List Style', 'tag-based-related-posts' ); ?>
        </option>
        <option value="grid" <?php selected( $options['style_template'], 'grid' ); ?>>
            <?php esc_html_e( 'Grid Style', 'tag-based-related-posts' ); ?>
        </option>
        <option value="minimal" <?php selected( $options['style_template'], 'minimal' ); ?>>
            <?php esc_html_e( 'Minimal Style', 'tag-based-related-posts' ); ?>
        </option>
        <option value="card" <?php selected( $options['style_template'], 'card' ); ?>>
            <?php esc_html_e( 'Card List', 'tag-based-related-posts' ); ?>
        </option>
    </select>
    <?php
}

 function tb_related_posts_nth_paragraph_field_cb() {
    $options = get_option( 'tb_related_posts_options', tb_related_posts_default_options() );
    ?>
    <input type="number" name="tb_related_posts_options[nth_paragraph]" 
           value="<?php echo esc_attr( $options['nth_paragraph'] ); ?>" 
           min="1" />
    <p class="description">
        <?php esc_html_e( 'Enter the paragraph number after which the related posts section should appear. Default is 1.', 'tag-based-related-posts' ); ?>
    </p>
    <?php
}

// Validate and sanitize options.
function tb_related_posts_validate_options( $input ) {
    $validated = array();

    $validated['number_of_posts'] = (int) ( $input['number_of_posts'] ?? 5 );
    $validated['excluded_tags']   = sanitize_text_field( $input['excluded_tags'] ?? '' );
    $validated['display_location'] = sanitize_text_field( $input['display_location'] ?? 'after_content' );
    $validated['style_template'] = sanitize_text_field( $input['style_template'] ?? 'list' );
    $validated['nth_paragraph'] = (int) ( $input['nth_paragraph'] ?? 1 );

    return $validated;
}

// Fetch settings in plugin logic.
function tb_related_posts_get_option( $key ) {
    $options = get_option( 'tb_related_posts_options', tb_related_posts_default_options() );
    return $options[ $key ] ?? tb_related_posts_default_options()[ $key ];
}

// Adjust plugin logic based on settings.
function tb_related_posts_append_to_content( $content ) {
    if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    $display_location = tb_related_posts_get_option( 'display_location' );

    if ( $display_location === 'manual' ) {
        return $content; // Do not auto-append for manual display.
    }

    $related_posts_html = tb_related_posts_generate_related_posts_html();

    if ( $display_location === 'before_content' ) {
        return $related_posts_html . $content;
    }
    
    
     if( $display_location === 'inside_content' ) 
     {
         $nth_paragraph    = (int) tb_related_posts_get_option( 'nth_paragraph' ) ?? 1;

        // Only proceed if the location is set to 'nth_paragraph'
        /*if ( $display_location !== 'nth_paragraph' ) {
            return $content;
        }
        */

        
        if ( empty( $related_posts_html ) ) {
            return $content;
        }

        // Split content into paragraphs
        $paragraphs = explode( '</p>', $content );
        
        // Ensure the Nth paragraph is valid
        if ( $nth_paragraph > count( $paragraphs ) ) {
            $nth_paragraph = count( $paragraphs );
        }

        // Insert the related posts HTML after the specified paragraph
        $output = '';
        foreach ( $paragraphs as $index => $paragraph ) {
            $output .= $paragraph . '</p>';
            if ( $index + 1 === $nth_paragraph ) {
                $output .= $related_posts_html;
            }
        }

        return $output;
      }


    if($display_location === 'after_content' ) 
    { 
        return $content . $related_posts_html;
    } 
}
add_filter( 'the_content', 'tb_related_posts_append_to_content' );

// Generate related posts HTML.
function tb_related_posts_generate_related_posts_html() {
    global $post;

    $number_of_posts = tb_related_posts_get_option( 'number_of_posts' );
    $excluded_tags   = array_map( 'trim', explode( ',', tb_related_posts_get_option( 'excluded_tags' ) ) );
    $style_template  = tb_related_posts_get_option( 'style_template' );

    $tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
    $tags = array_diff( $tags, $excluded_tags ); // Exclude tags.

    if ( empty( $tags ) ) {
        return '';
    }

    $args = array(
        'tag__in'             => $tags,
        'post__not_in'        => array( $post->ID ),
        'posts_per_page'      => $number_of_posts,
        'ignore_sticky_posts' => 1,
         'order_by_most_tags' => 1,
         'post_status' => 'publish', 
         'orderby' => 'rand', 
         'order' => 'ASC', 
         'caller_get_posts'=>1
    );
    
    // Generate a unique transient key for this post
    $transient_key = 'tb_related_posts_' . $post->ID;

    // Attempt to fetch cached data
    $cached_posts = get_transient( $transient_key );

    if ( $cached_posts !== false ) {
        // Set cached posts if they exist
        $related_posts = $cached_posts;
    }
    else
    {
    // If no cached data, fetch tags for the current post
    $related_posts = get_posts( $args );
    // Cache the results in a transient for 12 hours
    set_transient( $transient_key, $related_posts, 12 * HOUR_IN_SECONDS );

    } 

    if ( empty( $related_posts ) ) {
        return '';
    }

    // Generate HTML based on the selected style.
    $html = '<div class="tb-related-posts tb-related-posts-' . esc_attr( $style_template ) . '">';
    $html .= '<h3>' . esc_html__( 'Related Songs', 'tag-based-related-posts' ) . '</h3>';

    switch ( $style_template ) {
        case 'grid':
            $html .= '<div class="related-posts-grid">';
            foreach ( $related_posts as $related ) {
                $html .= '<div class="related-post-item">';
                $html .= '<a href="' . esc_url( get_permalink( $related->ID ) ) . '">';
                $html .= esc_html( get_the_title( $related->ID ) );
                $html .= '</a>';
                $html .= '</div>';
            }
            $html .= '</div>';
            break;

        case 'minimal':
            $html .= '<ul class="related-posts-minimal">';
            foreach ( $related_posts as $related ) {
                $html .= '<li>';
                $html .= '<a href="' . esc_url( get_permalink( $related->ID ) ) . '">';
                $html .= esc_html( get_the_title( $related->ID ) );
                $html .= '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
            break;
            
         case 'card':
            $html .= '<div class="tb-related-posts tb-related-posts-cards" >';
            foreach ( $related_posts as $related ) {
                $html .= '<div class="tb-related-post-card" >';
                $html .= '<a href="' . esc_url( get_permalink( $related->ID ) ) . '"  class="tb-related-post-link" > ';
                
                $html .= 
                        '<div class="tb-related-post-thumbnail">'. 
                  get_the_post_thumbnail($related->ID, 'thumb', array('height' => 75, 'width' => 75) ). 
                       ' </div>
                    <div class="tb-related-post-title">
                        '. get_the_title($related->ID ).' 
                    </div>'
                    
                     ;
                
                $html .= '</a>';
                $html .= '</div>';
            }
            $html .= '</div>';
            break;

        case 'list':
        default:
            $html .= '<ul class="related-posts-list">';
            foreach ( $related_posts as $related ) {
                $html .= '<li>';
                $html .= '<a href="' . esc_url( get_permalink( $related->ID ) ) . '">';
                $html .= esc_html( get_the_title( $related->ID ) );
                $html .= '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
            break;
    }

    $html .= '</div>';
    return $html;
}

function tb_related_posts_enqueue_styles() {
    wp_enqueue_style( 'tb-related-posts-styles', plugin_dir_url( __FILE__ ) . 'css/tb-related-posts.css',array(), 2.1) ;
}
add_action( 'wp_enqueue_scripts', 'tb_related_posts_enqueue_styles' );



// Shortcode for manual placement.
function tb_related_posts_shortcode() {
    return tb_related_posts_generate_related_posts_html();
}
add_shortcode( 'tb_related_posts', 'tb_related_posts_shortcode' );

