<?php

require_once 'vendor/restler.php';
require_once('./vendor/autoload.php');
require_once('pepper_mongo.php');
require_once('Routes/Employees.php');
require_once('Routes/Orders.php');
require_once('Routes/Init.php');

require 'vendor/predis/predis/autoload.php';

use Luracast\Restler\Restler;




$r = new Restler();
$r->addAPIClass('Employees', '/');
$r->addAPIClass('Orders', '/');
$r->addAPIClass('Init', '/');
$r->handle();


