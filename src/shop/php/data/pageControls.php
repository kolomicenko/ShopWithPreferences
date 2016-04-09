<?php


/**
 * Class with controls on a catalog.php page.
 */
class PageControls{

    /**
     * Array indexed by id's of orders. Values are associative arrays,
     * where the keys are: id (id of the order), value (string that user sees)
     * and sql (which is used for ORDER BY clause in sql queries).
     *
     */
    static $SortingOrders = array(
        1 => array( "id" => 1, "value" => "Popularity",
            "name" => "POPULARITY", "sql" => ""),
        2 => array( "id" => 2, "value" => "Name a -> z",
            "name" => "NAMEASC", "sql" => "productName asc"),
        3 => array( "id" => 3, "value" => "Name z -> a",
            "name" => "NAMEDESC", "sql" => "productName desc"),
        4 => array( "id" => 4, "value" => "Price lowest -> highest",
            "name" => "PRICEASC", "sql" => "productPrice asc"),
        5 => array( "id" => 5, "value" => "Price highest -> lowest",
            "name" => "PRICEDESC", "sql" => "productPrice desc")
    );

    /// Array with numbers of items that can be displayed on a page at a time.
    /// User allways chooses among these.
    static $ItemsOnPageNumbers = array(1 => 10, 20, 50, 100, "all");
    
}


?>
