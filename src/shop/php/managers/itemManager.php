<?php

require_once 'php/managers/categoriesManager.php';
require_once 'php/data/product.php';
require_once 'php/data/attribute.php';
require_once 'php/data/productRatings.php';
require_once 'php/data/comment.php';

/**
 * This is a manager for item.php. It gets from database everything needed
 * (product name, price, images, ratings, attributes, description, comments)
 * and creates a Product object.
 * <p>
 * In the left menu, category tree is still displayed for the user to know
 * where they are. That's why category id is carried in GET. But there are cases
 * when it's difficult to carry the id in GET (ie. when coming from shopping
 * cart), so the cat. ID must be fetched from database before calling constructor
 * of parent (which is CategoriesManager).
 * <p>
 * Naturally, the other and necessary item in GET is the product's ID.
 */
class ItemManager  extends CategoriesManager{

    public $product = null; ///< The current product object
    
    function __construct(){

        // need to know product's categoryID before calling parent's constructor

        // product and images
        $productResult = query("select *
                    from products natural join imagepaths
                    where productID=%d and imgSize <> 'small'
                    order by imgSize asc",  // medium image and then the big ones
                  $_GET["id"]);

        $product = mysql_fetch_assoc($productResult);
        if ($product == false){ // fake input, non existing productID in GET
            parent::__construct();
            return;
        }

        // parent constructor
        parent::__construct($product['categoryID']);

        // product rating, default is null, if exists it is overwritten
        // global = average rating of all users, user = private rating of current one
        $globalRatings = $userRatings = null;
        
        // for explicit global rating, select average of all users
        // mysql doesn't have full outer join, here is a 'union all' workaround
        $ratingsRes = query("
            SELECT productID, userID, finalRating, displayCountRating,
               scrollRating, clickRating, displayTimeRating, method, orderRating,
               IFNULL(  -- if explRating not known, set it to 0
                  IF(userID = 0, -- for unlogged user, use average
                      (SELECT AVG(explRating) FROM explratings
                       WHERE productID = r.productID
                      ),
                     explRating
                  ), 0
               ) explRating
            FROM implratings r NATURAL JOIN methods NATURAL LEFT JOIN explratings
            WHERE productID = $product[productID]
                  AND (userID = 0 or userID = $this->userID)
           
            UNION ALL -- instead of FULL OUTER JOIN (which is not supported in mysql)

            SELECT productID, userID, 0 as finalRating, 0 as displayCountRating,
                0 as scrollRating, 0 as clickRating, 0 as displayTimeRating,
                0 as method, 0 as orderRating, explRating
            FROM
            (SELECT userID, productID, explRating FROM explratings

             UNION -- user's expl ratings and global expl ratings needed

             SELECT 0 as userID, productID, avg(explRating) FROM explratings
             GROUP BY productID   -- faster then WHERE approach
            ) as e NATURAL LEFT JOIN implratings r
            WHERE r.productID IS NULL AND
                  e.productID = $product[productID]
                  AND (userID = 0 or userID = $this->userID)
            ORDER BY method

        ");

        // parse the ratings result
        while ($line = mysql_fetch_assoc($ratingsRes)){            
            if ($line['userID'] > 0){ // user rating
                if ($userRatings == null){
                    $userRatings = new UserRatings($line['explRating'],
                                                   $line['orderRating'],
                                                   $line['displayCountRating']);
                }
                $userRatings->addRatingsMethod($line['method'], $line['scrollRating'],
                    $line['clickRating'], $line['displayTimeRating'], $line['finalRating']);
            }else{ // global rating
                if ($globalRatings == null){
                    $globalRatings = new GlobalRatings($line['explRating'],
                                                       $line['orderRating'],
                                                       $line['displayCountRating']);
                }
                $globalRatings->addRatingsMethod($line['method'], $line['scrollRating'],
                    $line['clickRating'], $line['displayTimeRating'], $line['finalRating']);
            }
        }


        //product attributes
        $attrsSql = query("select * from attrvalues natural join attributes where
                productID = %d", $_GET["id"]);

        $attrs = array();
        while ($attr = mysql_fetch_assoc($attrsSql)){
                $attrs[] = new Attribute($attr['attrName'],
                                         $attr[$attr['type'].'Value'],
                                         $attr['formatString']);
        }

        // product images (the big ones)
        $bigImagesPaths = array();
        while ($bigImagePath = mysql_fetch_assoc($productResult)){
            $bigImagesPaths[] = $bigImagePath['imgPath'];
        }

        //product description (lorem ipsum most of the time, to save space)
        $description = $product['productDescription'];
        if ($description == ''){
            require_once 'php/data/loremIpsum.php';
            $description = loremIpsum();
        }//not to have to store the same description for each product in database

        // comments on the product
        $commentsSql = query("select * from comments natural join users 
                                where productID = %d or 
                                productID = 0 -- comments for all products
                                order by time desc, commentID", $_GET['id']);
        $comments = array();
        while ($line = mysql_fetch_assoc($commentsSql)){
            $comments[] = new Comment($line['time'], $line['text'], $line['userName']);
        }

        // create the product
        $this->product = new DetailedProduct($product['productName'],
                                             $product['productPrice'],
                                             $product['imgPath'],
                                             $attrs,
                                             $product['productID'],
                                             $bigImagesPaths,
                                             $description,
                                             $globalRatings,
                                             $userRatings,
                                             $comments);

        
    }

    /**
     * Prints similar (or alternative) items in the right menu.
     *
     */
    function printRightMenu(){
    ?>
    <div class="caption" style="background-image: url('img/rightMenuAI.png');"></div>

    <?php
    if ($this->product == null){ // fake input
        return;
    }

    $result = $this->bb->getPreference("SimilarItems", 5, $this->product->id);

    while($line = mysql_fetch_assoc($result)){
      ?>
      <div class="item">
          <img width="190" height="12" alt="" src="img/top_bg_right.gif">
          <a href="item.php?id=<?php echo $line['productID']; ?>&amp;cat=<?php echo $this->currentCategory; ?>">
              <span class="name"><?php echo $line['productName']?></span>
              <span class="price"><?php echo $line['productPrice']?>,-</span>
              <br>
          <img src="<?php echo $line['imgPath']; ?>" alt="">
          </a>
          <img width="190" height="12" alt="" src="img/bot_bg_right.gif">
      </div>

      <?php


    }
  }
}
?>
