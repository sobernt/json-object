**Install**

install this package via composer:

`composer require sobernt/json-object`

**Usage**

init object with minimal params:

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

full constructor:

`__construct($json,$max_depth=255)`

set `$max_depth = 500` for reconfigure object max recursion level = 500

throws `JsonException` if json can't be parse

you can use this as default object
    $obj->testobject
    
this example return array:

    $obj->testarray
    
this example returns object:
  
    $obj->testcompositearray[1]
  
throws `InvalidArgumentException` if you call object not founded in json

you can set our prop [string or JsonObject]:

    $obj->prop="testprop"
    
this example return value from tree:

    $obj->filter("testcompositearray[1].testcompositearray2key")
    
you can set your formatter for primitive objects:

     $this->setFormatter(function(string $data,string $name=null)
        {
        ..
        return $data;
        }
    )
    
formatter necessarily has 2 import params:data and name.
it's throw `InvalidArgumentException` if input is not callable.