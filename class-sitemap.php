<?php
class Sitemap {
    private $exclude           = array();
    private $custom_post_menus = array();
    private $split             = array();
    private $default = array(
            'depth'       => 0, 
            'show_date'   => '',
            'date_format' => '',
            'child_of'    => 0,
            'exclude'     => '',
            'title_li'    => '', 
            'echo'        => false,
            'authors'     => '', 
            'sort_column' => 'menu_order, post_name',
            'link_before' => '', 
            'link_after'  => '', 
            'walker'      => '',
    );

    /**
     * Setting exclude item id.
     * @param array(int) $ids storing post/page id to var.
     */

    public function set_exclude_page( $ids = array() ) {

        if ( empty($ids) ) {
            return false;
        }

        foreach ($ids as $key => $value) {
            if ( !is_numeric($value) ) {
                break;
            } else {
                array_push($this->exclude, $value);
            }
        }
    }

    /**
     * Setting split item information.
     * @param array $splits: array of int passing page id.
     * 
     */

    public function set_split_item ( $splits = array() ) {

        if ( empty($splits) ) {
            return null;
        }

        foreach ($splits as $key => $value) {
            if ( !is_numeric($value) ) {
                break;
            } else {
                array_push($this->split, $value);
            }
        }
    }

    /**
     * Setting custom post menu.
     * @param array $posts: nested array of post type & parent page. parent page should be id or tpl name.
     * 
     */

    public function set_custom_post_to_menu( $posts = array() ) {
        if ( empty($posts) ) {
            return null;
        }

        foreach ( $posts as $post ) {
            if ( count($post) <= 1 ) {
                return null;
            } else {
                $i = 0;
                $id_array = array();

                foreach ($post as $key => $value) {
                    switch ( $key ) {
                        case 'tpl-name':
                            $page_ids = get_posts( array(
                                        'post_type'        => 'page',
                                        'meta_key'         => '_wp_page_template',
                                        'meta_value'       => $value.'/'.$value.'.php',
                                        'suppress_filters' => false,
                                        'fields'           => 'ids',
                                    )
                                );
        
                            $id_array[$i]['id'] = $page_ids;
                            break;

                        case 'id':
                            $id_array[$i]['id'] = $value;
                            break;

                        case 'slug':
                            $page_ids = get_posts( array(
                                    'post_type'     => 'page',
                                    'fields'        => 'ids',
                                    'pagename'      => $value,
                                )
                            );

                            $id_array[$i]['id'] = $page_ids;
                            break;

                        case 'post_type':
                            $args = array(
                                'sort_column' => 'menu_order',
                                'sort_order'  => 'ASC',
                                'title_li'    => '',
                                'echo'        => false,
                                'post_type'   => $value,
                                'depth'       => 1,
                            );
                            $id_array[$i]['menus'] = wp_list_pages( $args );
                    }
                }
                array_push($this->custom_post_menus, $id_array[$i]);
                $i++;
            }
        }
    }

    /**
     * Return page list for sitemap.
     * @param array $args: refer wp_page_list arguments.
     * @link https://developer.wordpress.org/reference/functions/wp_list_pages/
     * @return str $sitemap: bunch of pages.
     */

    public function get_page_list( $args = array() ) {
        if ( !empty($args) ) {
            $args = array_merge( $this->default, $args );
            $menu = wp_list_pages( $args );
        } else {
            $menu = wp_list_pages( $this->default );
        }
        

        if ( !empty($this->split) ) {
            foreach ( $this->split as $split_id ) {
                $split_patterns[] = "~(?<=<\/li>\n)(<li class=\"page_item page-item-$split_id)~";
            }
            if ( !empty($split_patterns) ) {
                $menu = preg_replace($split_patterns, "\n</ul>\n<ul class='sitemap__col'>\n$1", $menu);
            }
        }

        if ( !empty($this->custom_post_menus) ) {
            foreach ( $this->custom_post_menus as $post_menu ) {
                $post_id = $post_menu['id'];
                $insert_str = "<ul>".$post_menu['menus']."</ul>";
                $pattern = "~(<li class=\"page_item page-item-$post_id\">)(.*)(<\/li>)~";

                if ( preg_match( $pattern, $menu) ) {
                    $menu = preg_replace( $pattern, "<li class=\"page_item page-item-$post_id page-item-has-children\">$2$insert_str$3", $menu );
                }
            }
        }
        return $menu;
    }
}