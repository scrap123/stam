<?php
use Mandango\Mandango;
use blazemeter\Model\Mapping\MetadataFactory;
use Mandango\Connection;
use Mandango\Cache\FilesystemCache;
function mandango_get() {
    return getMandango();
}

function getMandango() {

        $metadataFactory = new MetadataFactory();
        $cache = getCache();
        $mandango = new Mandango($metadataFactory, $cache);

        $connection = new Connection("mongodb://mongo:27017/blazemeter", 'blazemeter', []);
        $mandango->setConnection('default', $connection);
        $mandango->setDefaultConnectionName('default');

    return $mandango;
}

function getCache() {
    return new FilesystemCache("/tmp/a.blazemeter.com/");
}
