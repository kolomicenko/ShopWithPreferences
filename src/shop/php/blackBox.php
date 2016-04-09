<?php

// currentPath must be used for including from here (because blackBox is used
// from different scopes)

require_once currentPath('shop/php/data/preference.php');

//you will probably need to call session_start before using preferences

/**
 * Handles Preference objects, calls their set and getPreference method.
 * This class inherits from Preference because it appears as one class
 * that works with all kinds of user preferences. That's why Manager only needs
 * to access BlackBox and not the other Preferences. Inside, it keeps references
 * on particular objects and calls their methods as requested.
 */
class BlackBox extends Preference{

    private $preferences = array(); ///< array of Preference objects

    function __construct($userID){
        parent::__construct($userID);
    }

    /**
     * Calls getPreference function of a specified Preference object. Called
     * function is passed all the arguments but the first one. That's why this
     * accepts variable number of arguments, depending on what preference user
     * wants to get. But first argument must allways be the preference classname
     * to easily index the array of pointers or to create a new object with it.
     * All preference objects are not created before they are needed, to save speed.
     *
     * @param <string> $prefName name of the Preference child class
     * @return <Object> return value of the called preference function
     */
    function getPreference($prefName) {
        if(!isset($this->preferences[$prefName])) {
            $this->preferences[$prefName] = new $prefName($this->userID);
        }

        // get all argument of the function
        $args = func_get_args();
        // discard the first one (prefName)
        array_shift($args);
        // call getPreference function on desired object
        return $this->preferences[$prefName]->getPreference($args);
    }


    /**
     * The same as getPreference, but this calls setPreference instead.
     *
     * @param <string> $prefName name of the Preference child class
     * @return <Object> return value of the called preference function
     */
    function setPreference($prefName) {
        if(!isset($this->preferences[$prefName])) {
            $this->preferences[$prefName] = new $prefName($this->userID);
        }

        $args = func_get_args();
        array_shift($args);
        return $this->preferences[$prefName]->setPreference($args);
    }    
    

    

}


?>