<?php

/// Product class used by catalog.php.
class Product {
    ///Value added tax of the item. All items share the same VAT.
    public static $VAT = 0.2;

    public $name, $price, $VATPrice, $imagePath, $attributes, $id;

    /** Simple constructor.
     *
     * @param <string> $name
     * @param <float> $price
     * @param <string> $imagePath
     * @param <array(Attribute)> $attributes
     * @param <int> $id
     */
    function __construct($name, $price, $imagePath, $attributes, $id){

        $this->name = $name;
        $this->price = $price;
        $this->VATPrice = $price + $price * self::$VAT;
        $this->imagePath = $imagePath;
        $this->attributes = $attributes;
        $this->id = $id;
    }

    /**
     * Parses Sql result with products and their attributes. All attributes of
     * one product must be on consequtive lines for this function to work.
     *
     * @param <sql-result> $sqlResult result with products and their attributes
     * @param <int> $attrsToDisplay maximum number of attributes that each
     *                              product will have
     * @return <array> Array of Product
     */
    static function arrayFromSQLResult($sqlResult, $attrsToDisplay){
        // we have products and their attributes in $sqlResult, which makes
        // it have itemsOnPage * (number of attributes) lines in total.
        // let's go through it in one cycle.

        // all products will be put to array of Products
        $products = array();

        // use lastLine to know when the cycle gets to a different product
        // from the one it had before
        $lastLine = array('productID' => 0);
        // productID is set to zero not to start creating a new Product at first
        // loop of the while cycle

        // attributes of the product
        $attrs = array();

        //number of read attributes
        $attrCount = 0;

        while ($line = mysql_fetch_assoc($sqlResult)){
            if ($lastLine['productID'] != $line['productID'] &&
                $lastLine['productID'] > 0){

                // add product saved in the lastLine
                $products[] = new Product($lastLine['productName'],
                    $lastLine['productPrice'],
                    $lastLine['imgPath'],
                    $attrs,
                    $lastLine['productID']);

                // clear the attributes array for a new Product
                $attrs = array();
                $attrCount = 0;
            }

            // at every loop of the while cycle, new attribute is read
            ++$attrCount;
            if ($attrCount <= $attrsToDisplay ){
                $attrs[] = new Attribute($line['attrName'],
                    $line[$line['type'].'Value'],
                    $line['formatString']);
            }

            $lastLine = $line;
        }

        // add last product
        if ($lastLine['productID'] > 0){
            $products[] = new Product($lastLine['productName'],
                $lastLine['productPrice'],
                $lastLine['imgPath'],
                $attrs,
                $lastLine['productID']);
        }

        return $products;

    }
}

/// Product class (with more details) used by item.php.
class DetailedProduct extends Product {

    public $name, $price, $VATPrice, $imagePath, $attributes, $id, $bigImagesPaths,
        $description, $globalRatings, $userRatings, $comments;

    /** Costructor. If globalRatings or userRatings objects are null (which 
     * means the product hasn't been rated yet), new ProductRatings object
     * with zero values is created.
     *
     * @param <string> $name
     * @param <float> $price
     * @param <string> $imagePath
     * @param <array(Attribute)> $attributes
     * @param <int> $id
     * @param <array(string)> $bigImagesPaths
     * @param <string> $description
     * @param <ProductRatings> $globalRatings
     * @param <ProductRatings> $userRatings
     * @param <array(Comment)> $comments
     */
    function __construct($name, $price, $imagePath, $attributes, $id,
                         $bigImagesPaths, $description, $globalRatings,
                         $userRatings, $comments){


        parent::__construct($name, $price, $imagePath, $attributes, $id);
        
        $this->bigImagesPaths = $bigImagesPaths;
        $this->description = $description;
        $this->globalRatings = $globalRatings != null ? $globalRatings : new GlobalRatings();
        $this->userRatings = $userRatings != null ? $userRatings : new UserRatings();
        $this->comments = $comments;
    }
}



?>
