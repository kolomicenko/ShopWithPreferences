<?php

require_once 'php/managers/categoriesManager.php';
require_once 'php/data/pageControls.php';
require_once 'php/data/product.php';
require_once 'php/data/attribute.php';

/**
 * A manager for catalog.php. It prepares array of Product to be displayed and
 * all page controls. As said in CategoryManeger, not only products from selected
 * category are shown, but also products from all subcategories.
 * Expects category ID and pageNo in GET, otherwise default values are applied.
 * In the right menu there are top products of the listed category.
 */
class CatalogManager extends CategoriesManager {

    public $currentSortingOrder, ///< order the user wants
        $sortingOrders,      ///< all available orders
        $itemsOnPageNumbers, ///< all available items on page numbers
        $currentItemsOnPageNo,///< the one user wants
        $currentPageNo,       ///< number of current page
        $pagesCount;         ///< total count of pages

    public $products; ///< array of Product objects to be displayed in catalog
    public $productsInRow = 2; ///< number of products that are displayed in a row in catalog

    /*
    /// Whether catalog has already been viewed, for startGuide's sake.
    ///  Cookie is used to carry this between pages.

    public $firstAccess;
    public $firstAccessCookieLifeTime = 2592000;
    ///< Lifetime of the cookie called 'noUserGuide' (30 days).
     */


    
    /**
     * Constructor calls two functions that do all the job, preparePageControls()
     * and prepareProducts().
     */
    function __construct() {

        parent::__construct();
        
        /*
        // to show the startGuide just once - not used for now
        if (!isset ($_COOKIE['noUserGuide'])){
            $this->firstAccess = true;
            setcookie('noUserGuide', '1', time() + $this->firstAccessCookieLifeTime);
        }else{
            $this->firstAccess = false;
        }*/

        // cat=0 means we are at home page
        if ($this->currentCategory == 0)
            return;

        $sqlLimit = $this->preparePageControls();

        $this->prepareProducts($sqlLimit);
    }

    /**
     * Handles 'sorting orders', 'items on page' numbers and 'page numbers'.
     * First, it checks whether user wants to change any of page controls. If not,
     * gets them from BlackBox and prepares paging (pages count, current page
     * number) and sqlLimit, which will be used in the final sql query to get
     * products on the specified page.
     *
     * @return <string> $sqlLimit which is the final text of the sql LIMIT clause
     * to select requested range of products.
     */
    function preparePageControls(){
        // user changing page controls
        if (isset($_POST['sortBy'])){
            $this->bb->setPreference("TopSortingOrder", $this->currentCategory, $_POST['sortBy']);
            header("location: $_SERVER[REQUEST_URI]");
            exit;
        }
        if (isset($_POST['itemsOnPage'])){            
            $this->bb->setPreference("TopItemsOnPageNo", $this->currentCategory, $_POST['itemsOnPage']);
            header("location: $_SERVER[REQUEST_URI]");
            exit;
        }

        // get currents from black box
        $this->currentSortingOrder =
            PageControls::$SortingOrders[
            $this->bb->getPreference("TopSortingOrder", $this->currentCategory)];
        $this->currentItemsOnPageNo =
            PageControls::$ItemsOnPageNumbers[
            $this->bb->getPreference("TopItemsOnPageNo", $this->currentCategory)];

        $this->sortingOrders = PageControls::$SortingOrders;
        $this->itemsOnPageNumbers = PageControls::$ItemsOnPageNumbers;        


        //prepare pages
        if ($this->currentItemsOnPageNo == "all") {
            $sqlLimit = "";
            $this->pagesCount = 1;
            $this->currentPageNo = 1;
        }else {
            $this->currentPageNo = isset($_GET['pageNo']) ? $_GET['pageNo'] : 1;
            $itemsCount = mysql_result(query("SELECT COUNT(*) FROM products
                    WHERE categoryID IN ($this->descendants)"), 0);
            $this->pagesCount = ceil($itemsCount / $this->currentItemsOnPageNo);

            $startingItem = ($this->currentPageNo - 1) * $this->currentItemsOnPageNo;
            $sqlLimit = "LIMIT $startingItem, $this->currentItemsOnPageNo";
        }       

        return $sqlLimit;
    }


    /**
     * Selects products from specified page, their attributes, rating, images
     * and orders them by specified sorting order. Everything is done in one
     * a bit complicated sql query rather then selecting products only and then
     * getting their attributes in in O(n) other queries, which would be very
     * slow. Then uses Product class' static function to parse the sql result.
     *
     * @param <string> $sqlLimit which is the final text of the sql LIMIT clause
     * to select requested range of products.
     */
    function prepareProducts($sqlLimit) {

        // first ordering is because of the sqlLimit
        // (because the limit must be applied before joining attributes),
        // second ordering because of attribute sorting (because they can't be
        // sorted before joining everything together)

        if ($this->currentSortingOrder["name"] != "POPULARITY"){
            $sqlSortingOrder = $this->currentSortingOrder["sql"];
            $products = query("SELECT * FROM
                (SELECT * FROM products WHERE categoryID IN ($this->descendants)
                ORDER BY $sqlSortingOrder $sqlLimit) AS p
                NATURAL JOIN imagepaths NATURAL JOIN attrvalues NATURAL JOIN attributes
                WHERE imgSize = 'small' ORDER BY $sqlSortingOrder, productID, sorting");

        // must order by productID either - to have one product on consecutive lines
        // when first ordering could be ambiguous (etc. price)

        }else{ // ordering by popularity (use blackBox to get the products)
            
            $products = $this->bb->getPreference('TopItems', $sqlLimit, $this->descendants);
        }        

        // number of attributes to show for each product
        // it can't be limited in the sql query
        $attrsToDisplay = $this->categories->currentNode->attrsToDisplay;

        $this->products = Product::arrayFromSQLResult($products, $attrsToDisplay);
        
    }   

    /**
     * Prints controlsForm at a specified position. Controls form consists of
     * 'page numbers' and their scroll buttons and selects of 'sorting orders'
     * and 'items on page' numbers.
     * <p>
     * If user click a page number, the request for another page is sent by GET.
     * But if they select either 'sorting order' or 'items on page' number, it
     * is sent by POST because it means a preference change and the behavior
     * must be saved. This is achieved with form having method=post, but the names
     * of the selects are empty. Once user selects an option from a select,
     * before submitting the form, the selects name is changed, so that its
     * content is sent, but the other select's content not. After changing
     * a value in one of those select, page number is reset to one.
     *
     * @param <string> $formPosition choose between 'Top' and 'Bottom', the id
     * of the form consist of this string and is important for javascripts.
     */
    function printControlsForm($formPosition){

    ?>

    <form id="controlsForm<?php echo $formPosition?>" class="controls" method="post"
          action="<?php echo $_SERVER["PHP_SELF"]."?cat=".$this->currentCategory; ?>" >
        <div class="leftControls">
            <a class="toBeginning">
                <img src="img/arrBegin.png" alt="beginning" width="7" height="10">
            </a>
            <a class="toLeft">
                <img src="img/arrPrev.png" alt="prev" width="5" height="10">
            </a>
            <span class="pageNumbers">
                    <?php
                    $pageNo = 0;
                    if ($this->pagesCount > 1) {
                        while (++$pageNo <= $this->pagesCount) {
                            echo '<a';
                            if ($pageNo == $this->currentPageNo)
                                echo ' class="currentPageNo"';
                            else
                                echo ' href="'.$_SERVER["PHP_SELF"]."?cat="
                                    .$this->currentCategory.'&amp;pageNo='.$pageNo.'"';
                            echo ">$pageNo</a>";
                        }
                    }
                    ?>
            </span>
            <a class="toRight">
                <img src="img/arrNext.png" alt="next" width="5" height="10">
            </a>
            <a class="toEnd">
                <img src="img/arrEnd.png" alt="end" width="7" height="10">
            </a>        
        </div>
        <div class="rightControls">Sort by:
            <select name="" onChange="this.name='sortBy';this.form.submit();">
                <?php

                foreach ($this->sortingOrders as $orderID => $order) {
                    ?>
                    <option value="<?php echo $orderID; ?>"
                    <?php if ($this->currentSortingOrder["id"] == $orderID)
                        echo " selected=\"selected\"";
                    echo ">$order[value]";
                }

                ?>

            </select>Items on page:
            <select name="" onChange="this.name='itemsOnPage';this.form.submit();">
                <?php
                foreach ($this->itemsOnPageNumbers as $numberID => $number) {
                ?>
                    <option value="<?php echo $numberID; ?>"
                        <?php if ($this->currentItemsOnPageNo == $number)
                            echo " selected=\"selected\"";
                        echo ">$number";
                }
                ?>
            </select>
        </div>
    </form>


    <?php
    }
    
    /**
     * Prints top products of the category in the right menu.
     */
    function printRightMenu(){
    ?>
    <div class="caption" style="background-image: url('img/rightMenuTI.png');"></div>

    <?php

    $result = $this->bb->getPreference("TopItems", 'LIMIT 0, 5', $this->descendants);

    // 0 because we don't want to see any attributes
    $products = Product::arrayFromSQLResult($result, 0);

    foreach ($products as $product){
      ?>
      <div class="item">
          <img width="190" height="12" alt="" src="img/top_bg_right.gif">
          <a href="item.php?id=<?php echo $product->id; ?>&amp;cat=<?php echo $this->currentCategory; ?>">
              <span class="name"><?php echo $product->name; ?></span>
              <span class="price"><?php echo $product->price; ?>,-</span>
              <br>
          <img src="<?php echo $product->imagePath; ?>" alt="">
          </a>
          <img width="190" height="12" alt="" src="img/bot_bg_right.gif">
      </div>

      <?php


    }
  }
}


?>