<?php

require_once 'php/managers/manager.php';
require_once 'php/categories.php';

/**
 * Parent of all managers that use categories. Works with UserCategories class and
 * displays them in left menu. Category tree is browsed using php, not javascript,
 * so that every click inside left menu means loading another page. This has
 * the advantage of listing products from a category and all its descendants
 * at a time, not just products from a leaf category, when user gets to it.
 * <p>
 * Constructor expects ID of the current category in GET or at least as the
 * parameter of itself. In other cases, the ID is set to zero and the home page
 * is displayed.
 *
 */
class CategoriesManager extends Manager {

    public $categories; ///< Pointer to category tree.
    public $currentCategory; ///< Current category number.
    /**
     * IDs of all descendants of the current category. Comma separated string, used
     * for mysql queries, to display products from all subcategories.
     */
    public $descendants;

    function __construct($cat = 0) {

        parent::__construct();

        $this->currentCategory = isset($_GET['cat']) ? $_GET['cat'] : $cat;

        // create tree of categories
        // use SortedCategories preference to get categories in sql result
        $this->categories = new UserCategories(
                    $this->bb->getPreference("SortedCategories"),                    
                    $this->currentCategory);
                
        // get descendants
        $this->descendants = $this->categories->getDescendantsOfCurrentCategory();

        // update last shopping page (used in ShoppingManager)
        $_SESSION['lastShoppingPage'] = $_SERVER['REQUEST_URI'];
    }

    /** Prints left menu with category tree.
     * Current category, its immediate descendants, its siblings, its ancestors
     * and their siblings are displayed, everything else is hidden.
     */
    function printLeftMenu() {
        ?>
<div class="caption" style="background-image: url('img/leftMenuC.png');"></div>

        <?php
        echo "<ul>";
        $this->categories->printMenuFromCategories(new CategoryMenuPrinter());
        echo "</ul>";

    }



}

