<?php

$loader = require __DIR__.'/../vendor/.composer/autoload.php';
$loader->add('Mandango\\Tests', __DIR__);
$loader->add('Model', __DIR__);

// mondator
$configClasses = require __DIR__.'/config_classes.php';

use \Mandango\Mondator\Mondator;

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Mandango\Extension\Core(array(
        'metadata_factory_class'  => 'Model\Mapping\MetadataFactory',
        'metadata_factory_output' => __DIR__.'/Model/Mapping',
        'default_output'          => __DIR__.'/Model',
    )),
));
$mondator->process();
