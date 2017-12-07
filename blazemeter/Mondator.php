<?php
require_once('./vendor/autoload.php');

// start Mondator
use Mandango\Extension\Core;
use Mandango\Mondator\Mondator;
use blazemeter\Common\Mandango\Extension;

$modelDir = __DIR__ . '/Model';
$mondator = new Mondator();


$mondator->setConfigClasses([
    'blazemeter\Model\Order' => [
         'idGenerator' => 'sequence',
         'collection' => 'orders',
         'fields' => [
            'saladType' => 'string',
             'comments' => 'string',
          ],
          'referencesOne' => [
            'employee' => ['class' => 'blazemeter\Model\Employee'],
          ],
          'referencesMany' => [
             'sauces' => ['class' => 'blazemeter\Model\Sauce'],
             'vegetables' => ['class' => 'blazemeter\Model\Vegetable'],
          ],
        ],
    'blazemeter\Model\Employee' => [
        'idGenerator' => 'sequence',
        'collection' => 'employees',
        'fields' => [
            'name' => 'string',
            'imageURL' => 'string',
         ],
        ],
    'blazemeter\Model\Vegetable' => [
        'idGenerator' => 'sequence',
        'collection' => 'vegetables',
        'fields' => [
            'name' => 'string',
        ],
    ],
    'blazemeter\Model\Sauce' => [
        'idGenerator' => 'sequence',
        'collection' => 'sauces',
        'fields' => [
            'name' => 'string',
        ],
    ],
    ]);


// assign extensions
$core = new Core([
    'metadata_factory_class' => 'blazemeter\Model\Mapping\MetadataFactory',
    'metadata_factory_output' => $modelDir . '/Mapping',
    'default_output' => $modelDir,
]);

$mondator->setExtensions([
    $core,
    new Extension\DocumentDeleteField(),
    new Extension\DocumentCreate(),
    new Extension\DocumentToResponse(),
]);

$mondator->process();
