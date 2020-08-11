# Carbon Field Easy Digital Downloads Software Licensing Field

A field add-on for Carbon Fields that allows you to Activate and Deactivate a license key with Software Licensing for Easy Digital Downloads.

## Installation 

```shell
composer require firetreedesign/carbon-field-edd-license
```

## Example

```php
Field::make( 'ft_edd_license', 'my_license_key', __( 'My License Key' ) )
    ->set_plugin_file( __FILE__ )
    ->set_item_id( 224 )
    ->set_version( '1.0.0' )
    ->set_store_url( 'https://firetreedesign.com/' )
    ->set_author( __( 'FireTree Design, LLC' ) );
    ->set_beta( false );
```