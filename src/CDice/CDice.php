<?php

/*
 * Dice class. 
 * Renders a userdefined dice (x faces) and rolls it a userdefined amount of 
 * times. Saves an array of rolls until user resets the object.
 * 
 * Author: Joakim Sehlstedt, March 2014
 */

class CDice {

    // property declaration
    protected $rolls = array();
    private $faces = 6;

    public function __construct($faces = 6) {
        if ($faces > 0) {
            $this->faces = $faces;
        }
        $this->rolls = array();
    }

    /**
     * Rolls the dice $times amount of times.
     * 
     * @param int $times
     */
    public function RollDice($times) {
        for ($i = 0; $i < $times; $i++) {
            $this->rolls[] = rand(1, $this->faces);
        }
    }

    /**
     * Formats a html list of the $rolls arrays content.
     * 
     * @return string
     */
    public function ListResults() {
        $html = "<ul>";
        foreach ($this->rolls as $val) {
            $html .= "<li>{$val}</li>";
        }
        $html .= "</ul>";
        return $html;
    }

    /**
     * Formats a html line of the $rolls array's content.
     * 
     * @return string
     */
    public function LineResults() {
        $html = "";
        foreach ($this->rolls as $val) {
            $html .= "{$val}, ";
        }
        return $html;
    }

    /**
     * Sums all the throws in $rolls array and returns the value.
     * 
     * @return int
     */
    public function GetTotal() {
        return array_sum($this->rolls);
    }

    /**
     * Get the value of last roll in array.
     * 
     * @return int
     */
    public function GetLastRoll() {
        return end($this->rolls);
    }
    
    /**
     * Gets the average value of all the throws.
     * 
     * @return int
     */
    public function GetAverage() {
        if (count($this->rolls) != 0) {
            return array_sum($this->rolls) / count($this->rolls);
        } else {
            return "<mark>! dice not rolled, no average availble !</mark>";
        }
    }

    /**
     * Get the number of faces.
     * 
     * @return integer
     */
    public function GetFaces() {
        return $this->faces;
    }
    
    /**
     * Get the rolls as an array.
     * 
     * @return array of the rolls.
     */
    public function GetRolls() {
        return $this->rolls;
    }
    
    /**
     * Get the rolls as an array.
     * 
     * @return array of the rolls.
     */
    public function ResetRolls() {
        $this->rolls = array();
    }
}
