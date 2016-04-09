/**
 * @file
 * Script for page showing one item (item.php).
 * Creates <a href="../js_docs_out/TabSwitching.html">TabSwitching</a>,
 * <a href="../js_docs_out/Rating.html">Rating</a>,
 * <a href="../js_docs_out/Behavior.html">Behavior</a>,
 * <a href="../js_docs_out/Comments.html">Comments</a>
 */

$(document).ready(function(){

    //lightbox
    $('div.image a').lightBox();

    //tab switches (description, ratings breakdown, user comments)
    new TabSwitching();

    //rating
    new Rating(rating, maxRating, productID, userLogged);


    //behavior
    new Behavior(productID);

    //comments
    if (userLogged){
        new Comments(productID);
    }    
});




