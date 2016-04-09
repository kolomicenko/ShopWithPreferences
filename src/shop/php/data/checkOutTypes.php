<?php

/**
 * Class for preference objects that user chooses during check out (checkOut.php).
 */
class CheckOutObject{
    public $id;
    public $name;
    public $price;
    public $description;

    /**
     * Simple contructor.
     *
     * @param <int> $id
     * @param <string> $name
     * @param <float> $price
     * @param <string> $description
     */
    function __construct($id, $name, $price, $description){
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }
}

/// Represents one shipping option.
class ShippingType extends CheckOutObject{

    function __construct($id, $name, $price, $description){
        parent::__construct($id, $name, $price, $description);
    }
}

/// Represents one payment option.
class PaymentType extends CheckOutObject{

    function __construct($id, $name, $price, $description){
        parent::__construct($id, $name, $price, $description);
    }

}

?>
