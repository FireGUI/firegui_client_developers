<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit79970b80b6b8a8b3407d2aa31e24500f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Picqer\\Barcode\\' => 15,
            'PhpParser\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Picqer\\Barcode\\' => 
        array (
            0 => __DIR__ . '/..' . '/picqer/php-barcode-generator/src',
        ),
        'PhpParser\\' => 
        array (
            0 => __DIR__ . '/..' . '/nikic/php-parser/lib/PhpParser',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit79970b80b6b8a8b3407d2aa31e24500f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit79970b80b6b8a8b3407d2aa31e24500f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
