<?php
/**
 * Plugin Name: Create Product On Image Upload
 * Plugin URI:  https://github.com/badasswp/create-product-on-image-upload
 * Description: Create WooCommerce products automatically by uploading images.
 * Version:     1.0.2
 * Author:      badasswp
 * Author URI:  https://github.com/badasswp
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

define( 'CREATE_PRODUCT_ON_IMAGE_UPLOAD_AUTOLOAD', __DIR__ . '/vendor/autoload.php' );

// Composer Check.
if ( ! file_exists( CREATE_PRODUCT_ON_IMAGE_UPLOAD_AUTOLOAD ) ) {
	add_action(
		'admin_notices',
		function () {
			vprintf(
				/* translators: Plugin directory path. */
				esc_html__( 'Fatal Error: Composer not setup in %s', 'create-product-on-image-upload' ),
				[ __DIR__ ]
			);
		}
	);

	return;
}

// Run Plugin.
require_once CREATE_PRODUCT_ON_IMAGE_UPLOAD_AUTOLOAD;
( new \CreateProductOnImageUpload\Plugin() )->register();
