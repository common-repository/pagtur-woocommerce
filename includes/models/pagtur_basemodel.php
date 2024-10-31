<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class PagTurBaseModel{
    


    public static function Deserialize($json){
        $classname = get_called_class();
        $classInstance = new $classname();
        if (is_string($json))
            $json = json_decode($json);
        
        foreach ($json as $key => $value){
            if (!property_exists($classInstance, str_replace('-','_',$key))) continue;
            $classInstance->{str_replace('-','_',$key)} = $value;
        }
        return $classInstance;
    }

    public static function DeserializeArray($json){
        $items = [];
        foreach ($json as $item)
            $items[] = self::Deserialize($item);
        return $items;
    }

//end class    
}

?>