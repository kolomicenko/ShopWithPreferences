<?php

require_once currentPath('shop/php/data/pageControls.php');
require_once currentPath('shop/php/data/productRatings.php');
require_once currentPath('shop/php/categories.php');

/**
 * Base class for all preferences. Preferences have to inherit from this class
 * and implement setPreference() and getPreference(). BlackBox class allways
 * handles preferences and calls set and get functions. These functions'
 * parameters are arrays, because every preference accepts different number
 * of arguments. Constructors of preference classes can take just one parameter,
 * which is userID of current user.
 */
abstract class Preference {
///ID of current user
    public $userID = 0;

    /**
     * Constructor.
     * @param <int> $userID
     */
    function __construct($userID) {
        $this->userID = $userID;
    }

    /** Returns chosen object of current Preference.
     * @param <array(arguments)> $args
     * @return <Object> Chosen object of current Preference.
     */
    abstract function getPreference($args);

    /** Sets current Preference according to the $args.
     * @param <array(arguments)> $args
     */
    abstract function setPreference($args);
}

/// Class for handling preferences on checkOut page (payment and shipping types).
abstract class CheckOutPreference extends Preference{
    
    function __construct($userID) {
        parent::__construct($userID);
    }

    /**Returns id of most preferred check-out object. First looks into session,
     * then for user's preference in database, then for most popular object
     * among all users. If nothing can be found, virtual function getDefaultValue
     * is called to get the default. It also sets the session not to have to look into
     * database next time.
     *
     * @param <array> $args empty array, no args needed
     * @return <int> id of preferred object
     */
    function getPreference($args) {
        $prefName = $this->getPrefName();

        if (!isset($_SESSION['preferences'][$prefName])){
            // if user's preference exists, it is used, otherwise global one is used
            $res = mysql_fetch_assoc(
                query("
                (SELECT 1 as sortCol, prefValue, userID, prefID
                FROM prefvalues NATURAL JOIN preferences
                WHERE prefName = '$prefName' AND userID = $this->userID)

                UNION -- preferences of logged user UNION others
                (SELECT 2, prefValue, 0 AS userID, prefID AS p
                FROM prefvalues NATURAL JOIN preferences
                WHERE prefName = '$prefName' GROUP BY prefValue
                HAVING COUNT(*) >= ALL(  -- get most common value
                    SELECT COUNT(*) FROM prefvalues NATURAL JOIN preferences
                    WHERE prefID = p GROUP BY prefValue
                ))
                ORDER BY sortCol LIMIT 0,1"));

            $_SESSION['preferences'][$prefName] = $res
                ? $res['prefValue']
                : $this->getDefaultValue();
        }

        return $_SESSION['preferences'][$prefName];


    }

    /**Sets user's check-out preference to specified object. Session and
     * database are both changed.
     *
     * @param <type> $args array, first value is the ID of preferred object
     */
    function setPreference($args) {
        $type = $args[0];
        $prefName = $this->getPrefName();

        $_SESSION['preferences'][$prefName] = $type;

        if ($this->userID != 0){
            query("INSERT INTO prefvalues (prefID, userID, prefValue) VALUES
                ((SELECT prefID FROM preferences WHERE prefName = '$prefName'),
                 $this->userID, %d)
                 ON DUPLICATE KEY UPDATE prefValue = %d", $type, $type);
        }
    }

    /**
     * Returns name of current preference handled by a child class.
     */
    abstract function getPrefName();

    /**
     * Returns default value of current preference handled by a child class.
     */
    abstract function getDefaultValue();
    
}


/// User's preference of shipping types
class TopShippingType extends CheckOutPreference {

    function __construct($userID) {
        parent::__construct($userID);
    }

    function getPrefName(){
        return 'topShippingType';
    }

    function getDefaultValue(){
        return 1;
    }
    
}

/// User's preference of payment options
class TopPaymentType extends CheckOutPreference {

    function __construct($userID) {
        parent::__construct($userID);
    }

    function getPrefName(){
        return 'topPaymentType';
    }

    function getDefaultValue(){
        return 1;
    }
}

/**Class for handling preferences on catalog.php page (sorting, items on page).
 * Every user can have different preferences for every category. When preference
 * for current category is unknown, it is counted from other categories or other
 * users' preferences.
 */
abstract class PageControlPreference extends Preference{

    function __construct($userID) {
        parent::__construct($userID);
    }

    /**Returns id of most preferred page controls object. First looks into session,
     * then for user's preference in database, then for most popular object
     * among other categories, then for popular object among all users but within
     * the same category and at last for most popular object globally. If user
     * is not logged in, database is not used (because they can't have anything
     * saved there), and most frequent object ID is counted from the session var.
     * If nothing can be found, virtual function getDefaultValue is called to get
     * the default. It also sets the session not to have to look into database
     * next time.
     *
     * @param <array> $args array, first value must be category ID
     * @return <int> id of preferred object
     */
    function getPreference($args) {
        $catID = $args[0];

        $prefName = $this->getPrefName();

        if (!isset($_SESSION['preferences'][$prefName][$catID])){
            
            // for user0 prefer session because database isn't altered by them
            if ($this->userID == 0 && isset($_SESSION['preferences'][$prefName])){
                $_SESSION['preferences'][$prefName][$catID] =
                    $this->mostCommonValue($_SESSION['preferences'][$prefName]);
            }else{
                $res = mysql_fetch_assoc(
                    query("
                (SELECT 1 as sortCol, prefValue, userID, prefID
                FROM prefvalues NATURAL JOIN preferences
                WHERE prefName = '$prefName' AND userID = $this->userID AND catID = $catID)

                UNION -- other categories of the same user (most usual value)
                (SELECT 2, prefValue, userID, prefID AS p
                FROM prefvalues NATURAL JOIN preferences
                WHERE prefName = '$prefName' AND userID = $this->userID
                GROUP BY prefValue HAVING COUNT(*) >= ALL( -- most usual value
                    SELECT COUNT(*) FROM prefvalues NATURAL JOIN preferences
                    WHERE prefID = p AND userID = $this->userID GROUP BY prefValue
                ))

                UNION -- same category of other users (most usual value)
                (SELECT 3, prefValue, 0 AS userID, prefID AS p
                FROM prefvalues NATURAL JOIN preferences
                WHERE prefName = '$prefName' AND catID = $catID
                GROUP BY prefValue HAVING COUNT(*) >= ALL(
                    SELECT COUNT(*) FROM prefvalues NATURAL JOIN preferences
                    WHERE prefID = p AND catID = $catID GROUP BY prefValue
                ))

                UNION -- other categories of other users (most usual value)
                (SELECT 4, prefValue, 0 AS userID, prefID AS p
                FROM prefvalues NATURAL JOIN preferences
                WHERE prefName = '$prefName'
                GROUP BY prefValue HAVING COUNT(*) >= ALL(
                    SELECT COUNT(*) FROM prefvalues NATURAL JOIN preferences
                    WHERE prefID = p GROUP BY prefValue
                ))

                ORDER BY sortCol LIMIT 0,1"));

                $_SESSION['preferences'][$prefName][$catID] = $res
                    ? $res['prefValue']
                    : $this->getDefaultValue();
            }
        }

        return $_SESSION['preferences'][$prefName][$catID];


    }

    /**Sets user's page controls preference to specified object. Session and
     * database are both changed. This is done only for users that are logged in.
     *
     * @param <array> $args array, first value category ID, second one is ID of
     *                  preferred object
     */
    function setPreference($args) {
        $catID = $args[0];
        $value = $args[1];
        $prefName = $this->getPrefName();

        $_SESSION['preferences'][$prefName][$catID] = $value;

        if ($this->userID != 0){
            query("INSERT INTO prefvalues (prefID, userID, catID, prefValue) VALUES
                ((SELECT prefID FROM preferences WHERE prefName = '$prefName'),
                 $this->userID, $catID, %d)
                 ON DUPLICATE KEY UPDATE prefValue = %d", $value, $value);
        }
    }

    /**
     * Returns name of current preference handled by a child class.
     */
    abstract function getPrefName();

     /**
     * Returns default value of current preference handled by a child class.
     */
    abstract function getDefaultValue();


    /**Returns most common value in an array. This is used to find most
     * popular object in the session variable.
     *
     * @param <type> $ar array
     * @return <type>
     */
    private function mostCommonValue($ar){
        $values = array_count_values($ar);
        arsort($values);
        return key($values);
    }

}

/// User's preference of catolog ordering
class TopSortingOrder extends PageControlPreference {
    
    function __construct($userID) {
        parent::__construct($userID);
    }

    function getPrefName(){
        return 'topSortingOrder';
    }

    function getDefaultValue(){
        return 1;
    }

    
}

/// User's preference of how many items to display on a catalog page
class TopItemsOnPageNo extends PageControlPreference{

    function __construct($userID) {
        parent::__construct($userID);
    }

    function getPrefName(){
        return 'topItemsOnPageNo';
    }

    function getDefaultValue(){
        return 1;
    }


}

/**
 * Deals with sorting of categories in the left menu. Categories are sorted
 * by popularity which is counted from popularity of its products. Because
 * products are only in leaf categories, popularity of their parents must
 * be counted after a complete tree is a built up.
 * 
 */
class SortedCategories extends Preference {

    /**
     * Recounts category ratings from ratings of their products. Popularity
     * of leaf categories is an arithmetic average of final rating of its products.
     * Popularity of parent categories is an avergage of popularity of their
     * children.
     */
    public function setPreference($args) {

        // count popularity of leaf categories
        query("
            INSERT INTO categoryratings(categoryID, userID, methodID, rating)
            SELECT * FROM
                (SELECT categoryID, userID, methodID, AVG(finalRating) as rating
                FROM implratings NATURAL JOIN products
                -- products needed because of their category ID
                -- get ratings for each category, user and method
                GROUP BY categoryID, userID, methodID) as sel
            ON DUPLICATE KEY UPDATE rating = sel.rating
        ");

        // all categories and some ratings
        $categories = query("SELECT *
                FROM categories NATURAL LEFT JOIN categoryratings
                ORDER BY superCategory, categoryID, methodID");

        // build up a tree and count ratings of all categories
        $catTree = new RatingCategories($categories);

        // get ratings of non-leaf categories
        $queryValues = $catTree->asString();

        // insert them into database
        query("REPLACE INTO categoryratings (categoryID, userID, methodID, rating)
                VALUES $queryValues");

        //echo mysql_error();
    }

    /**
     * Gets all categories from database, sorted by popularity and then by name.
     * First Ordering by superCategory must allways be used, Categories class needs it.
     *
     * @return <mysql result> sorted categories
     */
    public function getPreference($args) {
                
        if ($this->userID != 0){
            return query("
            SELECT *
            FROM categories

                NATURAL LEFT JOIN -- logged user's ratings
                (SELECT categoryID, rating userRating 
                FROM categoryratings NATURAL JOIN methods
                WHERE userID = $this->userID AND method = '".ProductRatings::$PREFERRED_CAT_METHOD."') AS r

                NATURAL LEFT JOIN -- global ratings
                (SELECT categoryID, rating globalRating 
                FROM categoryratings NATURAL JOIN methods
                WHERE userID = 0 AND method = '".ProductRatings::$PREFERRED_CAT_METHOD."') AS rr

            ORDER BY superCategory, userRating DESC, globalRating DESC, categoryName
            ");
        }else{
        return query("
            SELECT *
            FROM categories

                NATURAL LEFT JOIN -- global ratings only
                (SELECT categoryID, rating globalRating 
                FROM categoryratings NATURAL JOIN methods
                WHERE userID = 0 AND method = '".ProductRatings::$PREFERRED_CAT_METHOD."') AS rr

            ORDER BY superCategory, globalRating DESC, categoryName
            ");
        }
    }
}

/**
 * Various types of user's product preferences.
 */
abstract class BestItems extends Preference {

///Just calls incrementBehavior with the same parameters.
    public function setPreference($args) {
        $productID = $args[0];
        $scrollCount = $args[1];
        $clickCount = $args[2];
        $displayTime = $args[3];
        $displayCount = $args[4];
        $this->incrementBehavior($productID, $scrollCount, $clickCount,
            $displayTime, $displayCount);
    }

    /**
     * Saves another bit of behavior that has been received from the user.
     * This function should call recountRatings() or something similar before
     * it ends.
     *
     * @param <int> $productID of the product with which user interacted
     * @param <int> $scrollCount amount of scrolling in pixels
     * @param <int> $clickCount number of mouse clicks
     * @param <int> $displayTime amount of time the product was displayed in ms
     * @param <0 or 1> $displayCount whether to add 1 to displayCount or not
     */
    private function incrementBehavior($productID, $scrollCount, $clickCount,
        $displayTime, $displayCount) {
    
        query("INSERT INTO behavior(productID, userID, displayCount,
                    scrollCount, clickCount, displayTime)
               VALUES(%d,%d,1,%d,%d,%d) ON DUPLICATE KEY UPDATE
                displayCount = displayCount + %d,
                scrollCount = scrollCount + %d,
                clickCount = clickCount + %d,
                displayTime = displayTime + %d",
            $productID, $this->userID,
            $scrollCount, $clickCount, $displayTime, $displayCount,
            $scrollCount, $clickCount, $displayTime);

        $this->recountRatings();
    }

    /**
     * Reads behavior and transfers it to relative ratings. Each rating's value
     * ranges between 0 and 100, rating 100 has the biggest behavior value, other
     * values are lineary smaller. This is done for every behavior kind and every
     * user. Global ratings, which is a rating average among all users, are also
     * counted. Final ratings are counted at last, applying weights for each
     * kind of rating, including explicit rating. Database LOCK is used to make
     * sure this function doen't run twice or more often at a time.
     *
     */
    private function recountRatings() {

        $checkRecountLength = false; // LOCK is used instead

        // check if this is already running
        if (mysql_result(query("SELECT GET_LOCK('shop.recountLock', 0)"), 0) == 0){
            return;
        }

        if ($checkRecountLength == true){
            // check when and how long it ran last time
            $recountRatingsInfo = mysql_fetch_assoc(query(
                    "SELECT * FROM recountratingsinfo NATURAL JOIN preferences
                     WHERE prefName = 'bestProducts'"));
            $lastTime = $recountRatingsInfo['lastRecountTime'];
            $lastLength = $recountRatingsInfo['lastRecountLength'];

            $time = microtime(true);

            // give currently running recount twice as much time to finish
            if ($time < $lastTime + 2 * $lastLength) {
                return;
            }
        }


        // count total and mean behavior ratings
        $this->countTotalBehaviorRatings();
        $this->countMeanBehaviorRatings();

        // count final ratings
        $this->updateFinalRatings();

        // update categories ratings
        $bb = new BlackBox($this->userID);
        $bb->setPreference("SortedCategories");
        
        
        if ($checkRecountLength == true){
            // count execution time and update recountRatingsInfo table
            query("UPDATE recountratingsinfo NATURAL JOIN preferences
                    SET lastRecountLength = %f, lastRecountTime = $time
                    WHERE prefName = 'bestProducts'",
                microtime(true) - $time);
        }
    }

    /**
     * Counts total ratings of separate behavior values. Maximum value is found
     * for each user and product. Ratings are counted as current value divided
     * by maximum value. For global ratings, average values of all users are
     * divided by maximum values of all users.
     * This is saved as a method called 'total'.
     */
    private function countTotalBehaviorRatings() {
        query("
            INSERT INTO implratings(userID, productID, methodID, displayCountRating,
                    displayTimeRating, clickRating, scrollRating, 
                    finalRating)
            SELECT * FROM (
                SELECT b.userID, productID, 
                    (SELECT methodID from methods WHERE method = 'total') as methodID,
                    coalesce(displayCount / dc * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayCountRating,
                    coalesce(displayTime / dt * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayTimeRating,
                    -- no mind if there is division by zero here ---> null -> 0
                    coalesce(clickCount / c * ".ProductRatings::$MAXIMUM_RATING.", 0) as clickRating,
                    coalesce(scrollCount / s * ".ProductRatings::$MAXIMUM_RATING.", 0) as scrollRating,                    
                    0 as finalRating
                FROM behavior b, (  -- top values for each user
                    SELECT max( scrollCount ) s, max( clickCount ) c,
                           max( displayTime ) dt, max( displayCount ) dc, userID
                    FROM behavior
                    WHERE userID > 0
                    GROUP BY userID
                ) AS maxs
                WHERE b.userID = maxs.userID -- this produces only one line for one product (and one user)

                UNION  -- registered users separately UNION all users together (incl. userID=0)

                SELECT 0, productID,
                    (SELECT methodID from methods WHERE method = 'total') as methodID,
                    coalesce(avg(displayCount) / dc * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayCountRating,
                    coalesce(avg(displayTime) / dt * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayTimeRating,
                    coalesce(avg(clickCount) / c * ".ProductRatings::$MAXIMUM_RATING.", 0) as clickRating,
                    coalesce(avg(scrollCount) / s * ".ProductRatings::$MAXIMUM_RATING.", 0) as scrollRating,                    
                    0 as finalRating
                FROM behavior b, ( -- top values for all users
                    SELECT max( scrollCount ) s, max( clickCount ) c,
                           max( displayTime ) dt, max( displayCount ) dc
                    FROM behavior
                    -- WHERE userID = 0 -- can't be here, user0 ratings could be >100 then
                ) AS maxs
                GROUP BY productID -- count average values for each particular product id
            ) as sel
            ON DUPLICATE KEY UPDATE
                scrollRating = sel.scrollRating,
                clickRating = sel.clickRating,
                displayTimeRating = sel.displayTimeRating,
                displayCountRating = sel.displayCountRating
        ");

    //echo mysql_error();
    }

    /**
     * Counts mean ratings of separate behavior values. All average ratings are
     * counted over one display of the product (so divided by displayCount).
     * Maximum fraction of value and displayCount value is found for each user
     * and product. Ratings are counted as current fractions of the same behavior
     * value and displayCount, divided by the maximum value. For global ratings,
     * average values of all users are divided by maximum values of all users.
     * This is saved as a method called 'mean'.
     */
    private function countMeanBehaviorRatings() {
        query("
            INSERT INTO implratings(userID, productID, methodID, displayCountRating,
                    displayTimeRating,
                    clickRating, scrollRating, finalRating)
            SELECT * FROM (
                SELECT b.userID, productID,
                    (SELECT methodID from methods WHERE method = 'mean') as methodID,
                    coalesce(displayCount / dc * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayCountRating,
                    coalesce(displayTime / displayCount / dt * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayTimeRating,
                    -- no mind if there is division by zero here ---> null -> 0
                    coalesce(clickCount / displayCount / c * ".ProductRatings::$MAXIMUM_RATING.", 0) as clickRating,
                    coalesce(scrollCount / displayCount / s * ".ProductRatings::$MAXIMUM_RATING.", 0) as scrollRating,                    
                    0 as finalRating
                FROM behavior b, (  -- top values for each user
                    SELECT max( scrollCount/displayCount ) s, max( clickCount/displayCount ) c,
                           max( displayTime/displayCount ) dt, max( displayCount ) dc, userID
                    FROM behavior
                    WHERE userID > 0
                    GROUP BY userID
                ) AS maxs
                WHERE b.userID = maxs.userID -- this produces only one line for one product (and one user)

                UNION  -- registered users separately UNION all users together (incl. userID=0)

                SELECT 0, productID,
                    (SELECT methodID from methods WHERE method = 'mean') as methodID,
                    coalesce(avg(displayCount) / dc * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayCountRating,
                    coalesce(avg(displayTime) / avg(displayCount) / dt * ".ProductRatings::$MAXIMUM_RATING.", 0) as displayTimeRating,
                    coalesce(avg(clickCount) / avg(displayCount) / c * ".ProductRatings::$MAXIMUM_RATING.", 0) as clickRating,
                    coalesce(avg(scrollCount) / avg(displayCount) / s * ".ProductRatings::$MAXIMUM_RATING.", 0) as scrollRating,
                    0 as finalRating
                FROM behavior b, ( -- top values for all users
                    SELECT max( scrollCount/displayCount ) s, max( clickCount/displayCount ) c,
                           max( displayTime/displayCount ) dt, max( displayCount ) dc
                    FROM behavior
                    -- WHERE userID = 0 -- can't be here, user0 ratings could be >100 then
                ) AS maxs
                GROUP BY productID -- count average values for each particular product id
            ) as sel
            ON DUPLICATE KEY UPDATE
                scrollRating = sel.scrollRating,
                clickRating = sel.clickRating,
                displayTimeRating = sel.displayTimeRating,
                displayCountRating = sel.displayCountRating
        ");

    //echo mysql_error();
    }

    /**
     * Counts final ratings for each product, user and method. For global ratings
     * average explicit rating is applied. If it is undefined anyway, medium
     * (like 3 stars) explicit rating is used. Every rating's weight is counted
     * in.
     */
    protected function updateFinalRatings(){        
        query("UPDATE implratings r NATURAL LEFT JOIN explratings er               
               SET
                finalRating = (
                orderRating * ".ProductRatings::$ORDER_RATING_WEIGHT."
                +
                IFNULL( -- if explRating not known, select mid-value
                  IF(r.userID = 0,  -- get average for unlogged users
                      (SELECT AVG(explRating) FROM explratings
                       WHERE productID = r.productID
                      ),
                     explRating  -- for logged users
                  ) - 1,
                  " . ((ProductRatings::$STARS_COUNT - 1) / 2) . "
                ) * ".
            (ProductRatings::$EXPL_RATING_WEIGHT *
            ProductRatings::$MAXIMUM_RATING / (ProductRatings::$STARS_COUNT - 1)) ." +
                               scrollRating * ".ProductRatings::$SCROLL_COUNT_WEIGHT." +
                               clickRating * ".ProductRatings::$CLICK_COUNT_WEIGHT." +
                               displayTimeRating * ".ProductRatings::$DISPLAY_TIME_WEIGHT." +
                               displayCountRating * ".ProductRatings::$DISPLAY_COUNT_WEIGHT.") / " .
            (ProductRatings::$EXPL_RATING_WEIGHT + ProductRatings::$SCROLL_COUNT_WEIGHT
            + ProductRatings::$CLICK_COUNT_WEIGHT + ProductRatings::$DISPLAY_TIME_WEIGHT
            + ProductRatings::$DISPLAY_TIME_WEIGHT + ProductRatings::$ORDER_RATING_WEIGHT));

        //echo mysql_error();
    }

}

/**
 * Top products from specified categories.
 */
class TopItems extends BestItems {

/**
 * Gets top items from the database. Top items are shown in right menu
 * on catalog.php and also directly in catalog when sorted by popularity.
 *
 * @param <string> $sqlLimit limit to be put into sql query
 * @param <string> $categories comma separated string of categories
 * @return <sql result> of the top products and their attributes
 */
    function getPreference($args) {
        $sqlLimit = $args[0];
        $categories = $args[1];

        // in both queries, first ordering is because of the sqlLimit
        // (because the limit must be applied before joining attributes),
        // second ordering because of attribute sorting (because they can't be
        // sorted before joining everything together)

        if ($this->userID > 0) { // user is logged in
        // first ordering by user ratings, then by global ratings
        // that's why the table ratings is joined twice
            $products = query("SELECT * FROM
                (SELECT p.productID, productName, productPrice, categoryID,
                    imgPath, r.finalRating rfR, rr.finalRating rrfR
                    FROM (products p NATURAL JOIN imagepaths)

                    LEFT JOIN -- user ratings
                    (SELECT finalRating, productID 
                    FROM implratings NATURAL JOIN methods
                    WHERE userID = $this->userID AND method = '".ProductRatings::$PREFERRED_METHOD."') AS r
                    ON p.productID = r.productID

                    LEFT JOIN -- global ratings
                    (SELECT finalRating, productID 
                    FROM implratings NATURAL JOIN methods
                    WHERE userID = 0 AND method = '".ProductRatings::$PREFERRED_METHOD."') AS rr
                    ON p.productID = rr.productID

                    WHERE categoryID IN ($categories) AND imgSize = 'small'
                    ORDER BY rfR DESC, rrfR DESC $sqlLimit ) AS s
                NATURAL JOIN attrvalues NATURAL JOIN attributes
                ORDER BY s.rfR DESC, s.rrfR DESC, productID, sorting");
        }else {
        // user is logged off, so only global rating is needed
            $products = query("SELECT * FROM
                (SELECT p.productID, productName,
                    productPrice, categoryID, imgPath, r.finalRating
                    FROM (products p NATURAL JOIN imagepaths)

                    LEFT JOIN -- global ratings
                    (SELECT finalRating, productID 
                    FROM implratings NATURAL JOIN methods
                    WHERE userID = 0 AND method = '".ProductRatings::$PREFERRED_METHOD."') AS r
                    ON p.productID = r.productID

                    WHERE categoryID IN ($categories) AND imgSize = 'small'
                    ORDER BY r.finalRating DESC $sqlLimit ) AS s
                NATURAL JOIN attrvalues NATURAL JOIN attributes
                ORDER BY s.finalRating DESC, productID, sorting");

        }// final sorting must be by productID, because previous sortings
        // could be ambiguous and 'sorting' would mix up the products and
        // by their attributes


        return $products;
    }
}

/**
 * Products similar to specified product. Similar means that there exist orders
 * where the product was bought with the same item(s) as its similar products
 * in different orders. (i.e. A and B were bought in one order and A and C were
 * bought in another order. If this happens often, it is very likely that B and C
 * are similar (alternative) to each other).
 *
 *
 */
class SimilarItems extends BestItems {

/**
 * Gets similar items to specified item from the database. These items are
 * shown in right menu on item.php. Similar items are items that each were in
 * different orders with same other items (which are their linked items).
 * If two items are in one separate order it means that they probably are not
 * alternative to each other.
 *
 * @param <int> $itemsCount number of items
 * @param <int> $itemID ID of the item to which to find similar items 
 * @return <sql result> of the products
 */
    function getPreference($args) {
        $itemsCount = $args[0];
        $itemID = $args[1];

        // get categoryID of the item (similar item must be from the same category)
        $categoryID = mysql_result(query(
                "SELECT categoryID FROM products WHERE productID = $itemID"), 0);
        
        /**
         * Number of items linked to $itemID to find. Too small number may cause
         * incomplete results, number too high may cause performance loss.
         */
        $LINKED_ITEMS_COUNT = 100;


        $bb = new BlackBox($this->userID);

        $linkedItemsIDs = "$itemID"; // add this item so that it's not in the final result

        // get linked items to the item
        $linkedItems = $bb->getPreference("LinkedItems", $LINKED_ITEMS_COUNT, $itemID);
        // make list of the linked items
        while ($item = mysql_fetch_assoc($linkedItems)){
            $linkedItemsIDs .= "," . $item['productID'];
        }

        // return linked items to the linked items found one step before
        // restrict the result to items in specified category
        return $bb->getPreference("LinkedItems", $itemsCount, $linkedItemsIDs, $categoryID);
    }
}

/**
 * Products that have been ordered together with one or more specified products.
 */
class LinkedItems extends BestItems {

/**
 * Gets linked (relative) items to specified item(s) from the database.
 * Sorts linked items by the frequency they are in an order and by the final rating.
 * Current user's orders are not excluded from considered orders as they may want
 * to buy the products again. Quantity of items is not taken care of (because it
 * could be very ambiguous when buying e.g. 5 metres of a cable).
 * These items are shown in right menu on cart.php or itemAdded.php.
 *
 * @param <int> $itemsCount number of items
 * @param <string> $itemIDs ID(s) of the item(s) to which to find similar items
 * @return <sql result> of the products
 */
    function getPreference($args) {
        $itemsCount = $args[0];
        $itemIDs = $args[1];

        if ($itemIDs == ""){ // empty shopping cart, return 0 rows
            return emptyResult();
        }

        /// Linked items only from certain category
        $catRestriction = isset($args[2]) ? $args[2] : 0; // optional parameter
        
        if ($this->userID > 0){            
            return query("
            SELECT productID, productName, productPrice, imgPath, categoryID
            FROM orders NATURAL JOIN orderproducts NATURAL JOIN products NATURAL JOIN imagepaths

                NATURAL LEFT JOIN -- user ratings
                (SELECT finalRating userFinal, productID 
                FROM implratings NATURAL JOIN methods
                WHERE userID = $this->userID AND method = '".ProductRatings::$PREFERRED_METHOD."') AS r

                NATURAL LEFT JOIN -- global ratings
                (SELECT finalRating globalFinal, productID 
                FROM implratings NATURAL JOIN methods
                WHERE userID = 0 AND method = '".ProductRatings::$PREFERRED_METHOD."') AS rr

            WHERE
                -- orderID must have been ordered together with current product(s)
                orderID IN (SELECT orderID FROM orders NATURAL JOIN orderproducts WHERE productID IN (%s))
                -- but orderID must not be one of current product(s)
                AND productID NOT IN (%s) AND imgSize = 'small' " .
                ($catRestriction == 0 ? '' : " AND categoryID = $catRestriction ") .
                "
            GROUP BY productID -- products may be on many lines, order them by the count
            ORDER BY count(productID) DESC, userFinal DESC, globalFinal DESC
            LIMIT 0, %d
            ", $itemIDs, $itemIDs, $itemsCount );             
        }else{
            return query("
            SELECT productID, productName, productPrice, imgPath, categoryID
            FROM orders NATURAL JOIN orderproducts NATURAL JOIN products NATURAL JOIN imagepaths

                NATURAL LEFT JOIN -- global ratings
                (SELECT finalRating globalFinal, productID 
                FROM implratings NATURAL JOIN methods
                WHERE userID = 0 AND method = '".ProductRatings::$PREFERRED_METHOD."') AS rr

            WHERE
                -- orderID must have been ordered together with current product(s)
                orderID IN (SELECT orderID FROM orders NATURAL JOIN orderproducts WHERE productID IN (%s))
                -- but orderID must not be one of current product(s)
                AND productID NOT IN (%s) AND imgSize = 'small' " .
                ($catRestriction == 0 ? '' : " AND categoryID = $catRestriction ") .
                "
            GROUP BY productID -- products may be on many lines, order them by the count
            ORDER BY count(productID) DESC, globalFinal DESC
            LIMIT 0, %d
            ", $itemIDs, $itemIDs, $itemsCount );
        }
    }

    /**
     * Recounts order ratings and updates final ratings.
     *
     * @param <array> $args first value is shopping cart that was purchased
     */
    function setPreference($args){
        $cart = $args[0];

        // When this becomes too slow, put it to BestItems::setPreference

        // recount order ratings
        $this->countOrderRatings();

        // recount final ratings
        $this->updateFinalRatings();
        
    }

    /**
     * Counts products' order ratings. Counts products' order ratings from total
     * times the products were ordered. User ratings are made from orders for
     * every user, global ratings from all user orders together. Unlike other
     * implicit ratings, order ratings are counted just by one method. That's
     * why the result is saved to database once for every method (this is not
     * wasting of space because final ratings differ for each method and
     * will be saved to the same table anyway). 
     */
    function countOrderRatings(){
        query("
INSERT INTO implratings(userID, productID, orderRating, methodID)
SELECT * FROM 
    (SELECT counts.userID, productID, pCount /  maxCount *
        ".ProductRatings::$MAXIMUM_RATING." AS orderRating
    FROM
        -- gets count of every product (for one user)
        (SELECT count( * ) AS pCount, userID, productID
        FROM orders NATURAL JOIN orderproducts
        -- every user has their own counts
        GROUP BY productID, userID) AS counts
        ,
        -- get maximum count of one product (for the user)
        (SELECT max( pCount ) maxCount, userID
        FROM
            -- helpCounts are counts of all products (for each user)
            (SELECT count( * ) AS pCount, userID, productID
            FROM orders NATURAL JOIN orderproducts
            GROUP BY productID, userID) as helpCounts
        WHERE userID > 0
        GROUP BY userID) AS maxs -- gets maximum helpCount (for each user)
    WHERE counts.userID = maxs.userID

    UNION -- user ratings union global ratings

    SELECT 0 as userID, productID, pCount /  maxCount *
        ".ProductRatings::$MAXIMUM_RATING." AS orderRating
    FROM
        -- gets count of every product (all users together)
        (SELECT count( * ) AS pCount, productID
        FROM orders NATURAL JOIN orderproducts
        GROUP BY productID) AS counts
        ,
        -- gets maximum count of a product
        (SELECT max( pCount ) maxCount
        FROM
            (SELECT count( * ) AS pCount, productID
            FROM orders NATURAL JOIN orderproducts
            GROUP BY productID
            ) AS helpCounts
        ) AS maxs
    )AS sel
    ,
    -- do this for all methods
    (SELECT methodID from methods) as methodssel

ON DUPLICATE KEY UPDATE
    orderRating = sel.orderRating
        ");
        
        //echo mysql_error();
    }

}

?>
