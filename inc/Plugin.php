<?php
/**
 * Plugin Class.
 *
 * Load the plugin and register the appropriate
 * WP hooks.
 *
 * @package CreateProductOnImageUpload
 */

namespace CreateProductOnImageUpload;

class Plugin {
	/**
	 * WP Post.
	 *
	 * @since 1.0.0
	 *
	 * @var \WP_Post
	 */
	public \WP_Post $post;

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
		$id = absint( $attachment_id );

		if ( ! wp_attachment_is_image( $id ) ) {
			return;
		}

		$this->post = get_post( $id );

		if ( is_null( $this->post ) ) {
			return;
		}

		$product_id = wp_insert_post( $this->get_product_args() );

		if ( is_wp_error( $product_id ) ) {
			return;
		}

		set_post_thumbnail( $product_id, $id );
		wp_set_object_terms( $product_id, 'simple', 'product_type' );

		foreach ( $this->get_meta_args() as $key => $value ) {
			update_post_meta( $product_id, $key, $value );
		}
	}

	/**
	 * Get Product Args.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[]
	 */
	protected function get_product_args(): array {
		$args = [
			'post_title'   => $this->post->post_title,
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
		return (array) apply_filters( 'cpoiu_post_args', $args, $this->post );
	}

	/**
	 * Get Meta Args.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[]
	 */
	protected function get_product_meta(): array {
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
		return (array) apply_filters( 'cpoiu_meta_args', $args, $this->post );
	}
}
