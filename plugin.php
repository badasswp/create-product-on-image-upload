<?php
/**
 * Plugin Name: Create Product On Image Upload
 * Plugin URI:  https://github.com/badasswp/create-product-on-image-upload
 * Description: Create WooCommerce products automatically by uploading images.
 * Version:     1.0.0
 * Author:      badasswp
 * Author URI:  github.com/badasswp
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: create-product-on-image-upload
 * Domain Path: /languages
 *
 * @package CreateProductOnImageUpload
 */

namespace badasswp\CreateProductOnImageUpload;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

class CreateProductOnImageUpload {
	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_attachment', [ $this, 'create_product' ] );
	}

	/**
	 * Create Product.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function create_product( $attachment_id ): void {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		$post = get_post( $attachment_id );

		if ( is_wp_error( $post ) ) {
			return;
		}

		$product_id = wp_insert_post( $this->get_product_args( $post ) );

		if ( is_wp_error( $product_id ) ) {
			return;
		}

		set_post_thumbnail( $product_id, $attachment_id );
		wp_set_object_terms( $product_id, 'simple', 'product_type' );

		foreach( $this->get_meta_args( $post ) as $key => $value ) {
			update_post_meta( $product_id, $key, $value );
		}
	}

	/**
	 * Get Product Args.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post WP Post.
	 * @return mixed[]
	 */
	protected function get_product_args( $post ): array {
		$args = [
			'post_title'   => $post->post_title,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'product',
		];

		/**
		 * Filter Product Args.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed[] $args Product Args.
		 * @param \WP_Post $post WP Post.
		 *
		 * @return mixed[]
		 */
		return (array) apply_filters( 'cpoiu_post_args', $args, $post );
	}

	/**
	 * Get Meta Args.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post WP Post.
	 * @return mixed[]
	 */
	protected function get_product_meta( $post ): array {
		$args = [
			'_visibility'    => 'visible',
			'_stock_status'  => 'instock',
			'total_sales'    => '0',
			'_downloadable'  => 'no',
			'_virtual'       => 'yes',
			'_regular_price' => '',
			'_sale_price'    => '',
			'_purchase_note' => '',
			'_featured'      => 'no',
			'_weight'        => '',
			'_length'        => '',
			'_width'         => '',
			'_height'        => '',
			'_sku'           => '',
			'_price'         => '',
			'_manage_stock'  => 'no',
			'_backorders'    => 'no',
			'_stock'         => '',
		];

		/**
		 * Filter Product Meta.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed[] $args Product Meta.
		 * @param \WP_Post $post WP Post.
		 *
		 * @return mixed[]
		 */
		return (array) apply_filters( 'cpoiu_meta_args', $args, $post );
	}
}

( new CreateProductOnImageUpload() )->register();
