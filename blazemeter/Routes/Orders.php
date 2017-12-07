<?php
use blazemeter\Model\Order;
use blazemeter\Model\OrderRepository;
use blazemeter\Model\Employee;
use blazemeter\Model\Sauce;
use blazemeter\Model\Vegetable;
use blazemeter\Routes\ApiResponse;
use Luracast\Restler\RestException;
use blazemeter\Routes\PepperObject;

class Orders extends PepperObject{

    private $saladOptions = ["Tuna", "vegetable", "chicken", "feta", "avocado"];

    //error messges
    const ERROR_ORDER_NON_EXIST = "Order does not exist";
    const ERROR_SALAD_NON_EXIST = "Salad type does not exist";
    const ERROR_EMPLOYEE_NON_EXIST = "employee does not exist";
    const ERROR_INVALID_SAUCE_IDS = "Invalid Sauce ids";
    const ERROR_INVALID_VEGETABLE_IDS = "Invalid Vegetable ids";

    //redis group key
    const REDIS_GROUP_KEY = "orders_group_key";


    function __construct() {

    }

    /**
     * @url POST order/
     *
     * @param integer $saladType type of salad to go with the order {@from body}
     * @param string $comments users added comments {@from body}
     * @param integer $employeeId of employee who made the order {@from body}
     * @param array $sauceIds ids of sauce {@from body}
     * @param array $vegetableIds ids of vegetable {@from body}
     *
     * @return mixed
     * @throws RestException
     * @status 201
     */

    public function createOrder($saladType, $comments, $employeeId, $sauceIds = [], $vegetableIds = []){

      //verifications and get initial params
      $this->verifySaladType($saladType);
      $emp = $this->getEmployee($employeeId);
      $sauces = $this->getSauces($sauceIds);
      $vegetables = $this->getVegetables($vegetableIds);

      //Initializing the order and saving it
      $order = Order::create();
      $order->setSaladType($saladType);
      $order->setComments($comments);
      $order->setEmployee($emp);
      $order->addSauces($sauces);
      $order->addVegetables($vegetables);
      $order->save();


      $this->deleteCache(self::REDIS_GROUP_KEY, []);

      return $this->wrapOrderToApiResponse($order, $sauces, $vegetables, $emp);

    }

    /**
     * @url GET order/{id}
     * @param integer $id the id of the order {@from path}
     *
     * @return ApiResponse
     * @throws RestException
     */
     public function getOrders($id){

         $orders = OrderRepository::get()->findOneById($id);
         if ($orders){
             return (new ApiResponse($orders->toArray()));
         }else{
             throw new RestException(404);
         }
    }

    /**
     * * GET api/orders returns all orders
     *
     * @return ApiResponse
     */

    public function getOrder(){

        $keyArr = ["orders"];
        $cached = $this->getCache($keyArr, self::REDIS_GROUP_KEY);

        if ($cached){
            return $cached;
        }

        $orders = OrderRepository::get()->createQuery([
            //get all
        ])->references(['employee', 'sauces', 'vegetables'])
            ->all();


        $temp = array_values(array_map(function (Order $item) {

            $arr = $item->toArray();
            $arr['employee'] = $item->getEmployee()->toArray();

            //http://mandango.readthedocs.io/en/latest/mandango/querying.html#references-many
            $vegs = $item->getVegetables()->all();

            $vegetablesArr = array_values(array_map(function (Vegetable $item) {
                return $item->toArray();
            }, $vegs));
            $arr['vegetables'] = $vegetablesArr;

            $sauces = $item->getSauces()->all();

            $sauceArr = array_values(array_map(function (Sauce $item) {
                return $item->toArray();
            }, $sauces));
            $arr['sauces'] = $sauceArr;
            $arr['saladType'] = $this->saladOptions[$arr['saladType']];
            return $arr;
        }, $orders));

        $this->insertToCache($keyArr, self::REDIS_GROUP_KEY, $temp);
        $apiResponse = new ApiResponse($temp);

        return $apiResponse;
    }

    /**
     * @url PATCH order/{id}
     *
     * @param integer $id the id of the order {@from path}
     * @param integer $saladType type of salad to go with the order {@from body}
     * @param string $comments users added comments {@from body}
     * @param array $sauceIds ids of sauce {@from body}
     * @param array $vegetableIds ids of vegetable {@from body}
     *
     * @return mixed
     * @throws RestException
     * @status 201
     */
    public function updateOrder($id, $saladType = null, $comments = null, $sauceIds = null, $vegetableIds = null){

        //verifications and get initial params

        if ($saladType){
            $this->verifySaladType($saladType);
        }
        $sauces = null;
        $vegetables = null;
        if ($sauceIds){
            $sauces = $this->getSauces($sauceIds);
        }

        if ($vegetableIds){
            $vegetables = $this->getVegetables($vegetableIds);
        }

        $order = Order::load((int) $id);

        if (!$order){
            throw new RestException(400, self::ERROR_ORDER_NON_EXIST);
        }

        if (!is_null($comments)){
            $order->setComments($comments);
        }

        if (!is_null($saladType)){
            $order->setSaladType($saladType);
        }

        if (!is_null($sauceIds)){
            $oldSauces = $order->getSauces()->all();
            $order->removeSauces($oldSauces);
            $order->addSauces($sauces);
        }

        if (!is_null($vegetableIds)){
            $oldVegetables = $order->getVegetables()->all();
            $order->removeVegetables($oldVegetables);
            $order->addVegetables($vegetables ? $vegetables : null);
        }
        $order->save();
        $this->deleteCache(self::REDIS_GROUP_KEY, []);

        return $this->wrapOrderToApiResponse($order, $sauces, $vegetables, $order->getEmployee());
    }

    /**
     *
     * @url DELETE order/{id}
     *
     * @param string $id the id of the order being deleted {@from path}
     *
     * @return mixed
     * @throws RestException
     * @status 204
     */
    public function deleteOrder($id){

        $order = Order::load((int) $id);
        if (!$order){
            throw new RestException(404, self::ERROR_ORDER_NON_EXIST);
        }
        $order->delete();
        $apiResponse = new ApiResponse($order->toArray());
        $this->deleteCache(self::REDIS_GROUP_KEY, []);
        return $apiResponse;
    }

    /**
     *
     * @url DELETE order/{id}
     *
     * @param Order $order the order to wrap
     *
     * @return mixed
     * @throws RestException
     * @status 204
     */
    private function wrapOrderToApiResponse($order, $sauces = null, $vegetables = null, $emp = null){

        $arr = $order->toArray();
        $arr['employee'] = $emp->toArray();
        $saucesArr = array_values(array_map(function (Sauce $item) {
            return $item->toArray();
        }, $sauces));
        $arr['sauces'] = $saucesArr;
        $vegetablesArr = array_values(array_map(function (Vegetable $item) {
            return $item->toArray();
        }, $vegetables));
        $arr['vegetables'] = $vegetablesArr;
        $apiResponse = new ApiResponse($arr);
        return $apiResponse;
    }

    private function verifySaladType($saladType){
        $requestedSaladType =  $this->saladOptions[$saladType];
        if (is_null($requestedSaladType)){
            throw new RestException(400, self::ERROR_SALAD_NON_EXIST);
        }
    }

    private function getEmployee($employeeId){
        $emp = Employee::load((int) $employeeId);
        if (is_null($emp)){
            throw new RestException(400, self::ERROR_EMPLOYEE_NON_EXIST);
        }
        return $emp;
    }

    /**
     *
     * @param array $sauceIds sauce ids to pull from db
     *
     * @return Sauce
     * @throws RestException
     */
    private function getSauces($sauceIds){

        $saucesFromDb = Sauce::loadMany($sauceIds);
        if (sizeof($saucesFromDb) != sizeof($sauceIds)){
            throw new RestException(400, self::ERROR_INVALID_SAUCE_IDS);
        }
        return $saucesFromDb;
    }


    /**
     *
     * @param array $vegetableIds vegetable ids to pull from db
     *
     * @return Vegetable
     * @throws RestException
     */
    private function getVegetables($vegetableIds){

        $vegetablesFromDb = Vegetable::loadMany($vegetableIds);
        if (sizeof($vegetablesFromDb) != sizeof($vegetableIds)){
            throw new RestException(400, self::ERROR_INVALID_VEGETABLE_IDS);
        }
        return $vegetablesFromDb;
    }


}
