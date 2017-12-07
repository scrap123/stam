<?php

namespace blazemeter\Model;

/**
 * blazemeter\Model\Order document.
 */
class Order extends \blazemeter\Model\Base\Order
{
    static public function load($id){
        if (!is_numeric($id)){
            throw new RestException(400, "id not legal");
        }

        try {
            return parent::load((int)$id);
        } catch (Exception $e) {
            throw new RestException(400, $e->getMessage());
        }

    }
}
