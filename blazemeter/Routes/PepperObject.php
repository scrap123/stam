<?php
namespace blazemeter\Routes;
use Luracast\Restler\RestException;
use blazemeter\Routes\BlazeRedisManager;
class PepperObject{

    const MINIMUM_LENGTH = 2;
    protected function verifyString($fieldName ,$string){

        if (strlen($string) < self::MINIMUM_LENGTH){
            throw new RestException(400, $fieldName." must be at least ".self::MINIMUM_LENGTH." characters");
        }
    }

    protected function verifyUrl($fieldName, $url){

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RestException(400, "{$fieldName} url is not valid");
        }
    }

    protected function getCache($keyArr, $groupKey){
        $blazeRedisManager = new BlazeRedisManager($groupKey);
        $blazeRedisManager->connect();
        $cached = $blazeRedisManager->getCachedResponse($keyArr);
        $blazeRedisManager->disconnect();
        return $cached;
    }

    protected function insertToCache($keyArr, $groupKey, $response){

        $blazeRedisManager = new BlazeRedisManager($groupKey);
        $blazeRedisManager->connect();
        $blazeRedisManager->addRequestToCache($keyArr, $response);
        $blazeRedisManager->disconnect();
    }


    protected function deleteCache($groupKey, $dependencyKeys){
        $blazeRedisManager = new BlazeRedisManager($groupKey);
        $blazeRedisManager->connect();
        $blazeRedisManager->onCacheCompromised();

        if ($dependencyKeys){
            foreach ($dependencyKeys as $singleKey) {
                $blazeRedisManager->setGroupKey($singleKey);
                $blazeRedisManager->onCacheCompromised();
            }
        }


        $blazeRedisManager->disconnect();
    }
}
