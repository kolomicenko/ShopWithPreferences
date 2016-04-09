<?php

// to safely use time and date functions
date_default_timezone_set('Europe/Prague');


/**
 * Class representing one user comment.
 * Before usage, date_default_timezone_set() must be called so that date() and
 * time() function can be safely used.
 */
class Comment{
    public $time, $text, $userName;

    function __construct($time, $text, $userName){
        $this->time = $time;
        $this->text = $text;
        $this->userName = $userName;
    }

    /**
     * Formats comment author's name. Only htmlspecialchars() function is used
     * now.
     * 
     * @param <type> name of the user
     * @return <string> formatted name
     */
    static function formatName($name){
        return htmlspecialchars($name);
    }

    /**
     * Formats text of a comment. Htmlspecialchars() function is used not to allow
     * users to use any html tags (for security reasons). Newlines are transferred
     * to html to preserve basic structure.
     * 
     * @param <type> original text from user.
     * @return <string> formatted text.
     */
    static function formatText($text){
        return str_replace("\n",'<br>',htmlspecialchars($text));
    }

    /**
     * Formats time of a comment.
     *
     * @param <int> $time as unix timestamp.
     * @return <string> text containing formatted time.
     */
    static function formatTime($time){
        return date('j.n.Y G:i:s', $time);

    }

    /**
     * Prints the comment. Even before, it is formatted.
     */
    function printMe(){
        ?>
        <table cellspacing="0" cellpadding="0" class="rounded comment">
            <tr>
                <td class="corner">
                    <img src="img/lt.gif" alt="" width="4" height="6">
                <td>
                <td class="corner">
                    <img src="img/rt.gif" alt="" width="4" height="6">
            <tr><td>
                <td>
                    <div class="userName"><?php echo $this->userName;?></div>
                    <div class="time"><?php echo self::formatTime($this->time);?></div>
                    <div class="text"><?php echo self::formatText($this->text);?></div>
                <td>
            <tr>
                <td class="corner">
                    <img src="img/lb.gif" alt="" width="4" height="6">
                <td>
                <td class="corner">
                    <img src="img/rb.gif" alt="" width="4" height="6">

        </table>
        <?php
    }
}


?>
