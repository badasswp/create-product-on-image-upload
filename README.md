# create-product-on-image-upload

Create WooCommerce products automatically by uploading images.

<img width="1277" alt="cpoiu-mini" src="https://github.com/user-attachments/assets/fbc5c010-1a3b-4746-99ad-fc0a762e6e31" />

## Why Create Product On Image Upload?

This plugin makes it easy to create WooCommerce products by simply uploading the featured image of the product you intend to create. It will proceed to use the name of the image as the product name.

https://github.com/user-attachments/assets/ec601f69-bd06-461f-90a6-d1b6a74dd1eb

### Hooks

#### `cpoiu_post_args`

This custom hook (filter) provides a way to customise the product's post args options that is used to create the product, like so:

```php
add_filter( 'cpoiu_post_args', [ $this, 'update_post_args' ] );

public function update_post_args( $options ): array {
    $options[ 'post_status' ] = 'draft';

    return $options;
}
```

**Parameters**

- options _`{array}`_ By default this will be an associative array containing key, value options of a product's post args.
<br/>

#### `cpoiu_meta_args`

This custom hook (filter) provides a way to customise the product's meta options that is used to update the product's meta values during creation time, like so:

```php
add_filter( 'cpoiu_meta_args', [ $this, 'update_meta_args' ] );

public function update_meta_args( $options ): array {
    if ( empty( $options[ '_sale_price' ] ) ) {
        $options[ '_sale_price' ] = absint( $options[ '_regular_price' ] )
    }

    return $options;
}
```

**Parameters**

- options _`{array}`_ By default this will be an associative array containing key, value options of a product's meta.
<br/>

## Contribute

Contributions are __welcome__ and will be fully __credited__. To contribute, please fork this repo and raise a PR (Pull Request) against the `master` branch.

### Pre-requisites

You should have the following tools before proceeding to the next steps:

- Composer
- Yarn
- Docker

To enable you start development, please run:

```bash
yarn start
```

This should spin up a local WP env instance for you to work with at:

```bash
http://create-product-on-image-upload.localhost:5698
```

You should now have a functioning local WP env to work with. To login to the `wp-admin` backend, please use `admin` for username & `password` for password.

__Awesome!__ - Thanks for being interested in contributing your time and code to this project!
