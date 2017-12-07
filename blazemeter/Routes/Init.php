<?php

use blazemeter\Model\Vegetable;
use blazemeter\Model\Sauce;
use blazemeter\Model\EmployeeRepository;
use blazemeter\Model\OrderRepository;
use blazemeter\Model\VegetableRepository;
use blazemeter\Model\SauceRepository;
class Init{


    private $vegetableTypes =
    [        "Carrot",
             "Lettuce",
             "Tomato",
             "onion",
             "Radish",
             "Spinach",
             "Zucchini",
             "Cucumber ",
             "Wasabi",
             "Garlic"
                ];

    private $sauceTypes =
        [    "Avgolemono",
             "Coulis",
             "Ketchup",
             "Mustard",
             "Gravy",
             "Chili",
             "Mayonnaise",
             "barbecue ",
             "Honey",
             "Thousand Islands"
        ];

    /**
     * Auto routed method which maps to POST api/init/
     *
     *
     * @return mixed
     * @throws RestException
     * @status 201
     */

    public function postInit(){

        EmployeeRepository::get()->getCollection()->drop();
        OrderRepository::get()->getCollection()->drop();

        if ($this->shouldInitVegsAndSauces()){
            for ( $i = 0; $i <= count($this->vegetableTypes); $i++ ) {
                $vegetable = Vegetable::create();
                $vegetable->setName($this->vegetableTypes[$i]);
                $vegetable->save();
            }

            for ( $i = 0; $i <= count($this->sauceTypes); $i++ ) {
                $sauce = Sauce::create();
                $sauce->setName($this->sauceTypes[$i]);
                $sauce->save();
            }
        }

        return "success";
    }

    private function shouldInitVegsAndSauces(){
        $vegs = VegetableRepository::get()->getCollection();
        return ($vegs->count() == 0);
    }
}
