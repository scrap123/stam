<?php

namespace blazemeter\Model;

/**
 * Repository of blazemeter\Model\Order document.
 */
class OrderRepository extends \blazemeter\Model\Base\OrderRepository
{
    public function getEmployeeMaxOrder($isMax){

        $pipeline = [
            [
                '$group' => [
                    '_id' => '$employee',
                    'number_of_orders' => [
                        '$sum' => 1,
                    ],
                ],
            ],
            [
                '$sort' =>
                    [number_of_orders => $isMax ? -1 : 1],

            ],
            [
                '$limit' =>
                    1,

            ],

        ];

        $result = $this->getCollection()->aggregate($pipeline);

        return $this->wrapEmployeeFromAggregate($result);
    }

    private function wrapEmployeeFromAggregate($maxEmployee){

        if ($maxEmployee && $maxEmployee['result']){
            $empid =  $maxEmployee['result'][0]['_id'];
            $emp = Employee::load((int) $empid);
            if ($emp){
                $empAsArr = $emp->toArray();
                $empAsArr['number_of_orders'] = $maxEmployee['result'][0]['number_of_orders'];
                return $empAsArr;
            }
        }
        return null;
    }
}
