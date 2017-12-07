<?php

use blazemeter\Model\Employee;
use blazemeter\Model\EmployeeRepository;
use blazemeter\Model\OrderRepository;
use blazemeter\Routes\ApiResponse;
use blazemeter\Routes\PepperObject;
use Luracast\Restler\RestException;

class Employees extends PepperObject{

    //error messges
    const ERROR_EMPLOYEE_NON_EXIST = "employee does not exist";

    //redis group key
    const REDIS_GROUP_KEY = "employee";


    function __construct() {

    }

    /**
     * @url POST employee/
     *
     * @param string $name name of employee {@from body}
     * @param string $imageUrl profile pic of the employee {@from body}
     *
     * @return ApiResponse
     * @throws RestException
     * @status 201
     */
    public function createEmployee($name, $imageUrl){

        $this->verifyString("name", $name);
        $this->verifyUrl("imageUrl", $imageUrl);
        $employee = Employee::create();
        $employee->setName($name);
        $employee->setImageURL($imageUrl);
        $employee->save();

        $apiResponse = new ApiResponse($employee->toArray());

        $this->deleteCache(self::REDIS_GROUP_KEY, []);
        return $apiResponse;

    }

    /**
     * @url GET employee
     * @param string $sort the field to sort by, it can be name or imageUrl {@from query}
     * @param bool $asc should the sort be ascending or descending {@from query}
     * @param string $search search criteria, will search the 'name' field for employees who start with this param {@from query}
     * @param int $skip {@from query}
     * @param int $limit {@from query}
     * @return mixed
     */
    public function getEmployee($sort = "", $asc = true, $search = "", $skip = 0, $limit = 100){

        $keyArr = [$sort, $asc, $search, $skip, $limit];
        $cached = $this->getCache($keyArr, self::REDIS_GROUP_KEY);

        if ($cached){
           return $cached;
        }

        $employees = EmployeeRepository::get()->createQuery([
            //get all
        ])->sort(!empty($sort) ?  array($sort => $asc ? 1 : -1) : null)
            ->criteria(!empty($search) ? array('name' => array('$regex' => '^'.$search)) : array())
            ->skip($skip)
            ->limit($limit)
            ->all();
        $temp = array_values(array_map(function (Employee $item) {
            return $item->toArray();
        }, $employees));

        $this->insertToCache($keyArr, self::REDIS_GROUP_KEY, $temp);
        $apiResponse = new ApiResponse($temp);

        return $apiResponse;
    }

    /**
     *
     * @url PATCH employee/{id}
     *
     * @param string $id the id of the employee being updated {@from path}
     * @param string $name the name of the employee to edit {@from body}
     * @param string $imageUrl the imageUrl of the employee to edit {@from body}
     *
     * @return mixed
     * @throws RestException
     */
    public function updateEmployee($id, $name = null, $imageUrl = null){

        if ($name){
            $this->verifyString("name", $name);
        }
        if ($imageUrl){
            $this->verifyUrl("imageUrl", $imageUrl);
        }


        $emp = $this->loadEmployee($id);

        if (!$emp){
            throw new RestException(400, self::ERROR_EMPLOYEE_NON_EXIST);
        }

        if (!is_null($name)){
            $emp->setName($name);
        }

        if (!is_null($imageUrl)){
            $emp->setImageURL($imageUrl);
        }

        $emp->save();

        $apiResponse = new ApiResponse($emp->toArray());
        $this->deleteCache(self::REDIS_GROUP_KEY, []);
        return $apiResponse;
    }

    /**
     *
     * @url DELETE employee/{id}
     *
     * @param string $id the id of the employee being deleted {@from path}
     *
     * @return mixed
     * @throws RestException
     * @status 204
     */
    public function deleteEmployee($id){

        $emp = $this->loadEmployee($id);

        if (!$emp){
            throw new RestException(404, self::ERROR_EMPLOYEE_NON_EXIST);
        }

        $emp->delete();
        $apiResponse = new ApiResponse($emp->toArray());
        $this->deleteCache(self::REDIS_GROUP_KEY, [Orders::REDIS_GROUP_KEY]);
        return $apiResponse;
    }

    /**
     * @url GET employee-min-order
     * @return mixed the employee with the least amount of orders, or proper message if there are no orders.
     */
    public function getEmployeeMinOrder(){

        return $this->getEmployeeEdgeOrderHelper(false);
    }

    /**
     * @url GET employee-max-order
     * @return ApiResponse the employee with the most orders, or proper message if there are no orders.
     */
    public function getEmployeeMaxOrder(){

        return $this->getEmployeeEdgeOrderHelper(true);
    }

    /**
     * Helper function to return min or max employee according to employee with max/min number of orders
     *
     * @param boolean $isMax are we looking for maximum or minimum
     * @return ApiResponse
     */
    private function getEmployeeEdgeOrderHelper($isMax){

        $maxEmployee = OrderRepository::get()->getEmployeeMaxOrder($isMax);
        return $maxEmployee ? new ApiResponse($maxEmployee) : null;
    }

    /**
     * Helper function to load an employee using id, and throwing exceptions if neccacary
     *
     * @param int $id id of employee
     * @return Employee
     * @throws RestException
     */
     private function loadEmployee($id){
        if (!is_numeric($id)){
            throw new RestException(400, "id not legal");
        }

        try {
            return Employee::load((int)$id);
        } catch (Exception $e) {
            throw new RestException(400, $e->getMessage());
        }

    }
}



