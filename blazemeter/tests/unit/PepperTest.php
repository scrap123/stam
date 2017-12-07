<?php

use blazemeter\Model\EmployeeRepository;
use blazemeter\Model\OrderRepository;
use blazemeter\Model\Sauce;
use blazemeter\Model\Vegetable;
class PepperTest extends \PHPUnit_Framework_TestCase{

    public function setUp(){

       EmployeeRepository::get()->getCollection()->drop();
       OrderRepository::get()->getCollection()->drop();
    }

    public function tearDown(){
        EmployeeRepository::get()->getCollection()->drop();
        OrderRepository::get()->getCollection()->drop();
    }

    public  function testEmployeeCreate(){

        $employees = new Employees();
        $employees->createEmployee('john_doe', "http://efgojeeew");

        $employeesFromDb = array_values(EmployeeRepository::get()->createQuery([
            'name'=>'john_doe'
        ])->all());

        $this->assertNotNull($employeesFromDb[0]);

        $this->assertEquals($employeesFromDb[0]->getName(),"john_doe");
        $this->assertEquals($employeesFromDb[0]->getImageURL(),"http://efgojeeew");

    }

     public function testEmployeeShortNameError(){

        $this->setExpectedException(\Luracast\Restler\RestException::class, "name must be at least 2 characters");
        $employees = new Employees();
        $employees->createEmployee('a', "http://efgojeeew");
     }

    public function testEmployeeBadUrlError(){

        $this->setExpectedException(\Luracast\Restler\RestException::class, "imageUrl url is not valid");
        $employees = new Employees();
        $employees->createEmployee('some name', "non valid url");
    }

    public function testEmployeeGet(){

        $name = "john_doe";
        $image = "http://efgojeeew";
        $employees = new Employees();
        $employees->createEmployee($name, $image);

        $employeesSaved = $employees->getEmployee()->getResult()[0];

        $this->assertEquals($employeesSaved['name'],$name);
        $this->assertEquals($employeesSaved['imageURL'],$image);
    }


    public function testEmployeeGet_sort(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $employees->createEmployee($name1, $image1);

        $name2 = "bbb";
        $image2 = "http://doesntmatter";
        $employees = new Employees();
        $employees->createEmployee($name2, $image2);

        // sort ascending
        $employeesList = $employees->getEmployee("name", true)->getResult();
        $employeesSaved1 = $employeesList[0];
        $employeesSaved2 = $employeesList[1];

        $this->assertEquals($employeesSaved1['name'], $name1);
        $this->assertEquals($employeesSaved2['name'], $name2);

        //sort descending

        $employeesList = $employees->getEmployee("name", false)->getResult();
        $employeesSaved1 = $employeesList[0];
        $employeesSaved2 = $employeesList[1];

        $this->assertEquals($employeesSaved1['name'], $name2);
        $this->assertEquals($employeesSaved2['name'], $name1);

    }

    public function testEmployeeGet_search(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $employees->createEmployee($name1, $image1);

        $name2 = "bbb";
        $image2 = "http://doesntmatter";
        $employees = new Employees();
        $employees->createEmployee($name2, $image2);

        // sort ascending
        $employeesList = $employees->getEmployee(null, true, "b")->getResult();
        $employeesSaved1 = $employeesList[0];
        $employeesSaved2 = $employeesList[1];

        $this->assertEquals($employeesSaved1['name'], $name2);
        $this->assertEquals($employeesSaved2, null);
    }

    /**
     * @dataProvider skipLimitProvider
     */
    public function testEmployeeGet_skip_and_limit($skip, $limit, $excpectedResult){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $employees->createEmployee($name1, $image1);

        $name2 = "bbb";
        $image2 = "http://doesntmatter";
        $employees = new Employees();
        $employees->createEmployee($name2, $image2);

        $employeesList = $employees->getEmployee(null, true, null, $skip, $limit)->getResult();
        $employeesSaved1 = $employeesList[0];
        $employeesSaved2 = $employeesList[1];

        $this->assertEquals($employeesSaved1['name'], $excpectedResult);
        $this->assertEquals($employeesSaved2, null);
    }

    public function skipLimitProvider()
    {
        return [
            [0, 1, "aaa"],
            [1, 1, "bbb"]
        ];
    }

    public function testEmployeeUpdate(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $newName = "new_name";
        $employees = new Employees();
        $apiResponse = $employees ->createEmployee($name1, $image1);

        $id = $apiResponse->getResult()['id'];
        $employees->updateEmployee($id, $newName);

        $employeesList = $employees->getEmployee()->getResult();
        $employeesSaved1 = $employeesList[0];
        $this->assertEquals($employeesSaved1['name'], $newName);
    }

    public function testEmployeeDelete(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $apiResponse = $employees ->createEmployee($name1, $image1);

        $id = $apiResponse->getResult()['id'];
        $employees->deleteEmployee($id);

        $employeesList = $employees->getEmployee()->getResult();

        $this->assertEquals(count($employeesList), 0);
    }


    public  function testOrderCreate(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $apiResponse = $employees ->createEmployee($name1, $image1);

        $empId = $apiResponse->getResult()['id'];

        $saladType = 1;
        $comment = "some random comment";
        $sauceIds = [1,4,5];
        $vegsIds = [2,6,7];
        $orders = new Orders();
        $apiResponse = $orders->createOrder($saladType, $comment, $empId, $sauceIds, $vegsIds);
        $id = $apiResponse->getResult()['id'];

        $ordersFromDb = array_values(OrderRepository::get()->createQuery([
            '_id'=>$id
        ])->all());

        $order1 = $ordersFromDb[0];
        $this->assertEquals($order1->getComments(), $comment);
        $this->assertEquals($order1->getSaladType(), $saladType);
        $this->assertEquals($order1->getEmployeeId(), $empId);

        $sauces = $order1->getSauces()->all();
        $vegetables= $order1->getVegetables()->all();

        $sauceFromDb = array_values(array_map(function (Sauce $item) {
            return $item->toArray()['id'];
        }, $sauces));

        $vegsFromDb = array_values(array_map(function (Vegetable $item) {
            return $item->toArray()['id'];
        }, $vegetables));

        $this->assertEquals($sauceFromDb, $sauceIds);
        $this->assertEquals($vegsFromDb, $vegsIds);
    }

    public function testOrderGet(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $apiResponse = $employees ->createEmployee($name1, $image1);

        $empId = $apiResponse->getResult()['id'];

        $saladType = 1;
        $comment = "some random comment";
        $sauceIds = [1,4,5];
        $vegsIds = [2,6,7];
        $orders = new Orders();
        $orders->createOrder($saladType, $comment, $empId, $sauceIds, $vegsIds);

        $orderUnderTest = $orders->getOrder()->getResult()[0];

        $sauceUnderTest = array_map(function ($item) {
            return $item['id'];
        }, $orderUnderTest['sauces']);

        $vegetablesUnderTest = array_map(function ($item) {
            return $item['id'];
        }, $orderUnderTest['vegetables']);

        $this->assertEquals($sauceUnderTest, $sauceIds);
        $this->assertEquals($vegetablesUnderTest, $vegsIds);
        $this->assertEquals($orderUnderTest['comments'], $comment);
        $this->assertEquals($orderUnderTest['employee']['id'], $empId);
        $this->assertEquals($orderUnderTest['saladType'], 'vegetable');
    }

    public function testOrderUpdate(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $apiResponse = $employees ->createEmployee($name1, $image1);

        $empId = $apiResponse->getResult()['id'];

        $saladType = 1;
        $comment = "some random comment";
        $sauceIds = [1,4,5];
        $vegsIds = [2,6,7];
        $orders = new Orders();
        $apiResponse = $orders->createOrder($saladType, $comment, $empId, $sauceIds, $vegsIds);
        $id = $apiResponse->getResult()['id'];

        $saladType = 2;
        $comment = "new Random comment";
        $sauceIdsUpdate = [1,4,8];
        $vegsIdsUpdate = [6,7,9];
        $orders = new Orders();
        $orders->updateOrder($id, $saladType, $comment, $sauceIdsUpdate, $vegsIdsUpdate);

        $ordersFromDb = array_values(OrderRepository::get()->createQuery([
            '_id'=>$id
        ])->all());

        $order1 = $ordersFromDb[0];
        $this->assertEquals($order1->getComments(), $comment);
        $this->assertEquals($order1->getSaladType(), $saladType);
        $this->assertEquals($order1->getEmployeeId(), $empId);

        $sauces = $order1->getSauces()->all();
        $vegetables= $order1->getVegetables()->all();

        $sauceFromDb = array_values(array_map(function (Sauce $item) {
            return $item->toArray()['id'];
        }, $sauces));

        $vegsFromDb = array_values(array_map(function (Vegetable $item) {
            return $item->toArray()['id'];
        }, $vegetables));

        $this->assertEquals($sauceFromDb, $sauceIdsUpdate);
        $this->assertEquals($vegsFromDb, $vegsIdsUpdate);
    }



    public function testOrderDelete(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $apiResponse = $employees ->createEmployee($name1, $image1);

        $empId = $apiResponse->getResult()['id'];

        $saladType = 1;
        $comment = "some random comment";
        $sauceIds = [1,4,5];
        $vegsIds = [2,6,7];
        $orders = new Orders();
        $apiResponse = $orders->createOrder($saladType, $comment, $empId, $sauceIds, $vegsIds);
        $id = $apiResponse->getResult()['id'];


        $orders->deleteOrder($id);

        $ordersList = $orders->getOrder()->getResult();

        $this->assertEquals(count($ordersList), 0);
    }


    public  function testOrderMinMax(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $apiResponse = $employees ->createEmployee($name1, $image1);

        $empId = $apiResponse->getResult()['id'];

        $saladType = 1;
        $comment = "some random comment";
        $sauceIds = [1,4,5];
        $vegsIds = [2,6,7];
        $orders = new Orders();
        $orders->createOrder($saladType, $comment, $empId, $sauceIds, $vegsIds);
        $orders->createOrder($saladType, $comment, $empId, $sauceIds, $vegsIds);

        // second employee

        $name2 = "bbb";
        $image2 = "http://doesntmatter";
        $apiResponse = $employees ->createEmployee($name2, $image2);

        $empId2 = $apiResponse->getResult()['id'];

        $saladType2 = 1;
        $comment2 = "some random comment";
        $sauceIds2 = [1,4,5];
        $vegsIds2 = [2,6,7];
        $orders->createOrder($saladType2, $comment2, $empId2, $sauceIds2, $vegsIds2);

        $result= $employees->getEmployeeMaxOrder()->getResult();
        $this->assertEquals($result['name'], $name1);
        $this->assertEquals($result['number_of_orders'], 2);

        $result= $employees->getEmployeeMinOrder()->getResult();
        $this->assertEquals($result['name'], $name2);
        $this->assertEquals($result['number_of_orders'], 1);
    }

    public  function testOrderMinMax_no_employees(){

        $employees = new Employees();
        $result = $employees->getEmployeeMaxOrder();
        $this->assertNull($result);
    }

    public  function testOrderMinMax_no_orders(){

        $name1 = "aaa";
        $image1 = "http://doesntmatter";
        $employees = new Employees();
        $employees ->createEmployee($name1, $image1);

        $result = $employees->getEmployeeMaxOrder();
        $this->assertNull($result);
    }
}
