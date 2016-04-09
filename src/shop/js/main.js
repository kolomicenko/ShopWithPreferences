// file description for doxygen
/**
 * @file
 * Don't read this page, go to <a href="../js_docs_out/overview-summary-main.js.html">
 * Main.js documentation</a> 
 *
 */


// file description for jsdoc
/**
 * @fileoverview
 * Main script that contains all classes, should be included in every page.
 * This script creates {@link BehaviorCookies} and {@link LoginForm} automatically
 * because this is what every page needs.
 *
 */



/**
 * Posts data to server using http POST. Creates and submits a html form to do this.
 * Usual use when a hyperlink doing POST is needed.
 *
 * @param {String} address Address to send POST request to
 * @param {Array} parameters Array of values indexed by names
 */
//usage: <a href="javascript:postData('target.php',{'name':'value', 'name2':'value2'});">
function postData(address, parameters) {
  var myForm = document.createElement("form");
  myForm.method = "post";
  myForm.action = address;
  for (name in parameters) {
    var myInput = document.createElement("input");
    myInput.setAttribute("name", name);
    myInput.setAttribute("value", parameters[name]);
    myForm.appendChild(myInput);
  }
  document.body.appendChild(myForm) ;
  myForm.submit() ;
  document.body.removeChild(myForm) ;
}

/**
 * @class
 * Animated showing/hiding of the login form on top of the page.
 * Login message is hidden on the first click.
 *
 * @constructor
 */
function LoginForm(){
    var thiss = this;

    /** Div with message to the user about successfull login/logout. */
    this.loginMessage = $('#loginMessage');

    /** Email input field. */
    this.loginEmail = $('#loginEmail');

    $('#formToggler').click(function(){
        thiss.loginMessage.hide(); // don't show it again
        $('#loginForm').animate({opacity: 'toggle'}, 200, function(){
            thiss.loginEmail.focus(); // focus the email input field
        });
        
        return false;
    });

    $('#formSubmit').click(function(){
        $('#loginForm').submit();

        return false;
    });    
}


/**
 * @class
 * Class for working with cookies containing user behavior.
 * Behavior is saved to cookies, they are sent and then deleted allways when
 * a page is loading.
 *
 * @constructor
 */
function BehaviorCookies(){
    var behavior = BehaviorCookies.get();
    if (behavior != null){
        Behavior.send(behavior); // send cookies to the server
        BehaviorCookies.remove(); // and delete them
    }
}

/**
 * Sets behavior cookies to specified values. Saves them for one month (when
 * user closes browser or navigates to different domain, cookies are kept and
 * sent when user gets to this application again).
 * 
 */
BehaviorCookies.set = function(productID, scrollCount, clickCount, displayTime,
                                displayCount){

    /// Lifetime of the cookies in days
    var cookieLifeTime = 30;

    $.cookie('productID', productID, {'expires': cookieLifeTime});
    $.cookie('scrollCount', scrollCount, {'expires': cookieLifeTime});
    $.cookie('clickCount', clickCount, {'expires': cookieLifeTime});
    $.cookie('displayTime', displayTime, {'expires': cookieLifeTime});
    $.cookie('displayCount', displayCount, {'expires': cookieLifeTime});
};

/**
 * Returns behavior cookies as a json object.
 * @return Json object or null if cookies don't exist
 */
BehaviorCookies.get = function(){
    var productID = $.cookie('productID');
    if (productID != null && productID != '' && productID > 0){
        return {"productID" : productID,
                "scrollCount" : $.cookie('scrollCount'),
                "clickCount" : $.cookie('clickCount'),
                "displayTime" : $.cookie('displayTime'),
                "displayCount" : $.cookie('displayCount')};
    }
    return null;
};

/**
 * Deletes behavior cookies. 
 */
BehaviorCookies.remove = function(){
    $.cookie('productID', null);
    $.cookie('scrollCount', null);
    $.cookie('clickCount', null);
    $.cookie('displayTime', null);
    $.cookie('displayCount', null);    
};





/**
 * @class
 * Class for switching tabs with description, user comments,... on the item page.
 * Switches among tabs as user clicks on links with tab names. First tab is
 * selected when the page loads.
 * @constructor
 */
function TabSwitching (){
    var thiss = this;

    /** Tabs jQuery object */
    this.tabs = $('.tab');

    /** jQuery object with the switches (the links) */
    this.switches = $('#switches a'); 

    /** Tab currently visible */
    this.currentIndex = 0; 

    this.switches.each(function(index){
        $(this).click(function(){
            thiss.switches.eq(thiss.currentIndex).removeClass('selected');
            thiss.tabs.eq(thiss.currentIndex).hide();
            thiss.currentIndex = index;
            thiss.switches.eq(thiss.currentIndex).addClass('selected');
            thiss.tabs.eq(thiss.currentIndex).show();
            return false;
        });

    }).first().click(); // select first tab in the beginning

}

/**
 * @class
 * Product rating functionality. Logged users can also change the rating by
 * clicking on one of the stars. There are five layers of stars on top of each
 * other. The first layer is Empty Stars, stars with grey color. The second one is
 * Full Stars, stars with yellow color that have given width and thus indicate
 * the rating of the product. The third layer is Choose Stars, stars with blue color,
 * which gets to foreground as user moves over the rating div. This one indicates
 * the rating that user is going to choose and changes as user moves the mouse.
 * This is done with the help of the fourth layer, which is transparent and consists
 * of separate divs, each representing one star, which fire the mouse events.
 * Without this helping layer, mouse coords would have to be counted, which is more
 * difficult for the browser. Last layer is the Cover layer which is used after
 * user clicks and sends the rating. It covers all layers underneath until the
 * response from the server is received, so that user can't click and send rating
 * more than once at a time.
 *
 * @constructor
 */
function Rating(rating, maxRating, productID, userLogged){
    var thiss = this;

    /** ID of current product. */
    this.productID = productID;
    /** Indicates whether the user is logged or not.*/
    this.userLogged = userLogged;
    /** Current rating of the product.*/
    this.rating = rating;

    /** Width of one star image in pixels. */
    this.starWidth = 20;

    /** jQuery object for Full Stars div.*/
    this.starsF = $('#starsFull');
    
    // sets the width of full stars
    // this way even fractions of stars can be "highlighted"
    this.starsF.css('width', Math.round(this.starWidth * rating));

    // set width of empty stars
    $('#rating').css('width', Math.round(this.starWidth * maxRating));

    if (this.userLogged){

        /** jQuery object for Choose Stars div.*/
        this.starsCh = $('#starsChoose');

        /** jQuery object for the last (helping) layer.*/
        this.helpingStars = $('#helpingStars');

        $('#rating').mouseleave(function(){ // when user leaves rating div
            thiss.starsCh.css('width', 0);
            thiss.starsF.show();
        }).find('.star').each(function(i){  // when user moves mouse of the
            $(this).mouseenter(function(){  // last layer (helping stars)
                thiss.starsF.hide();
                thiss.starsCh.css('width', (i + 1)* thiss.starWidth);
                // alter width of Choose Stars (this way no star fractions)
            });
            $(this).click(function(){ //when user clicks it, ajax-send the rating
                //temporarily hide all the layers, so that event are not fired
                $('#coverLayer').show();
                //thiss.helpingStars.css('width', 0);

                // hide Choose Stars layer
                thiss.starsCh.css('width', 0);
                // set the Full Stars layer to new value
                thiss.starsF.css('width', (i + 1)* thiss.starWidth).show();
                // send rating to the server
                $.post("php/ajax/rating.php",
                {
                    "productID": thiss.productID,
                    "rating": (i + 1)
                },
                function(){
                    // now user can send other ratings again
                    $('#coverLayer').hide();
                    //thiss.helpingStars.css('width', 100);
                }
                );
            });
        });
    }else{
        $('#rating').click(function(){ // guest users cannot rate products
            alert("Please sign in to rate products.");
        });

    }

}

/**
 * @class
 * Class that captures and sends user behavior to the server by ajax. Click count,
 * display time and amount of scrolling are observed. OnUnload event is fired
 * when user leaves current page, which is the time to send information about
 * user's behavior on that page. However, there are some issues with this event:
 *<p>
 *Opera doesn't fire onUnload when navigating to the same server (i.e. history
 *navigation or page refreshing). There are two ways how to solve this.
 *Either send behavior using ajax every certain amount of time (but make sure
 *that displayCount is incremented just once - that's why displayCount is allways
 *sent to the server as part of the behavior), or save behavior to cookies
 *{@link BehaviorCookies} and then ajax-send it just once when next page loads.
 *Cookies are much better, this approach causes no extra requests to the server.
 *<p>
 *There is a third option (for Opera), to switch off fast history navigation by
 *history.navigationMode = 'compatible' (http://www.opera.com/support/kb/view/827/).
 *But using this as well as using other ways discussed in the article didn't
 *really do anything (at least on my computer).
 *<p>
 *There is even a fourth solution that also doesn't work too well. DisplayCount
 *can be counted even by the server. Then, if displayCount counted by javascript
 *is smaller, it means onUnload wasn't fired a couple of times. Missing
 *values can be counted from the difference, but this can't be generally
 *precise and in worst case the page could be loaded from cache, which could
 *cause the server's display count to be smaller (and it does happen).
 *<p>
 *The cookies solution works like this:
 *Behavior is saved from cookies to database at the time of next page request.
 *(which definitely is the very next request, because user stayed at the same
 *server - otherwise unload would've been fired, behavior sent using ajax
 * and cookies deleted)
 *<p>
 *PHP cannot be used to read the cookies, because next page request (with
 *the cookies) is sent BEFORE onUnload is fired. That means, if the browser
 *fired onUnload and next page wasn't got from cache, behavior would be saved
 *into database TWICE. That's why dealing with cookies can be done only with
 *javascript when the next page is being loaded. This also solves afore-
 *mentioned cache problem.
 *<p>
 *There is one more problem. With webkit (chrome, safari) ajax request never
 *reach server when run in onUnload event AND user stays at the same domain.
 *Don't know if this is an intended behavior or a bug, either way, server must
 *deal with cookies at this situation because they are already deleted on the
 *client side at the time of next page load. In case the page is fetched
 *from cache (at this webkit situation), there is NO solution other than
 *setting {@link Behavior#SEND_INFO_OFTEN} to true or, as a last resort, changing
 *cache control. Someone may think that the problem could be solved by not
 *deleting the cookies before ajax returns a reply from a server, which
 *actually would work for webkit, but not for firefox, because it doesn't
 *wait for an ajax reply during page unload (which makes sense after all), so
 *that cookies wouldn't be deleted and behavior saved twice.
 *<p>
 *There are even more issues with browsers. Such as whenever a window has
 *been ctrl-tabbed or alt-tabbed out and back, firefox doesn't fire onunload
 *upon tab closing (ctrl-w), which must be a bug. Opera doesn't fire onUnload
 *when it should much more often (upon tab closing, browser closing, ...)
 *<p>
 *As a consequence, {@link Behavior#SEND_INFO_OFTEN} is used for Opera implicitly.
 *IE and Firefox should not make any problems. And Chrome and Safari are
 *also OK, with the use of cookies.
 *<p>
 *EDIT: as onUnload makes so many problems, it's been decided to use only cookies
 *to send the info. Their lifetime should be longer than just one session. That
 *way no behavior will be lost, in worst case just uploaded later (when user
 *opens the pages again). I resorted to this after all the issues with Opera or
 *Chrome and after realizing that ratings recounting (which may take a bit longer)
 *is done after sending the behavior and IE waits for whole operation to complete.
 *And Firefox waits too, when re-refreshing again.
 *
 * @constructor
 */

function Behavior(productID){
    var thiss = this;

    /** Whether to ajax-send behavior info in certain interval
     * {@link Behavior#INFO_INTERVAL}.*/
    this.SEND_INFO_OFTEN = false || $.browser.opera;

    //EDIT: set even opera no to use this (use cookies instead)
    this.SEND_INFO_OFTEN = false;

    /** How often to send the info by ajax (in miliseconds). */
    this.INFO_INTERVAL = 5000;

    /** Whether behavior has been sent from this page.
     *  Used not to increment display count when behavior info is not sent
     *  for the first time, so that this only makes sense when SEND_INFO_OFTEN
     *  is set to true. */
    this.infoBeenSent = false;

    /** Whether to save behavior to cookies.
     *  At least on of SEND_INFO_OFTEN or USE_BEHAVIOR_COOKIES should be used,
     *  USE_BEHAVIOR_COOKIES is recommended.
     */
    this.USE_BEHAVIOR_COOKIES = true;

    /**
     * Whether to send info when onUnload event is fired.
     */
    this.USE_ONUNLOAD = false;

    /** How often to save behavior cookies (in miliseconds). */
    this.COOKIES_INTERVAL = 100;

    /** Pointer to cookies saving interval function. */
    this.cookiesTimer = null;

    /** 
     *  Maximum time user is expected to view the page. No bigger values are
     *  sent to the server as behavior.
     */
    this.MAX_DISPLAY_TIME = 1000 * 60 * 5; // five minutes

    /** ID of current product. */
    this.productID = productID;

    /** Total amount of pixels user scrolled by. */
    this.scrollCount = 0;

    /** Last position of content in the window.
     * Used to count amount of scrolling. */
    this.lastScrollTop = 0;

    /** Number of mouse clicks user does. */
    this.clickCount = 0;

    /** Last time page was focused. */
    this.timeFocused = new Date().valueOf();

    /** Total time the page has been focused. */
    this.displayTime = 0;

    /** Whether the window is focused right now. */
    this.focused = true;

    /** Whether the page is saved to bookmarks. This is not used now, there
     *  is no way to find this out using javascript. */
    this.bookmarked = false;

    /** Total amount of pixels user moved mouse by. Not used now, because
     *  mouse moving is not too relevant behavior. But maybe this would be able to
     *  tell whether user really sits in front of PC and reads the page.
     *  */
    this.mouseMove = 0;

    // when user scrolls the page
    $(window).scroll(function(){
        //thiss.scrollCount ++; // not precise (browsers don't fire the event as often)
        // it is better to count pixels
        var currentScrollTop = $(window).scrollTop();
        thiss.scrollCount += Math.abs(currentScrollTop - thiss.lastScrollTop);
        thiss.lastScrollTop = currentScrollTop;
    });

    // record all click to links (tab switches, images, ...)
    $('#main a').click(function(){
        thiss.clickCount ++;
    });

    // if window is blurred (user goes to another tab or leaves the browser),
    // stop counting time
    $(window).blur(function(){
        if (!thiss.focused)
            return;
        thiss.focused = false;
        thiss.displayTime += new Date().valueOf() - thiss.timeFocused;
    });

    // when focused, start counting time again
    $(window).focus(function(){
        if (thiss.focused)
            return;
        thiss.focused = true;
        thiss.timeFocused = new Date().valueOf();
    });

    // send info about behavior to server when user is leaving the page
    if (this.USE_ONUNLOAD){
        //set onUnload cookie for server to know that onUnload has been used
        $.cookie('onUnload', 1);

        $(window).unload(function(){
            // disable cookies timer first
            if (thiss.USE_BEHAVIOR_COOKIES){
                clearInterval(thiss.cookiesTimer);
            }
            // send the behavior
            thiss.sendInfo();
        });
    }

    
    // send info directly to server every certain amount of time
    if (this.SEND_INFO_OFTEN){
        window.setInterval(function() {
            thiss.sendInfo();
            }, this.INFO_INTERVAL);
    }
    
    // save behavior cookies every certain amount of time
    if (this.USE_BEHAVIOR_COOKIES){
        this.cookiesTimer = window.setInterval(function() {
            thiss.setBehaviorCookies();
            }, this.COOKIES_INTERVAL);

        $(window).unload(function(){
            thiss.setBehaviorCookies();
        });

    }
}

/** Counts {@link Behavior#displayTime} from {@link Behavior#focused}
 *and {@link Behavior#timeFocused}. Maximum display time restriction is applied. */
Behavior.prototype.countDisplayTime = function(){
    // if window is focused, displayTime has increased since last call
    if (this.focused){
        var time = new Date().valueOf();
        this.displayTime += time - this.timeFocused;
        this.timeFocused = time;
    }

    // check if maximum display time was reached
    if (this.displayTime > this.MAX_DISPLAY_TIME){
        this.displayTime = this.MAX_DISPLAY_TIME;
    }
}

/**
 * Sends behavior info to the server. Also removes behavior cookies as they are
 * not needed when the behavior has already been sent.
 */
Behavior.prototype.sendInfo = function (){
    var thiss = this; // jquery changes scope of 'this'

    this.countDisplayTime();

    // if there's nothing to send, return
    // makes more sense when SEND_INFO_OFTEN
    if (this.displayTime == 0 && this.scrollCount == 0 && this.clickCount == 0)
        return;

    //if info has been sent using ajax (thanks to SEND_INFO_OFTEN),
    //don't increment displayCount again
    var displayCount = this.infoBeenSent ? 0 : 1;
    this.infoBeenSent = true;

    // send all the info to the server    
    Behavior.send(
        {
            "productID": thiss.productID,
            "scrollCount": thiss.scrollCount,
            "clickCount": thiss.clickCount,
            "displayTime": thiss.displayTime,
            "displayCount": displayCount
        }
    );

    // remove cookies now
    // if this is during onUnload, timer has been disabled already
        
    BehaviorCookies.remove(); // nothing happens if they don't exist    

    /*alert("scrollCount " + thiss.scrollCount +
            "\n clickCount " + thiss.clickCount +
            "\n displayTime " + thiss.displayTime);*/

    // reset all values (make sense only if SEND_INFO_OFTEN
    this.displayTime = 0;
    this.scrollCount = 0;
    this.clickCount = 0;
}

/**
 * Saves behavior into cookies. Call this using the {@link Behavior#cookiesTimer}.
 *
 **/
Behavior.prototype.setBehaviorCookies = function(){
    this.countDisplayTime();
    var displayCount = this.infoBeenSent ? 0 : 1; //if info has been sent
    //using ajax (thanks to SEND_INFO_OFTEN), don't increment displayCount again

    BehaviorCookies.set(this.productID, this.scrollCount, this.clickCount, 
                        this.displayTime, displayCount);

}

/** Sends behavior to the server (using ajax)
 *
 * @param {json object} behavior Behavior values that will be sent.
 */
Behavior.send = function(behavior){
    $.post("php/ajax/behavior.php", behavior);
}


/**@class
 * Class that adds comments functionality.
 * Ajax is used to post comments, so that page doesn't have to refreshed. When
 * server returns the comment details (formated text, time, ...) the comment
 * is added as a new element to the page and the page is scrolled to that element.
 * @constructor
 */

function Comments(productID){
    var thiss = this;

    /// Maximum length of the comment, in chars.
    this.MAX_TEXT_LENGTH = 512;

    /** ID of current product. */
    this.productID = productID;

    /** Button that sends the comment to the server.*/
    this.button = $('#addComment');

    /** jQuery object of the textarea with comment text. */
    this.text = $('#commentText');

    // enabling/disabling the Add comment button and limiting length of the text
    function textareaHandler(text){
        if (text.value.length > thiss.MAX_TEXT_LENGTH){
            text.value = text.value.substring(0, thiss.MAX_TEXT_LENGTH);
        }

        thiss.button[0].disabled = text.value == "";
    }
    
    this.text.keyup(function(){        
        textareaHandler(this);
    });
    this.text.change(function(){
        textareaHandler(this);
    });

    //send comment using ajax, add it to the page and scroll so that user can see it
    this.button.click(function(){
        this.disabled = true;
        $.post("php/ajax/addComment.php",
        {
            "productID": thiss.productID,
            "text": thiss.text.val()
        }
        , function(data){
            $('#commentForm').parent().prepend(data).each(function(){
                    // count new comment's offset
                    var newOffset = $(this).offset().top - 100;
                    
                    /* ScrollObject is an object that will be scrolled.
                     * Some browsers need 'html' to be scrolled, others need
                     * 'body'. No browser minds if both elements are used, except
                     * for Opera, which would scroll "twice".
                     */
                    var scrollObject = $.browser.opera ? $('html') : $('html, body');

                    scrollObject.animate({scrollTop: newOffset}, 500); // use animation
            });
            thiss.text.val(""); // clear the textarea
        });

    });


}

/**
 * @class
 * Scrolling of page numbers on the catalog page.
 * Sometimes regular javascript functions and properties are used, sometimes
 * those of jQuery, depending on what is easier.
 * @constructor
 */
function PageNumbers (formPosition){
    var thiss = this;

    /** jQuery object of the whole form div.*/
    this.form = $('#controlsForm' + formPosition);

    /** Div with the page numbers. */
    this.pageNumbers = this.form.find('.pageNumbers');

    /** Scroll left button. */
    this.toLeft = this.form.find('.toLeft');

    /** Scroll right button. */
    this.toRight = this.form.find('.toRight');

    /** Scroll to the beginning button. */
    this.toBeginning = this.form.find('.toBeginning');

    /** Scroll to the end button. */
    this.toEnd = this.form.find('.toEnd');

    /** Scroll left/right step in pixels.  */
    this.scrollStep = 70;

    /** How far in pixels the number of current page should be. */
    this.currentPageNoOffset = 60;

    // scroll page numbers so that currentPageNoOffset is satisfied
    this.pageNumbers.scrollLeft(this.form.find('.currentPageNo').offset().left
        - this.pageNumbers.offset().left - this.currentPageNoOffset);

    // assign events to the buttons
    this.toLeft.click(function(){
        thiss.scroll(-1, false);        
    });
    this.toRight.click(function(){
        thiss.scroll(1, false);
    });
    this.toBeginning.click(function(){
        thiss.scroll(-1, true);
    });
    this.toEnd.click(function(){
        thiss.scroll(1, true);
    });

    // hide/show some of the buttons
    this.checkBorders();
}


/**
 * Scroll numbers in a specified direction
 *
 * @param {integer} direction -1 for scrolling to the left, +1 to the right
 * @param {bool} allTheWay whether to scroll all the way to the end/beginning
 *
 */
PageNumbers.prototype.scroll = function (direction, allTheWay){
    var thiss = this;
    
    if (allTheWay)
        if (direction < 0)            
            this.pageNumbers.animate({scrollLeft: 0}, 1000, function(){
                thiss.checkBorders();
            });
        else            
            this.pageNumbers.animate({scrollLeft: this.pageNumbers[0].scrollWidth},
                                     1000, function(){
                thiss.checkBorders();
            });
            // the [0] (or get(0)) returns DOM node from the jQuery object
    else{        
        this.pageNumbers.animate({scrollLeft: '+=' + (this.scrollStep * direction)},
                                 300, function(){
                thiss.checkBorders();
            });
    }    
};

/**
 * Checks whether to hide or show buttons for scrolling.
 * The visibility css property instead of display css property is toggled.
 * There could also be a checkFutureBorders function which will hide/show buttons
 * before animation and not after.
 *
 */
PageNumbers.prototype.checkBorders = function(){
    if (this.pageNumbers.scrollLeft() == 0){
        this.toLeft.css('visibility', 'hidden');
        this.toBeginning.css('visibility', 'hidden');
    }else{
        this.toLeft.css('visibility', 'visible');
        this.toBeginning.css('visibility', 'visible');
    }
    if (this.pageNumbers[0].clientWidth >= this.pageNumbers[0].scrollWidth ||
            this.pageNumbers[0].scrollWidth == this.pageNumbers.scrollLeft() + this.pageNumbers[0].clientWidth){
        this.toRight.css('visibility', 'hidden');
        this.toEnd.css('visibility', 'hidden');
    }else{
        this.toRight.css('visibility', 'visible');
        this.toEnd.css('visibility', 'visible');
    }
};

/**
 * @class
 * Provides shopping cart functionality. Ajax is used to send all changes of the
 * cart (item removal, item quantity change, order preference change) to the server.
 * Also changes cart table after that (recounts prices, changes quantities, ...)
 *
 * @constructor
 */
function CartControls(){
    var thiss = this;

    /** The 'Continue Order' or 'Submit Order' button. */
    this.rightButton = $('#main .button.fright');

    /** The loading image. */
    this.loading = $('#loadingGif');

    // for tds to have fixed height and for animation of their hiding
    $('table.cart td').wrapInner("<div />");

    // user changes quantity of an item
    $('input.quantity').change(function(){
        thiss.sendChange(this, this.value);
    });

    // when user clicks 'Continue Order', just show order preferences
    $('#submitButton').click(function(){
        // change buttons caption to 'Submit order'
        $(this).html('Submit order').click(function(){
            // change the button's click event handler
            // now the button really submits the order
            postData('checkOut.php',{'checkOut':'yes'});
        });
        $('#orderPrefs').slideDown(300);

        return false;
    });

    // user changes order preference, ajax-send it to the server
    $('#orderPrefs input:radio').change(function(){        
        thiss.startLoading();
        $.post('php/ajax/changePrefType.php',
            {
                "name": this.name,
                "value": this.value
            },
            function(){
                thiss.stopLoading();
            }
        );
    });

    // user removes an item from the cart
    $('a.removeItemButton').click(function(){
        thiss.sendChange(this, 0);
        return false;
    });

    // change the radio button event when user clicks to whole table row
    $('table.prefTypeTable tr').click(function(){
        var radio = $(this).find('input:radio').get(0);
        if (radio.checked == false){
            radio.checked = true;
            $(radio).change(); // don't forget to fire the event
        }
        
    });
}

/**
 * Sends item's quantity change to the server and changes the cart table
 * appropriately.
 *
 * @param {Object} button Button that fired the event (removal or quantity change)
 * @param {int} quantity Desired quantity of the item (removal = 0)
 *
 */
CartControls.prototype.sendChange = function(button, quantity){
    var thiss = this;

    // for user not to be able to submit the order now
    this.startLoading();

    // table row the button is in
    var tr = $(button).parents('tr').first();

    $.post('php/ajax/changeCart.php',
        {
            "id": button.name,
            "quantity": quantity
        },
        function(data){
            thiss.stopLoading();
            
            if (data == null){ // something bad has happened
                window.location.reload(); // reload the page to "reset" javascript
                return;
            }
            if (data.quantity == 0){ // item has been removed
                // change the count in title of the page
                $('#titleItemCount').html(data.itemCount);
                if (data.itemCount == 0){ // nothing left in the cart
                   // remove everything
                   thiss.rightButton.remove();
                   $('table.cart, #orderPrefs').remove();
                   //display message 'nothing in cart';
                   $('div.cartInfo').removeClass('noDisplay');
                   return;
                }
                //something left in the cart, just hide the removed item
                
                //use div inner wrappers to do this, because td or tr don't
                //have the height css property
                tr.children('td').children('div').each(function(){
                    $(this).slideUp(300, function(){
                        tr.remove(); // remove the row when completed
                    });
                });
            }else{ // just quantity changed, set the item prices and quantity
                tr.find('input').val(data.quantity);
                tr.find('td.price div').html(data.price + ',-');
                tr.find('td.VATPrice div').html(data.VATPrice + ',-');
            }
            // set total prices
            // in case the cart is empty and the cart table deleted, nothing happens
            $('#totalPrice').html(data.totalPrice + ',-');
            $('#totalVATPrice').html(data.totalVATPrice + ',-');            
        },
        "json"
    );
};

/** Displays the loading image and hides the Submit button. */
CartControls.prototype.startLoading = function(){
    this.rightButton.hide();
    this.loading.show();
};

/** Displays the Submit button and hides the loading image. */
CartControls.prototype.stopLoading = function(){
    this.loading.hide();
    this.rightButton.show();
};


// do this on every page
$(document).ready(function(){

    // check if there is still some behavior saved in cookies    
    new BehaviorCookies();


    //toggling the form for logging in
    new LoginForm();



});
