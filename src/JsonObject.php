<?php
namespace sobernt\JsonObject;
use DateTime;
use JsonSerializable;
use sobernt\JsonObject\Exceptions\InvalidArgumentException;
use sobernt\JsonObject\Exceptions\JsonException;


/**
 * Class JsonObject
 * @package JsonObject
 */
class JsonObject implements JsonSerializable
{
    /**
     * @var array $cache - array of properties
     */
    private $cache;

    private $formatter;

    /**
     * JsonObject constructor.
     * @param string $json - string for full parse
     * @param int $max_depth - max depth of recursion
     * @throws JsonException if has json validate errors
     */
    public function __construct($json=null,$max_depth=255)
    {
        $this->max_depth = $max_depth;
        $this->cache=[];
        $this->setDefaultFormatter();

        if(is_null($json)) return;

        $result=json_decode($json,true,$max_depth);

        if(json_last_error()!=JSON_ERROR_NONE){
            throw new JsonException("Json can not be parsed.",400);
        }
        $this->cache = $this->arrayChild($result);
    }

    /**
     * jq-like query.
     * support $key as prop.subprop or prop.subprop[1] if subprop is array
     * no use . for root
     * @param string $key - key for getter
     * @return mixed - value by it key or null
     */
    public function filter(string $key)
    {
        $keys = explode ( '.' , $key);
        $arrIndex=null;
        $thisKey=array_shift ($keys);
        if(preg_match("/([a-zA-Z_][a-zA-Z_0-9]*?)\[(\d)\]/",$thisKey,$keyMath)){//this attr key is arr[0]
            $thisKey=$keyMath[1];
            $arrIndex=$keyMath[2];
        }
        if(array_key_exists($thisKey,$this->cache)){
            if($this->cache[$thisKey] instanceof JsonObject){
                return $this->cache[$thisKey]->filter(implode ( "." , $keys ));
            } else{
                if(is_array($this->cache[$thisKey])) {
                    if (is_null($arrIndex)) {
                        return $this->cache[$thisKey];
                    } else {
                        if($this->cache[$thisKey][$arrIndex] instanceof JsonObject) {
                            return $this->cache[$thisKey][$arrIndex]->filter(implode ( "." , $keys ));
                        } else{
                            $this->cache[$thisKey][$arrIndex];
                        }
                    }
                } else{
                    return $this->cache[$thisKey];
                }
            }
        } else return null;
    }

    /**
     * getter for other property
     * @param $name - name of property
     * @return mixed|null - php primitive, object or other, by property
     * @throws InvalidArgumentException - if argument non't in json structure
     */
    public function __get($name)
    {
        if(array_key_exists($name,$this->cache)){
            var_dump($this->cache[$name]);
            return $this->cache[$name];
        }else{
            throw new InvalidArgumentException("structure element not found.",404);
        }
    }

    /**
     * @param string $name
     * @param $value
     * @throws InvalidArgumentException
     */
    public function __set(string $name, $value)
    {
        if(!(
            ($value instanceof JsonObject) ||
            is_string($value)
        )) throw new InvalidArgumentException("you can set only accept types.",400);
        $this->cache[$name]=$value;
    }

    /**
     * unpack values from array in cache. Uses for recursion call this object.
     * do this class cached.
     * do not recommend use this method, but it's really for changing work(cached)
     * structure without change Raw json
     * @param array $data - data for fake create recursive object from this class(or no this)
     * @throws \Exception
     */
    public function __invoke(array $data)
    {
        $this->cache = $this->arrayChild($data);
    }

    /**
     * @return array - keys for this object
     */
    public function __sleep()
    {
        return array_keys($this->cache);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->cache;
    }

    /**
     * @return array
     */
    public function jsonSerialize():array
    {
        return $this->cache;
    }

    private function setDefaultFormatter(){
        $this->setFormatter(function(string $data,string $name=null)
    {
        if(preg_match("/^[0-9]{1,19}$/",$data)){
            return intval($data);
        }
        if(is_double($data)){
            return doubleval($data);
        }
        //data in format YYYY-MM-DD OR YYYY-MM-DD hh:mm:ss
        if(preg_match('/^([\d]{4})-([\d]{1,2})-([\d]{1,2}) (([\d]{1,2}):([\d]{1,2}):([\d]{1,2}))?$/', $data, $data_matches)){
            $date = new DateTime();
            $date->setDate($data_matches[1],$data_matches[2],$data_matches[3]);

            if(sizeof($data_matches)==7){//with time
                $date->setTime($data_matches[5],$data_matches[6],$data_matches[7]);
            }

            return $date;
        }

        return $data;
    });
    }

    /**
     * checking string on non-array and non-obj type
     * @param $value - checked value
     * @return bool if true
     */
    private function isPrimitiveChild($value):bool
    {
        return !is_array($value)&&
               !$this->isAssoc($value);
    }

    /**
     * @param $formatter - formatter callback for primitive types. return primitive type.
     * @throws InvalidArgumentException
     */
    public function setFormatter($formatter){
        if(!is_callable($formatter)){
            throw new InvalidArgumentException("invalid formatter function.it's must be callable",400);
        }
        $this->formatter=$formatter;
    }

    /**
     * @param array $array - array for check
     * @return bool - true if checked is assoc array
     */
    private function isAssoc(array $array){
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * @param array $array child array
     * @return array with validated child's
     * @throws JsonException
     */
    private function arrayChild(array $array):array
    {
        $result=[];
        foreach ($array as $key=>$value){
            if(is_array($value)) {
                if ($this->isAssoc($value)) {
                    $result[$key] = $this->getChildFromAssoc($value);
                } else {
                    $result[$key] = $this->arrayChild($value);
                }
            } else{
                $result[$key]=($this->formatter)($value,$key);
            }
        }
        return $result;
    }

    /**
     * @param array|null $array
     * @return JsonObject - child for this
     * @throws JsonException if child structure has errors
     */
    private function getChildFromAssoc(array $array = null):JsonObject
    {
        $res = new JsonObject(null);
        if(!is_null($array)) {
            $res($array);
        }
        return $res;
    }


}