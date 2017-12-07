<?php
namespace blazemeter\Routes;
use Predis\Client;

class BlazeRedisManager {
    private $groupKey;
    /** @var  Client */
    private $client;

    //caching param
    const REDIS_KEEP = 30;

    function __construct($groupKey) {
        $this->groupKey = $groupKey;
    }

    public function setGroupKey($groupKey) {
        $this->groupKey = $groupKey;
    }

    function connect(){
        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => 'redis',
            'port'   => 6379,
        ]);

    }

    // use with caution, will delete all redis data
    function flushAll() {
        $this->client->flushall();
    }

    private function createHash($arr) {
        return implode($arr);
    }

    function disconnect(){
        if ($this->client){
            $this->client->disconnect();
        }
    }

    function getCachedResponse($keyArr){
        $requestKey = $this->createHash($keyArr);
        $response = $this->client->get($requestKey);
        if ($response){
            return json_decode($response);
        }
        return null;
    }

    function addRequestToCache($keyArr, $response){

        $requestKey = $this->createHash($keyArr);

        $apiResponse = new ApiResponse($response);
        $apiResponse->setCached(true);

        $this->client->setex($requestKey, self::REDIS_KEEP, json_encode($apiResponse));
        $this->client->sadd($this->groupKey, $requestKey);
    }

    function onCacheCompromised(){
        $requestKeys =  $this->client->smembers($this->groupKey);

        if ($requestKeys){
            $this->client->del($requestKeys);
            foreach ($requestKeys as $singleKey) {
                $this->client->srem($this->groupKey, $singleKey);
            }
        }
    }
}
