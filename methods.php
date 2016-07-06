<?php

/**
* get default language object id (WPML)
*
* @param $post_id: integer or array
* @param $post_type: string "page", "post", "attachment" or other custom post type.
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
* Function to pass the php variables into javascript
* 
*
*/

if ( !function_exists('pass_php_to_js') ) {
    function pass_php_to_js() {

    }
}