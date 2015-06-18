<?php

/**
 * CDiceImage
 * 
 * A dice with css controlled jpg sprite as graphical output. 
 * Subclass to the CDice class.
 * 
 * Author: Joakim Sehlstedt, March 2014
 */

class CDiceImage extends CDice {
    // properties
    const FACES = 6;
    
    // constructors
    public function __construct() {
        parent::__construct();  // call base class constructor
    }
    
    /**
     * Formats a html-list with the object's rolls in form of a graphic dice.
     * @return string in html format.
     */
    public function GetRollsGraphicList() {
        $html = "<ul class='dice'>";
        foreach ($this->rolls as $val) {
            $html .= "<li class='dice-{$val}'></li>";
        }
        $html .= "</ul>";
        return $html;      
    }
}
