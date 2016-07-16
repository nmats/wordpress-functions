<?php

define( TRANSLATE_DOMAIN,       'translate_domain'  ); //constants for string translation
define( ORIGINAL_JS,            'original-javascript' ); // constant for original javascript file.
define( ORIGINAL_CSS,           'original_css' ); // constant for original css file.
define( CHILD_JS,               'child_javascript' ); // constant for child custom javascript file.
define( CHILD_CSS,              'child_css' ); // constant for child custom css file.
define( JS_DIRECTORY,           get_template_directory_uri() . '/js' ); // constant of original js file directory.
define( CSS_DIRECTORY,          get_template_directory_uri() . '/css' ); // constant of original css file directory.
define( CHILD_JS_DIRECTORY,     get_stylesheet_directory_uri() . '/js' ); // constant for child custom js directory.
define( CHILD_CSS_DIRECTORY,    get_stylesheet_directory_uri() . '/css' ); // constant for child custom css directory.

/**
 * Adding theme support for initialize.
 */

if ( !function_exists('original_theme_initialize') ) {
    function original_theme_initialize() {
        /**
         * Custom background.
         */
        $custom_bg = array(
                'default-color'         => '#ffffff',
                'default-image'         => '',
            );
        add_theme_support( 'custom_background', $custom_bg );

        /**
         * HTML5 markup.
         */
        $html5 = array(
                'search_form',
                'gallery',
                'caption',
            );
        add_theme_support( 'html5', $html5 );

        /**
         * Enable title tag functions.
         */
        add_theme_support( 'title-tag' );
    }
}
add_action( 'after_setup_theme', 'original_theme_initialize' );

/**
 * get default language object id (WPML)
 *
 * @param int $post_id
 * @param str $post_type string "page", "post", "attachment" or other custom post type.
 * @return mixed
 */
    
if ( !function_exists('get_lang_id') ) {
    function get_lang_id( $post_id, $post_type = 'page' ) {

        global $sitepress;
        $default_language = $sitepress->get_default_language();

        if( function_exists( 'wpml_object_id' ) ) {
            $default_id_array = [];

            // If $post_id is array
            if ( is_array($post_id) ) {
                foreach ( $post_id as $id ) {

                    $default_id_array[] = apply_filters( 'wpml_object_id', $id, $post_type, true, $default_language );
                
                }
                return $default_id_array;
            } 
            // If string 
            elseif ( is_string( $post_id ) ) {

                // if the string comma separated string
                if ( strpos( $post_id, ',' ) ) {
                    $id_str_array = split( ',', $post_id );
                    foreach ( $id_str_array as $id ) {

                        $default_id_array[] = apply_filters( 'wpml_object_id', $id, $post_type, true, $default_language );
                    
                    }
                    return $default_id_array;
                }
                // if it contains one string.
                else {
                    return apply_filters( 'wpml_object_id', $id, $post_type, true, $default_language );
                }
            }
            // If it is integer.
            else {
                return apply_filters( 'wpml_object_id', $id, $post_type, true, $default_language );
            }

        } else {
          return $post_id;
        }
    }
}

/**
 * Enqueue function for javascript.
 * Hook script & style sheet function to wp_enqueue_style
 *
 */

if ( !function_exists('original_enqueue_files') ) {
    function original_enqueue_files() {

        original_enqueue_script();
        original_enqueue_style();

    }
}
add_action( 'wp_enqueue_style', 'original_enqueue_files' );

if ( !function_exists('original_enqueue_script') ) {
    function original_enqueue_script() {
        wp_register_script( ORIGINAL_JS, JS_DIRECTORY.'/core.js', array(), '', true );
        wp_enqueue_script( ORIGINAL_JS );

        if ( file_exists( CHILD_JS_DIRECTORY.'/custom.js' ) && is_child_theme() ) {
            wp_register_script( CHILD_JS, CHILD_JS_DIRECTORY.'/custom.js', array(ORIGINAL_JS), '', true );
            wp_enqueue_script( CHILD_JS );
        }
    }
}

if ( !function_exists('original_enqueue_style') ) {
    function original_enqueue_style() {
        wp_enqueue_style( ORIGINAL_CSS, CSS_DIRECTORY . '/core.css', array(), '', false );

        if ( file_exists( CHILD_CSS_DIRECTORY . '/custom.css' ) && is_child_theme() ) {
            wp_enqueue_style( CHILD_CSS, CHILD_CSS_DIRECTORY . '/custom.css', array(), '', false );
        }
    }
}


/**
 * Function to pass the php variables into javascript
 * 
 * @param array $args
 * @return nothing
 */

if ( !function_exists('pass_php_to_js') ) {
    function pass_php_to_js( $args = array() ) {

        $default = array(
                'blog_name'     => get_bloginfo( 'name' ),
                'blog_url'      => get_bloginfo( 'url' ),
                'blog_email'    => get_bloginfo( 'admin_email' ),
                's_sheet_dir'   => get_bloginfo( 'stylesheet_directory' ),
            );

        if ( !empty($args) ) {

            $args = array_merge( $default, $args );
            
        }
        wp_localize_script( ORIGINAL_JS, 'php_array', $args );
    }
}
add_action( 'original_enqueue_files', 'pass_php_to_js' );

/**
 * Function if using filegallery plugin.
 * @param int $post_id
 * @param str|arr $media_tag
 * @return arr $attachment_id return array of post id.
 *
 */
if ( !function_exists('get_your_demanded_image_id') ) {
    function get_your_demanded_image_id( $post_id, $media_tag = array() ) {

        $default = array(
            'post_type'         => 'attachment',
            'posts_per_page'    => -1,
            'post_parent'       => $post_id,
            'post_status'       => 'inherit',
            'post_mime_type'    => 'image',
            'tax_query'         => array(
                array(
                    'taxonomy'      => 'media_tag',
                    'field'         => 'name',
                    'terms'         => $media_tag,
                ),
            ),
        );

        if ( !empty($media_tag) && !empty($post_id) ) {
            $attachments = get_posts($default);

            if ( !empty($attachments) ) {
                $attachment_id = array();

                foreach ( $attachments as $attachment ) {
                    $attachment_id[] = $attachment->ID;
                }
                wp_reset_query();
                return $attachment_id;

            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}