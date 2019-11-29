<?php

use sobernt\JsonObject\Exceptions\InvalidArgumentException;
use sobernt\JsonObject\JsonObject;

require("vendor/autoload.php");

    $obj = new sobernt\JsonObject\JsonObject("{
        \"testkey\":\"testval\",
        \"testarray\":[
            \"testsimplearrayval1\",
            \"testsimplearrayval2\"
        ],
        \"testcompositearray\":[
            \"testcompositearrayval1\",
            {
                 \"testcompositearray2key\": \"testcompositearray2value\"
            }
        ],
         \"testobject\":{
                 \"testobjectkey\": \"testobjectval\",
                 \"testobjectintkey\": \"1\"
        }
    }");
    var_dump($obj);
    echo("\ntestkey:");
    var_dump($obj->testkey);
    echo("\ntestarray:");
    var_dump($obj->testarray);
    echo("\ntestcompositearray:");
    var_dump($obj->testcompositearray);
    echo("\ntestcompositearray[1]:");
    var_dump($obj->testcompositearray[1]);
    echo("\ntestobject:");
    var_dump($obj->testobject);
    echo("\ntestobject->testobjectkey:");
    var_dump($obj->testobject->testobjectkey);
    echo("\n->testobject->testobjectintkey:");
    var_dump($obj->testobject->testobjectintkey);
    echo("\nobj:");
    var_dump($obj);
        $obj->prop="testprop";
    echo("\njson_encode(obj):");
    var_dump(json_encode($obj));
    echo("\nfilter testcompositearray[1].testcompositearray2key:");
    var_dump($obj->filter("testcompositearray[1].testcompositearray2key"));


    try{
        var_dump($obj->tst);
    }catch (InvalidArgumentException $e){
        var_dump($e);
    }

    try{
        $obj = new JsonObject("testval");
    }catch (\sobernt\JsonObject\Exceptions\JsonException $e){
        var_dump($e);
    }