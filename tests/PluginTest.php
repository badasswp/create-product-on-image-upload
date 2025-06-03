<?php

namespace CreateProductOnImageUpload\Tests;

use Mockery;
use WP_Mock\Tools\TestCase;
use CreateProductOnImageUpload\Plugin;

/**
 * @covers \CreateProductOnImageUpload\Plugin::get_product_args
 */
class PluginTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_get_product_args() {
		$plugin = Mockery::mock( Plugin::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$plugin->shouldReceive( 'register' )->andReturnNull();

		$post             = Mockery::mock( \WP_Post::class )->makePartial();
		$post->ID         = 1;
		$post->post_title = 'Hello World';

		\WP_Mock::expectFilter(
			'cpoiu_post_args',
			[
				'post_title'   => 'Hello World',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'product',
			],
			$post
		);

		$options = $plugin->get_product_args( $post );

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
}
