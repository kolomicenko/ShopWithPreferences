<?php

/**
 * Class for product ratings (either global or one user's) of one product.
 * This class is used as a holder of important constants and as a printer of ratings
 * on product detail page.
 */
abstract class ProductRatings{
    public $explRating, ///< Explicit rating of the product.

           $orderRating, ///< Orders (how often purchased) rating of the product.

           $displayCountRating, ///< Display count rating of the product.

           $scrollRatings, ///< Array of scroll ratings indexed by method names.
           $clickRatings, ///< Array of click ratings indexed by method names.
           $displayTimeRatings, ///< Array of display time ratings indexed by method names.
           $finalRatings, ///< Array of final ratings indexed by method names.

           $methods, ///< Array of methods for counting the impl ratings.

           $caption; ///< Name of the ratings ('Your' or 'Global').

    public static $methodCount = 0;

    ///Maximum number rating can have. The minimum is allways 0.
    public static $MAXIMUM_RATING = 100;
    ///Number of stars explicit rating has.
    public static $STARS_COUNT = 5;

    ///Ratings method preferred for all kinds of products sorting
    public static $PREFERRED_METHOD = 'mean';

    ///Ratings method preferred for all kinds of categories sorting
    public static $PREFERRED_CAT_METHOD = 'total';

    // weights of particular ratings
    public static $EXPL_RATING_WEIGHT = 5;
    public static $ORDER_RATING_WEIGHT = 5;
    public static $DISPLAY_COUNT_WEIGHT = 1;
    public static $SCROLL_COUNT_WEIGHT = 1;
    public static $CLICK_COUNT_WEIGHT = 1;
    public static $DISPLAY_TIME_WEIGHT = 1;


    /**
     * Simple costructor.
     * @param <int> $explRating
     * @param <int> $displayCountRating
     * @param <int> #orderRating
     */
    function __construct($explRating, $orderRating, $displayCountRating){
        $this->explRating = $explRating;
        $this->displayCountRating = $displayCountRating;
        $this->orderRating = $orderRating;

        $this->clickRatings = array();
        $this->displayTimeRatings = array();
        $this->finalRatings = array();
        $this->scrollRatings = array();
    }
    /**
     * Adds a method and all ratings counted by.
     *
     * @param <string> $method
     * @param <int> $scrollRating
     * @param <int> $clickRating
     * @param <int> $displayTimeRating
     * @param <int> $finalRating
     */
    function addRatingsMethod($method,           
           $scrollRating,
           $clickRating,
           $displayTimeRating,
           $finalRating){

        ++ProductRatings::$methodCount;

        $this->methods[] = $method;
        $this->displayTimeRatings[$method] = $displayTimeRating;
        $this->clickRatings[$method] = $clickRating;
        $this->scrollRatings[$method] = $scrollRating;
        $this->finalRatings[$method] = $finalRating;
    }

    /**
     * Prints header of ratings table. Prints nothing if no ratings exist.
     *
     */
    static function printTableHeader(){
        if (ProductRatings::$methodCount == 0){
            return;
        }
        ?>
        <thead>
            <tr><td>
                <td>Explicit
                <td>Orders
                <td>Display count
                <td>Method
                <td>Display time
                <td>Click
                <td>Scroll
                <td><b>Final</b>

        </thead>
        <tbody>
        <?php


    }

    /**
     * Outputs all ratings to a table. Displays a message if ratings are missing.
     * If the first method of $methods array is '0', it means that only explicit
     * rating is provided.
     */
    function printMe(){
        
        if (count($this->methods) == 0){
            echo '<tr>' . $this->noRatingMessage();
            return;
        }

        $explRating = ($this->explRating - 1) * ProductRatings::$MAXIMUM_RATING /
                            (ProductRatings::$STARS_COUNT - 1);
        $explRating = $explRating < 0 ? 'N/A' : $explRating . "%";
        
        foreach ($this->methods as $key => $method){        
    ?>
        <tr>
            <td><?php if ($key == 0) echo "$this->caption:"; ?>

            <td><?php if ($key == 0) echo $explRating;?>

            <?php
            if ($method == '0'){ // explicit rating only
                echo $this->noRatingMessage();
                return;
            }

            ?>

            <td><?php if ($key == 0) echo "$this->orderRating%";?>

            <td><?php if ($key == 0) echo "$this->displayCountRating%"; ?>

            <td><?php echo "<span class=\"underline\">$method:</span>"; ?>

            <td><?php echo $this->displayTimeRatings[$method];?>%

            <td><?php echo $this->clickRatings[$method];?>%

            <td><?php echo $this->scrollRatings[$method];?>%

            <td><b><?php echo $this->finalRatings[$method];?>%</b>

    <?php
        }
    }

    
    /**
     * Returns message that should be printed if no rating exists.
     */
    abstract function noRatingMessage();


}


/**
 * Class representing ratings of current user.
 */
class UserRatings extends ProductRatings{
    function __construct($explRating = 0, $orderRating = 0, $displayCountRating = 0){
        parent::__construct($explRating, $orderRating, $displayCountRating);

        $this->caption = 'Your ratings';
    }

    function noRatingMessage(){
        return '<td colspan="10" style="padding: 10px 0;">
            Your implicit ratings of this product have not been created yet.';
    }
}

/**
 * Class representing global ratings.
 */
class GlobalRatings extends ProductRatings{
    function __construct($explRating = 0, $orderRating = 0, $displayCountRating = 0){
        parent::__construct($explRating, $orderRating, $displayCountRating);

        $this->caption = 'Global ratings';
    }

    function noRatingMessage(){
        return '<td colspan="10" style="padding: 20px 0;">
                No implicit ratings of this product have been created yet.';
    }

    
}


?>
