<?php

namespace Mautic\WebhookBundle\utils;

use GuzzleHttp\Client;
use Mautic\CoreBundle\Controller\CommonController;
use Mautic\LeadBundle\Entity\Company;
use Mautic\LeadBundle\Entity\Lead;

class RequestResponseProcessing extends CommonController
{

    public $method = '';
    public $url = '';
    public $headers = '';
    public $authType = '';
    public $authData = [];
    public $login = '';
    public $password = '';
    public $token = '';
    public $options = [];
    public $fieldsWithValues = [];
    public $actualLoad = [];
    public $leadField = '';
    public $leadValue = '';
    public $subject_entity_id =  '';
    public $lead =  '';
    public $client = '';
    public $extra = '';
    public $subject = '';
    public $subject_entity = '';
    public $payload = '';
    public $responseData = '';


    public function __construct(
        $url, $method, $headers, $authType, $login, $password, $token, $actualLoad, $fieldsWithValues, $subject_entity_id, $factory, $extra,
        $subject, $payload
    )
    {
        $this->url = $url;
        $this->method = $method;
        $this->headers = $this->parseHeaders($headers);
        $this->authType = $authType;
        $this->login = $login;
        $this->password = $password;
        $this->token = $token;
        $this->actualLoad = $this->parseActualLoad($actualLoad);
        $this->fieldsWithValues = $this->parseFieldsWithValues($fieldsWithValues);
        $this->subject_entity_id = $subject_entity_id;
        $this->factory = $factory;
        $this->extra = $extra;
        $this->subject = $subject;
        $this->payload = $payload;
    }


    public function startProcessing(){

        # set subject table
        switch ($this->subject){
            case "Contact":
                $table = 'leads';
                $repository = Lead::class;
                break;
            case "Company":
                $table = 'companies';
                $repository = Company::class;
                break;
            default:
                $this->subject = "Contact";
                $table = 'leads';
                $repository = Lead::class;
                break;
        }

        # get entity
        $repository = $this->factory->getEntityManager()->getRepository($repository);
        $this->subject_entity = $repository->getEntity($this->subject_entity_id);

        # config auth type
        $this->setHeaders();
        switch ($this->authType) {
//            case "Other":
//                $this->options['auth'] = $this->authOther();
//                break;
            case "Basic":
//                $this->options['auth'] = $this->authBasic();
                $this->options['headers']['Authorization'] = ['Basic'. base64_encode($this->login.':'.$this->password)];
                break;
            case "Token":
//                $this->options['auth'] = $this->authToken();
                $this->options['headers']['Authorization'] = ['Bearer ' . $this->token];
                break;
            default:
                $this->authType = "Other";
                break;
        }

        # get data according method
        switch ($this->method) {
            case "GET":
                $response = $this->sendGet();
                $result = $this->processResponse($response);
                break;
            case "POST":
                $response = $this->sendPost();
                $result = $this->processResponse($response);
                break;
            default:
                $this->method = 'GET';
                $response = $this->sendGet();
                $result = $this->processResponse($response);
                break;
        }

        # set data to DB
        if(isset($result)){
            $command = 'UPDATE';
            $set = 'SET';
            $where = 'WHERE';
            $conn = $this->factory->getEntityManager()->getConnection();
            foreach ($result as $key => $value){
                $sql = $command.' '.$table.' '.$set.' '.$key.' '.'='.' '.'"'.str_replace("'", "''",($value[array_keys($value)[0]])).'"'.' '.$where.' '.'id'.' '.'='.' '.$this->subject_entity_id;
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            }
        }
        return [$response, $this->responseData];
    }

    public function setHeaders()
    {
        $this->options['headers'] = $this->headers;
    }

    public function processResponse($response)
    {
        $responseList = json_decode($response->getBody(), true);
        $this->responseData = implode($responseList);
        $tempList = [];
        $resultsList = [];
        foreach ($this->fieldsWithValues as $key => $value){
            $result = $this->search_key($key, $responseList, $tempList);
            if ($result){$resultsList[$value] = [$key => $result[array_keys($result)[0]]];}
            unset($result);
            $tempList = [];
        }
        return $resultsList;
    }

    public function sendGet()
    {
        $this->client = new Client(['headers' => $this->options['headers']]);
        return $this->client->request($this->method, $this->url);
    }

    public function sendPost()
    {
        # SET $actualLoad (if not payload)
//        $postData = [];
//        foreach ($this->actualLoad as $field){
//            $postData[$field] = $this->subject_entity->$field;
//        }
//        $this->options['body'] = $postData;
        $this->options['body'] = $this->payload;
        $this->client = new Client(['headers' => $this->options['headers']]);
        return $this->client->request($this->method, $this->url, ['json' => $this->options['body']]);
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

    public function parseActualLoad($load_str){
        $actualLoadList = [];
        if (!$load_str == '') {
            if (!str_contains($load_str, ',')) {
                $actualLoadList[] = trim($load_str);
            } else {
                $load_list_temp = explode(',', $load_str);
                foreach ($load_list_temp as $item)
                    $actualLoadList[] = trim($item);
            }
        }
        return $actualLoadList;
    }

    public function parseFieldsWithValues($fieldsWithValues_str){
        $fieldsWithValues_list = [];
        if (!$fieldsWithValues_str == '') {
            if (str_contains($fieldsWithValues_str, ':')) {
                if (str_contains($fieldsWithValues_str, ',')) {
                    $fieldsWithValues_list_temp = explode(',', $fieldsWithValues_str);
                    foreach ($fieldsWithValues_list_temp as $item_key => $item_value) {
                        $pair = explode(':', $item_value);
                        $key = trim($pair[0]);
                        $value = str_replace(' ', '_', trim($pair[1]));
                        $fieldsWithValues_list[$key] = $value;
                    }
                }else{
                    $pair = explode(':', $fieldsWithValues_str);
                    $key = trim($pair[0]);
                    $value = str_replace(' ', '_', trim($pair[0]));
                    $fieldsWithValues_list[$key] = $value;
                }
            }else{
                $error = 'field format should be: received data:contact_field. Example: voucher:voucher';
            }
        }
        return $fieldsWithValues_list;
    }

    public function parseHeaders($fieldsWithValues_str){
        $fieldsWithValues_list = [];
        if (!$fieldsWithValues_str == '') {
            if (str_contains($fieldsWithValues_str, ':')) {
                if (str_contains($fieldsWithValues_str, ',')) {
                    $fieldsWithValues_list_temp = explode(',', $fieldsWithValues_str);
                    foreach ($fieldsWithValues_list_temp as $item_key => $item_value) {
                        $pair = explode(':', $item_value);
                        $key = trim($pair[0]);
                        $value = trim($pair[1]);
                        $fieldsWithValues_list[$key] = $value;
                    }
                }else{
                    $pair = explode(':', $fieldsWithValues_str);
                    $key = trim($pair[0]);
                    $value = trim($pair[1]);
                    $fieldsWithValues_list[$key] = $value;
                }
            }else{
                $error = 'field format should be: received data:contact_field. Example: voucher:voucher';
            }
        }
        return $fieldsWithValues_list;
    }
}