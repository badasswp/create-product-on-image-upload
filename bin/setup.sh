#!/bin/bash

wp-env run cli wp theme activate twentytwentythree
wp-env run cli wp rewrite structure /%postname%
wp-env run cli wp option update blogname "Create Product On Image Upload"
wp-env run cli wp option update blogdescription "Create WooCommerce products automatically by uploading images."

wp-env run cli wp plugin install woocommerce --activate
