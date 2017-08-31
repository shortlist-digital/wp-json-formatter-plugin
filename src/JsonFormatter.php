<?php

namespace ShortlistMedia\WpJsonFormatter;

class JsonFormatter
{
    private $allowed_image_sizes = [
        'square',
        'portrait',
        'landscape',
        'letterbox'
    ];

    public function register() {
        add_filter( 'rest_post_dispatch', [ $this, 'remove_unused_images' ] );
        add_filter( 'rest_post_dispatch', [ $this, 'fix_titles' ] );
    }

    /**
     * Remove unused images sizes from images and hero_image widgets
     */
    public function remove_unused_images( $result ) {
        $result = $this->remove_from_image_widget( $result );
        $result = $this->remove_from_hero_image( $result );

        return $result;
    }

    /**
     * Remove the escaped strings (mainly quotation) from the post title
     * and from taxonomies titles
     */
    public function fix_titles( $result ) {
        $result->data['title']['rendered'] = html_entity_decode( $result->data['title']['rendered'] );

        foreach ( $result->data['acf'] as $key => &$field ) {
            if ( in_array( $key, [ 'category', 'tags' ], true ) ) {
                $field = $this->fix_taxonomy_title( $field );
            }
        }

        return $result;
    }

    private function fix_taxonomy_title( $taxonomy ) {
        if ( is_object( $taxonomy ) ) {
            $taxonomy->name = html_entity_decode( $taxonomy->name );
        } elseif ( is_array( $taxonomy ) ) {
            foreach( $taxonomy as &$term ) {
                $term->name = html_entity_decode( $term->name );
            }
        }

        return $taxonomy;
    }

    private function remove_from_image_widget( $result ) {
        if ( ! isset( $result->data['acf']['widgets'] ) ) {
            return $result;
        }

        foreach ( $result->data['acf']['widgets'] as &$widget ) {
            if ( $widget['acf_fc_layout'] !== 'image' ) {
                continue;
            }

            foreach ( $widget['image']['sizes'] as $key => $sizes ) {
                if ( ! $this->is_size_allowed( $key ) ) {
                    unset( $widget['image']['sizes'][$key] );
                }
            }
        }

        return $result;
    }

    private function remove_from_hero_image( $result ) {
        if ( ! isset( $result->data['acf']['hero_images'] ) ) {
            return $result;
        }

        foreach ( $result->data['acf']['hero_images'] as &$hero ) {
            foreach ( $hero['sizes'] as $key => $sizes ) {
                if ( ! $this->is_size_allowed( $key ) ) {
                    unset( $hero['sizes'][$key] );
                }
            }
        }

        return $result;
    }

    private function is_size_allowed( $size ) {
        foreach ( $this->allowed_image_sizes as $allowed_size ) {
            if ( strpos( $size, $allowed_size ) !== false ) {
                return true;
            }
        }

        return false;
    }
}
