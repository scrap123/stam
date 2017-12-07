<?php
namespace blazemeter\Routes;
class ApiResponse{

    public $result;
    public $cached;

    function __construct($result = null) {
        $this->result = $result;
        $this->cached = false;
    }

    /**
     * @return bool
     */
    public function isCached(){
        return $this->cached;
    }

    /**
     * @param bool $cached
     */
    public function setCached($cached){
        $this->cached = $cached;
    }

    /**
     * @return mixed
     */
    public function getResult(){
        return $this->result;
    }
}
