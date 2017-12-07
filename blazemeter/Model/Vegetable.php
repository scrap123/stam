<?php
use blazemeter\Model\VegetableRepository;
namespace blazemeter\Model;

/**
 * blazemeter\Model\Vegetable document.
 */
class Vegetable extends \blazemeter\Model\Base\Vegetable
{
    static public function loadMany($ids){
        $vegetables = VegetableRepository::get()->createQuery([
            '_id' => array('$in' => $ids)
        ])->all();
        return $vegetables;
    }
}
