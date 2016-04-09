<?php

/**
 * @file
 *
 * Displays details of one product, uses ItemManager. Uses lightbox for gallery
 * of product's images. Also prints product description, ratings and comments.
 * Explicit rating is shown and registered users can choose their own. User
 * interaction is watched and sent from this page. See item.js and classes
 * TabSwitching, Rating, Behavior and Comments in main.js.
 */

require_once 'php/managers/itemManager.php';
$manager = new ItemManager();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" href="css/jquery.lightbox.css" type="text/css" media="screen">

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.lightbox.js"></script>
        <script type="text/javascript" src="js/jquery.cookie.js"></script>

        <script type="text/javascript" src="js/main.js"></script>

        <?php if ($manager->product != null){ ?>
        <script type="text/javascript">
            productID = <?php echo $manager->product->id ?>;            
            rating = <?php echo $manager->userLogged
                                ? $manager->product->userRatings->explRating
                                : $manager->product->globalRatings->explRating ?>;
            maxRating = <?php echo ProductRatings::$STARS_COUNT; ?>;
            userLogged = <?php echo $manager->userLogged ?>;
        </script>        
        <script type="text/javascript" src="js/item.js"></script>
        <?php } ?>

        <title>Product | Shop</title>

    </head>
    <body>

        <div id="entirePage">
            <?php
            require_once 'php/pageLayout/title.php';
            require_once 'php/pageLayout/leftMenu.php';
            require_once 'php/pageLayout/topMenu.php';
            require_once 'php/pageLayout/rightMenu.php';

            ?>
            <div id="main">
                <?php if ($manager->product != null){ ?>
                <div class="product">
                    <div class="image">
                        <a href="<?php echo $manager->product->bigImagesPaths[0] ?>"
                           rel="lightbox[item]" title="<?php echo $manager->product->name; ?>">
                            <img width="240" height="180" alt=""
                                 src="<?php echo $manager->product->imagePath; ?>">

                            <?php if (next($manager->product->bigImagesPaths) != false) { ?>
                            <span>All images</span>
                        </a>
                        <div class="otherImages">   
                                <?php while (list(, $path) = each($manager->product->bigImagesPaths)) { ?>
                            <a href="<?php echo $path ?>" rel="lightbox[item]"
                               title="<?php echo $manager->product->name; ?>"></a>
                                   <?php }//foreach that doesn't reset the array pointer ?>
                        </div>
                        <?php }else { echo '</a>';} ?>

                    </div>
                    <div class="rightColumn">
                        <div class="name">
                            <?php echo $manager->product->name; ?>
                        </div>
                        <div id="rating">
                            <div id="starsFull"></div>
                            <?php if ($manager->userLogged) { ?>
                            <div id="starsChoose"></div>
                            <div id="helpingStars">
                                <?php for ($i = 0; $i < ProductRatings::$STARS_COUNT; ++$i){ ?>
                                    <a class="star"></a>
                                <?php } ?>
                            </div>
                            <div id="coverLayer"></div>
                            <?php } ?>

                        </div>
                        <div class="separator"></div>
                        <div class="attributes">
                            <table>
                                <?php
                                foreach ($manager->product->attributes as $attr) {
                                    ?>
                                <tr><td><?php echo $attr->name ?><td>
                                            <?php printf($attr->formatString,$attr->value);
                                        }
                                        ?>
                            </table>
                        </div>

                        <form id="toCartForm" action="itemAdded.php" method="post">
                            <div class="priceBox">
                                <span class="price">
                                    <?php echo $manager->product->price ?>,-
                                </span>
                                <span class="VATPrice">
                                    (incl. VAT <?php echo $manager->product->VATPrice ?>,-)
                                </span>
                            </div>
                            <div class="cartControls">
                                <input type="hidden" name="addId" value="<?php echo $manager->product->id;?>">
                                Quantity: <input class="quantity" type="text" name="quantity" value="1">
                                <!--<input type="submit" name="buy" value="Add to cart">-->
                                <a class="button red fright"
                                   href="javascript:document.getElementById('toCartForm').submit()">
                                    Add to cart
                                </a>
                            </div>


                        </form>

                    </div>                    

                    <div class="separator"></div>
                    <div id="switches">
                        <a href="#">Product description</a>
                        <a href="#">Ratings breakdown</a>
                        <a href="#">User comments</a>
                    </div>
                    <div class="separator"></div>
                    <div class="tab">
                        <table cellspacing="0" cellpadding="0" id="description" class="rounded">
                            <tr>
                                <td class="corner">
                                    <img src="img/lt.gif" alt="" width="4" height="6">
                                <td>
                                <td class="corner">
                                    <img src="img/rt.gif" alt="" width="4" height="6">
                            <tr><td><td><?php echo $manager->product->description;?><td>
                            <tr>
                                <td class="corner">
                                    <img src="img/lb.gif" alt="" width="4" height="6">
                                <td>
                                <td class="corner">
                                    <img src="img/rb.gif" alt="" width="4" height="6">

                        </table>
                    </div>
                    <div class="tab">                        

                        <table cellspacing="0" cellpadding="0" class="rounded ratings">
                            <tr>
                                <td class="corner">
                                    <img src="img/lt.gif" alt="" width="4" height="6">
                                <td>
                                <td class="corner">
                                    <img src="img/rt.gif" alt="" width="4" height="6">
                            <tr><td><td>
                            <?php
                                echo "<table>";
                                ProductRatings::printTableHeader();

                                $manager->product->globalRatings->printMe();

                                if ($manager->userLogged){
                                    $manager->product->userRatings->printMe();
                                }
                                echo "</table>";
                            ?>
                            

                                <td>
                            <tr>
                                <td class="corner">
                                    <img src="img/lb.gif" alt="" width="4" height="6">
                                <td>
                                <td class="corner">
                                    <img src="img/rb.gif" alt="" width="4" height="6">

                        </table>
                        

                    </div>
                    <div class="tab">
                        <?php

                        foreach ($manager->product->comments as $comment) {
                            $comment->printMe();
                        }

                        if ($manager->userLogged) {
                            ?>
                        <p class="commentInfo">
                            Add your own comment here:
                        </p>

                        <div id="commentForm">
                            <textarea cols="50" rows="3" id="commentText"></textarea>
                            <input type="submit" id="addComment" value="Add comment" disabled="true">
                        </div>

                        <?php }else{ ?>
                        <p class="commentNotice">
                            Please sign in to post comments.
                        </p>
                        <?php } ?>
                    </div>



                </div>
                <?php }else{ ?>
                <div class="pageEmpty">Required item is not in database.</div>
                <?php } ?>
            </div>

            <div id="footer"></div>            
        </div>
    </body>
</html>