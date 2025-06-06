<?php

namespace CreateProductOnImageUpload\Tests;

use Mockery;
use WP_Mock\Tools\TestCase;
use CreateProductOnImageUpload\Plugin;

/**
 * @covers \CreateProductOnImageUpload\Plugin::register
 * @covers \CreateProductOnImageUpload\Plugin::get_product_args
 * @covers \CreateProductOnImageUpload\Plugin::get_product_meta
 * @covers \CreateProductOnImageUpload\Plugin::create_product
 */
class PluginTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_register() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		\WP_Mock::expectActionAdded( 'add_attachment', [ $plugin, 'create_product' ] );
		\WP_Mock::expectActionAdded( 'init', [ $plugin, 'register_translation' ] );

		$plugin->register();

		$this->assertConditionsMet();
	}

	public function test_create_product_fails_if_attachment_is_not_image() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'wp_attachment_is_image' )
			->andReturnUsing(
				function ( $arg ) {
					return false;
				}
			);

		$plugin->create_product( 1 );

		$this->assertConditionsMet();
	}

	public function test_create_product_fails_if_attachment_post_is_null() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'wp_attachment_is_image' )
			->andReturnUsing(
				function ( $arg ) {
					return true;
				}
			);

		\WP_Mock::userFunction( 'get_post' )
			->andReturnUsing(
				function ( $arg ) {
					return null;
				}
			);

		$plugin->create_product( 1 );

		$this->assertConditionsMet();
	}

	public function test_create_product_fails_if_insert_fails() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$error = Mockery::mock( \WP_Error::class );

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'wp_attachment_is_image' )
			->andReturnUsing(
				function ( $arg ) {
					return true;
				}
			);

		\WP_Mock::userFunction( 'get_post' )
			->andReturnUsing(
				function ( $arg ) {
					$post             = Mockery::mock( \WP_Post::class );
					$post->post_title = 'Hello World';

					return $post;
				}
			);

		\WP_Mock::userFunction( 'is_wp_error' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg instanceof \WP_Error;
				}
			);

		$plugin->shouldReceive( 'get_product_args' )
			->andReturn(
				[
					'post_title'   => 'Hello World',
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'product',
				]
			);

		\WP_Mock::userFunction( 'wp_insert_post' )
			->andReturn( $error );

		$plugin->create_product( 1 );

		$this->assertConditionsMet();
	}

	public function test_create_product_fails_passes() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'wp_attachment_is_image' )
			->andReturnUsing(
				function ( $arg ) {
					return true;
				}
			);

		\WP_Mock::userFunction( 'get_post' )
			->andReturnUsing(
				function ( $arg ) {
					$post             = Mockery::mock( \WP_Post::class );
					$post->post_title = 'Hello World';

					return $post;
				}
			);

		\WP_Mock::userFunction( 'is_wp_error' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg instanceof \WP_Error;
				}
			);

		$plugin->shouldReceive( 'get_product_args' )
			->andReturn(
				[
					'post_title'   => 'Hello World',
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'product',
				]
			);

		$plugin->shouldReceive( 'get_product_meta' )
			->andReturn( $this->get_meta_args() );

		\WP_Mock::userFunction( 'wp_insert_post' )
				->andReturn( 2 );

		\WP_Mock::userFunction( 'set_post_thumbnail' )
			->with( 2, 1 )
			->andReturn( null );

		\WP_Mock::userFunction( 'wp_set_object_terms' )
			->with( 2, 'simple', 'product_type' )
			->andReturn( null );

		\WP_Mock::userFunction( 'update_post_meta' )
			->times( 18 )
			->andReturn( null );

		$plugin->create_product( 1 );

		$this->assertConditionsMet();
	}

	public function test_get_product_args() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$plugin->post             = Mockery::mock( \WP_Post::class )->makePartial();
		$plugin->post->ID         = 1;
		$plugin->post->post_title = 'Hello World';

		\WP_Mock::expectFilter(
			'cpoiu_post_args',
			[
				'post_title'   => 'Hello World',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'product',
			],
			$plugin->post
		);

		$options = $plugin->get_product_args();

		$this->assertSame(
			$options,
			[
				'post_title'   => 'Hello World',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'product',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_product_args_returns_filtered_args() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$plugin->post             = Mockery::mock( \WP_Post::class )->makePartial();
		$plugin->post->ID         = 1;
		$plugin->post->post_title = 'Hello World';

		\WP_Mock::onFilter( 'cpoiu_post_args' )
		->with(
			[
				'post_title'   => 'Hello World',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'product',
			],
			$plugin->post
		)
		->reply(
			[
				'post_title'   => 'New Product Name',
				'post_content' => '',
				'post_status'  => 'future',
				'post_type'    => 'product',
			],
		);

		$options = $plugin->get_product_args();

		$this->assertSame(
			$options,
			[
				'post_title'   => 'New Product Name',
				'post_content' => '',
				'post_status'  => 'future',
				'post_type'    => 'product',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_product_meta() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$plugin->post             = Mockery::mock( \WP_Post::class )->makePartial();
		$plugin->post->ID         = 1;
		$plugin->post->post_title = 'Hello World';

		\WP_Mock::expectFilter(
			'cpoiu_meta_args',
			[
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
			],
			$plugin->post
		);

		$options = $plugin->get_product_meta();

		$this->assertSame(
			$options,
			[
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
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_product_meta_returns_filtered_args() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$plugin->post             = Mockery::mock( \WP_Post::class )->makePartial();
		$plugin->post->ID         = 1;
		$plugin->post->post_title = 'Hello World';

		\WP_Mock::onFilter( 'cpoiu_meta_args' )
		->with(
			[
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
			],
			$plugin->post
		)
		->reply(
			[
				'_visibility'    => 'visible',
				'_stock_status'  => 'instock',
				'total_sales'    => '0',
				'_downloadable'  => 'no',
				'_virtual'       => 'yes',
				'_regular_price' => '0',
				'_sale_price'    => '0',
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
			],
		);

		$options = $plugin->get_product_meta();

		$this->assertSame(
			$options,
			[
				'_visibility'    => 'visible',
				'_stock_status'  => 'instock',
				'total_sales'    => '0',
				'_downloadable'  => 'no',
				'_virtual'       => 'yes',
				'_regular_price' => '0',
				'_sale_price'    => '0',
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
			]
		);
		$this->assertConditionsMet();
	}

	protected function get_meta_args() {
		return [
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
	}
}
