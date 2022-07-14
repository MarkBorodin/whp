<?php

namespace Mautic\WebhookBundle\utils;

use Mautic\CoreBundle\Controller\CommonController;

class PremiumFunctionality extends CommonController
{

    public function __construct($factory)
    {
        $this->factory = $factory;
    }


    public function writeToDB($result, $subject_entity_id)
    {
        $command = 'UPDATE';
        $set = 'SET';
        $where = 'WHERE';
        $table = 'leads';
        $conn = $this->factory->getEntityManager()->getConnection();
        foreach ($result as $key => $value){
            $sql = $command.' '.$table.' '.$set.' '.$key.' '.'='.' '.'"'.str_replace("'", "''",($value[array_keys($value)[0]])).'"'.' '.$where.' '.'id'.' '.'='.' '.$subject_entity_id;
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
    }


    public function processResponse($response, $fieldsWithValues)
    {
        $responseList = json_decode($response->body, true);
        $tempList = [];
        $resultsList = [];
        foreach ($fieldsWithValues as $key => $value){
            $result = $this->search_key($key, $responseList, $tempList);
            if ($result){$resultsList[$value] = [$key => $result[array_keys($result)[0]]];}
            unset($result);
            $tempList = [];
        }
        return $resultsList;
    }


    public function search_key($searchKey, array $arr, array &$result)
    {
        if (isset($arr[$searchKey])) {
            $result[] = $arr[$searchKey];
        }
        foreach ($arr as $key => $param) {
            if (is_array($param)) {
                $this->search_key($searchKey, $param, $result);
            }
        }
        return $result;
    }

}