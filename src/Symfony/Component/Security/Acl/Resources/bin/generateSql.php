<?php

require_once __DIR__.'/../../../../ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Acl\Dbal\Schema;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                    => __DIR__.'/../../../../../..',
    'Doctrine\\Common'           => __DIR__.'/../../../../../../../vendor/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations' => __DIR__.'/../../../../../../../vendor/doctrine-migrations/lib',
    'Doctrine\\DBAL'             => __DIR__.'/../../../../../../../vendor/doctrine-dbal/lib',
    'Doctrine'                   => __DIR__.'/../../../../../../../vendor/doctrine/lib',
));
$loader->register();


$schema = new Schema(array(
    'class_table_name'         => 'acl_classes',
    'entry_table_name'         => 'acl_entries',
    'oid_table_name'           => 'acl_object_identities',
    'oid_ancestors_table_name' => 'acl_object_identity_ancestors',
    'sid_table_name'           => 'acl_security_identities',
));

$reflection = new ReflectionClass('Doctrine\DBAL\Platforms\AbstractPlatform');
$finder = new Finder();
$finder->name('*.php')->in(dirname($reflection->getFileName()));
foreach ($finder as $file) {
    require_once $file->getPathName();
    $className = sprintf('Doctrine\DBAL\Platforms\%s', basename($file->getFilename(), '.php'));

    $reflection = new ReflectionClass($className);
    if ($reflection->isAbstract()) {
        continue;
    }

    $platform = $reflection->newInstance();
    $targetFile = sprintf(__DIR__.'/../schema/%s.sql', $platform->getName());
    file_put_contents($targetFile, implode("\n\n", $schema->toSql($platform)));
}
