<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8597691bacb45302fd9f943e6dc99517
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8597691bacb45302fd9f943e6dc99517::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8597691bacb45302fd9f943e6dc99517::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8597691bacb45302fd9f943e6dc99517::$classMap;

        }, null, ClassLoader::class);
    }
}
