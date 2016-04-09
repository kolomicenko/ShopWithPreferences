<?php

/// Class representing one category node.
class Category {
    public
    $id, ///< id of the category
    $sonCount, ///< number of sons the category has
    $sons, ///< array of sons
    $father; ///< parent of the category

    
    /**
     * Constructs one category node.
     *
     * @param <ing> $id
     * @param <Category> $father
     */
    function __construct($id, $father) {
        $this->id = $id;
        $this->sonCount = 0;
        $this->sons = array();
        $this->father = $father;
    }
    /**
     * Adds a subcategory to this category.
     * @param <Category> $node to be added as a subcategory
     */
    function addSon($node) {
        $this->sons[++$this->sonCount] = $node;
    }
}

/// Class representing one right menu category node.
class UserCategory extends Category{
    public $name, ///< name of the category
           $toBePrinted, ///< whether to print this category or not in a category menu
           $isCurrent = false; ///< whether this is the current category node

    /// how many attributes to display in a catalog viewing this category
    public $attrsToDisplay;

    /**
     *
     * @param <string> $name
     * @param <ing> $id
     * @param <Category> $father
     * @param <int> $attrsToDisplay 
     */
    function __construct($name, $id, $father, $attrsToDisplay) {

        parent::__construct($id, $father);

        $this->name = $name;
        $this->toBePrinted = false;
        $this->attrsToDisplay = $attrsToDisplay;
    }
    
}

/**
 * Class representing a tree of categories. Most of the functions that manipulate 
 * with the tree are recursive.
 */
class Categories{
   /**
    * Top category, the root of the tree.
    * This is not a real category and is not visible to the user.
    */
    protected $baseNode;

    /** Finds a category with specified id.
     *
     * @param <int> $id of the category that is being looked for
     * @param <Category> $node to start searching from
     * @return <Category> found category node or null
     */
    protected function findNode($id, $node) {
        if($node->id == $id) {
            return $node;
        }
        foreach($node->sons as $son) {
            if(($found = self::findNode($id, $son)) != null)
                return $found;
        }
        return null;
    }

}



/**
 * Class representing a tree of categories for the left menu. Prints menu from
 * the categories using a specified printer class.
 */

class UserCategories extends Categories{

  ///Node of the current category.
    public $currentNode;

  /** Constructs the category tree and marks the current node.
   *
   * @param <sql result> $allCategories is the sql result selecting all
   * categories, that MUST be ordered by superCategory column and than optionaly
   * by whatever the user wants.
   * @param <int> $currentCategory is the ID of current category
   */
    public function __construct($allCategories, $currentCategory) {

        $this->baseNode = new UserCategory("",0,0,0); // virtual 'Top category'
        $lastSuperCategory = 0; // used not to have to findNode every time
        $node = $this->baseNode;

        // parse the sql result
        while($line = mysql_fetch_assoc($allCategories)) {
            // super category has changed, find it
            if($line["superCategory"] != $lastSuperCategory) {
                $node = $this->findNode($line["superCategory"],$this->baseNode);
                $lastSuperCategory = $line["superCategory"];
            }
            // add the category to the tree
            $node->addSon(
                new UserCategory($line["categoryName"],$line["categoryID"],$node,$line["attrsToDisplay"])
            );
        }

        $this->currentNode = self::findNode($currentCategory,$this->baseNode);
        // if current category doesn't exist, create it
        // fake input workaround
        if ($this->currentNode == null) {
        // father is baseNode, but it is not added as baseNode's son
        // this way makePrintable and printMenu work
            $this->currentNode = new UserCategory('',$currentCategory,$this->baseNode,0);
        }
        $this->currentNode->isCurrent = true;
    }

    

  /**
   * Sets to be printed all nodes that should be set so. Which is current
   * category, its immediate descendants, its siblings, its ancestors and their
   * siblings.
   *
   * @param <Category> $node that will have its children set to be printed.
   */
    private static function makePrintable($node) {
        foreach($node->sons as $son) {
            $son->toBePrinted = true;
        }
        if($node->id != 0)
            self::makePrintable($node->father);

    }

  /**
   * Prints nodes using specified printer (class with printing function).
   *
   * @param <type> $node that will have its children printed
   * @param <type> $depth of the node from the root
   * @param <type> $categoryPrinter is a class with printing function that will
   * be used for printing
   */
    private static function printNode($node, $depth, $categoryPrinter) {
        foreach($node->sons as $son) {
            if($son->toBePrinted == true) {
                $categoryPrinter->printCategoryMenuItem($son, $depth);
                self::printNode($son, $depth + 1, $categoryPrinter);
            }
        }
    }

    /**
     * Prints whole menu using specified printer (class with printing function).
     * This is the function then is called by categoryManager
     * @param <type> $categoryPrinter
     */
    public function printMenuFromCategories($categoryPrinter) {
        self::makePrintable($this->currentNode);
        self::printNode($this->baseNode,0,$categoryPrinter);
    }

    /**
     * Returns all subcategories of node $node.
     * @param <Category> $node
     * @return <string> $node ID and all its descendants' IDs,
     * comma separated string
     */
    private static function getDescendants($node) {
        $descendants = "$node->id";
        foreach($node->sons as $son) {
            $descendants .= ",".self::getDescendants($son);
        }
        return $descendants;
    }

    /**
     * Returns all subcategories of the current category.
     * @return <string> current node's ID and all its descendants' IDs,
     * comma separated string
     */
    public function getDescendantsOfCurrentCategory() {
        $descendants = $this->getDescendants($this->currentNode);

        return $descendants;
    }


}

/**
 * Category printer that prints a menu from a category tree.
 */
class CategoryMenuPrinter {
    /// text-indent of top category
    static $TOP_CATEGORY_INDENT = 10;

    /// text-indent that is added for every depth level
    static $NEXT_CATEGORY_INDENT = 20;

    /** 
     * Prints a category node at a specified depth.
     *
     * @param <Category> $category node to be printed
     * @param <int> $depth of the node in the category tree
     */
    static function printCategoryMenuItem($category, $depth) {
        $realDepth = self::$TOP_CATEGORY_INDENT +
            $depth * self::$NEXT_CATEGORY_INDENT;

        $catClass = $category->isCurrent ? 'class="current"' : '';

        echo "<li><a style=\"text-indent:${realDepth}px;\"
        href=\"catalog.php?cat=$category->id\" $catClass>
        <img src=\"img/arrow.gif\" width=\"11\" height=\"11\" alt=\"\">
        $category->name</a>";
    }

}


/**Class representing one category node with category ratings.
 */

class RatingCategory extends Category{

    ///Array indexed by methods containing users and their ratings.
    ///(something like: array(methodID => array(userID => rating)))
    public $methods;

    public function __construct($id, $father, $methods){

        parent::__construct($id, $father);

        $this->methods = $methods;
        
    }
}

/**
 * Class representing a tree of categories with their ratings. This is used
 * to count ratings of all categories when only ratings of leaf categories are
 * known. Every node's ratings are averages of ratings of its children (for each
 * user and method).
 */
class RatingCategories extends Categories{

    /**
     * Constructs category tree with their ratings.
     *
     * @param <sqlResult> $allCategories sqlResult with categories and their ratings.
     *                      MUST be sorted by superCategory and then by categoryID.
     */
    public function __construct($allCategories) {
        
        $this->baseNode = new RatingCategory(0, 0, array()); // virtual 'Top category'
        $lastSuperCategory = 0; // used not to have to findNode every time
        $node = $this->baseNode; // start from the base node

        // parse the sql result
        $methods = array();
        $lastLine = array('categoryID' => 0);
        while($line = mysql_fetch_assoc($allCategories)) {
            
            // different category read
            if ($lastLine['categoryID'] != $line['categoryID'] &&
                $lastLine['categoryID'] > 0){

                //add the category to the tree
                $node->addSon(
                    new RatingCategory($lastLine["categoryID"], $node, $methods));                

                //clear methods array again
                $methods = array();

                // if super category has changed, find another parent to add
                // children to
                if($line["superCategory"] != $lastSuperCategory) {
                    $node = $this->findNode($line["superCategory"],$this->baseNode);
                    $lastSuperCategory = $line["superCategory"];
                }
            }            
            // add rating to the methods array
            if ($line['rating'] != null){
                $methods[$line['methodID']][$line['userID']] = $line['rating'];
            }

            $lastLine = $line;
        }

        // the last category must be added after the cycle
        if ($lastLine['categoryID'] > 0){
            $node->addSon(
                    new RatingCategory($lastLine["categoryID"], $node, $methods));            
        }

        // count ratings of all nodes
        $this->countRatings($this->baseNode);
    }

    /**
     * Returns the category tree as a string to be used in an sql query. The format
     * is (categoryID, userID, methodID, rating). Leaf categories are ommited,
     * caller already knows their ratings.
     *
     * @return <string> part of sql query with category IDs and ratings
     */
    public function asString(){
        return $this->nodeAsString($this->baseNode);
    }

    /**
     * Returns subtree of categories as a string (part of sql query).
     *
     * @param <type> $node to be serialized
     * @return <string> part of sql query
     */
    private function nodeAsString($node){
        if ($node->sonCount == 0 || (count($node->methods) == 0 && $node->id != 0))
            return "";

        // serialize node's methods and their ratings
        $str = "";
        $comma = "";
        foreach ($node->methods as $methodID => $methodValues){
            foreach ($methodValues as $userID => $rating){
                $str .= $comma . "($node->id, $userID, $methodID, $rating)";
                $comma = ", ";
            }
        }

        //serialize node's sons and append them to the string
        $comma = "";
        foreach($node->sons as $son) {
            $sonStr = $this->nodeAsString($son);
            
            if ($sonStr == "")
                continue;

            $str .= $comma . $sonStr;
            $comma = ", ";
        }
        return $str;
    }

    /**
     * Counts final ratings of a node and it's descendants. Leaf categories
     * must have its ratings defined, the tree is counted from leafs to the root.
     * Every node's ratings are averages of ratings of its children (for each
     * user and method).
     *
     * @param <type> $node node that will have its ratings counted
     */
    private function countRatings($node){
        
        // in case this is the leaf, stop
        if ($node->sonCount == 0)
            return;

        // count ratings of all descendants
        foreach ($node->sons as $son){
            $this->countRatings($son);
        }

        // don't need ratings of the root (because it's not a real category)
        if ($node->id == 0)
            return;

        // get sums of all ratings (for each user and method)
        foreach ($node->sons as $son){
            foreach ($son->methods as $methodID => $methodValues){
                foreach ($methodValues as $userID => $rating){
                    if (!isset($node->methods[$methodID][$userID])){
                        $node->methods[$methodID][$userID] = 0;
                    }
                    $node->methods[$methodID][$userID] += $rating;
                }
            }
        }

        // divide every sum by number of sons - count the average
        foreach ($node->methods as &$method){
            foreach ($method as &$rating){
                $rating /= $node->sonCount;
            }
        }

    }


}

?>