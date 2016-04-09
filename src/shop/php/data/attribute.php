<?php


///Class representing one product attribute.
class Attribute {
    public $name, $value, $formatString;
    /**
     *
     * @param <string> $name
     * @param <string> $value
     * @param <string> $formatString
     */
    function __construct($name, $value, $formatString){
        $this->name = $name;
        $this->value = $value;
        $this->formatString = $formatString;
    }
}
?>
