<?php

use blazemeter\Model\SauceRepository;
namespace blazemeter\Model;

/**
 * blazemeter\Model\Sauce document.
 */
class Sauce extends \blazemeter\Model\Base\Sauce
{
    static public function loadMany($ids){
        $sauces = SauceRepository::get()->createQuery([
            '_id' => array('$in' => $ids)
        ])->all();
        return $sauces;
    }
}
