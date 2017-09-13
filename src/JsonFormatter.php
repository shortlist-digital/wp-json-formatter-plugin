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
		add_filter( 'rest_prepare_category', [ $this, 'remove_acf_from_embedded_categories' ]);
    }

	public function remove_acf_from_embedded_categories($response) {
		if (isset($response->data['acf'])) {
			unset($response->data['acf']);
		}
		return $response;
	}

    /**
     * Remove unused images sizes from images and hero_image widgets
     */
    public function remove_unused_images( $json_response ) {
        if ( $json_response->status !== 200 ) {
            return $json_response;
        }

        $json_response = $this->remove_from_widgets( $json_response );
        $json_response = $this->remove_from_hero_image( $json_response );

        return $json_response;
    }

    /**
     * Remove the escaped strings (mainly quotation) from the post title
     * and from taxonomies titles
     */
    public function fix_titles( $json_response ) {
        if ( $json_response->status !== 200 ) {
            return $json_response;
        }

        $json_response->data['title']['rendered'] = html_entity_decode( $json_response->data['title']['rendered'] );

        foreach ( $json_response->data['acf'] as $key => &$field ) {
            if ( in_array( $key, [ 'category', 'tags' ], true ) ) {
                $field = $this->fix_taxonomy_title( $field );
            }
        }

        return $json_response;
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

    private function remove_from_widgets( $result ) {
        if ( ! isset( $result->data['acf']['widgets'] ) ) {
            return $result;
        }

        $widgets_with_images = [
            'image'          => 'image',
            'feature-box'    => 'image',
            'gallery'        => 'gallery_items',
            'image-carousel' => 'images'
        ];

        foreach ( $result->data['acf']['widgets'] as &$widget ) {
            if ( ! in_array( $widget['acf_fc_layout'], array_keys( $widgets_with_images ), true ) ) {
                continue;
            }

            $element = &$widget[ $widgets_with_images[ $widget['acf_fc_layout'] ] ];
            if ( isset( $element['sizes'] ) ) {
                $element['sizes'] = $this->unset_sizes( $element['sizes'] );
            } else {
                foreach( $element as &$image_element ) {
                    $image_element['sizes'] = $this->unset_sizes( $image_element['sizes'] );
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
            $hero['sizes'] = $this->unset_sizes( $hero['sizes'] );
        }

        return $result;
    }

    private function unset_sizes( $image_element ) {
        foreach ( $image_element as $key => $sizes ) {
            if ( ! $this->is_size_allowed( $key ) ) {
                unset( $image_element[$key] );
            }
        }

        return $image_element;
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
