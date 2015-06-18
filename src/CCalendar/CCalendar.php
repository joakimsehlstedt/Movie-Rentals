<?php

/**
 * Class for creating and rendering different versions of a calendar.
 * Abstract! Not meant to instantiate directly. Extend to create HTML 
 * and CSS layouts as needed, to add database calendar events etc.
 * 
 * Usage in child class:
 * setMonth() method must be used, with or without argument. Argument
 * passed must use MM-YYYY format for month. Without argument, it will
 * always show actual current month.
 * 
 * Method getWeekRows() will return sorted arrays containing 
 * information about the chosen calendar month and its days. Is to be
 * used in child class to fetch the month-data.
 *
 * @author      Pär Eriksson <par.erikson@telia.com>
 * @copyright   Pär Eriksson 2013
 * @license     http://opensource.org/licenses/MIT - MIT
 * @package     Partus framework - Calendar
 * 
 * @todo returning arrays needs work.
 */
abstract class CCalendar {
    /**
     * Properties
     */

    /**
     * Properties used to keep track of current month and year
     * shown in browser.
     */
    protected $currentYear;
    protected $currentMonth;
    protected $currentMonthMaxDays;
    protected $currentDate;

    /**
     * Properties used to calculate number of days to show from 
     * previous and next months in the calendar
     */
    protected $firstWeekdayCurrentMonth;
    protected $lastWeekdayCurrentMonth;

    /**
     * Properties used to store info about previous and next 
     * months. Made as member variables so they can be used in 
     * child classes, for example links in a calendar.
     */
    protected $prevMonth;
    protected $nextMonth;
    protected $yearPrevMonth;
    protected $yearNextMonth;

    /**
     * Constructor.
     * Sets properties.
     * 
     * @return void
     */
    private function __construct() {
        $this->currentDate = date("m") . "-" . date("Y");
    }

    /**
     * Sets month and year for calendar display.
     * Extracts information from date supplied by
     * pagecontroller. Format for date: MM-YYYY.
     * Ex. 11-2013.
     * 
     * @return void
     */
    public function setMonth($date = NULL) {
        //Argument for setMonth optional. If not sent, current month always used.
        if ($date == NULL) {
            $this->currentDate = date("m") . "-" . date("Y");
        } else {
            $this->currentDate = $date;
        }

        $month = substr($this->currentDate, 0, 2);
        $year = substr($this->currentDate, 3, 4);
        $this->currentMonth = $month;
        $this->currentYear = $year;
        $this->currentMonthMaxDays = cal_days_in_month(CAL_GREGORIAN, ltrim($this->currentMonth, '0'), $this->currentYear);
    }

    /**
     * Creates an array containing days of the month
     * and a value to use in CSS rendering.
     * 
     * @return array with days of current month
     */
    protected function getDaysThisMonth() {
        $daysToShow;

        for ($i = 1; $i <= $this->currentMonthMaxDays; $i++) {
            //Find weekday for date in loop?
            $daysToShow[$i]['calday'] = $i; //Value used in CSS in child class render method.
            //Actual date of the day in YYYY-MM-DD format. Can be used to place database calendar events correctly.
            $daysToShow[$i]['date'] = $this->currentYear . "-" . sprintf("%02s", $this->currentMonth) . "-" . sprintf("%02s", $i);
            //Week of the year this day is in.
            $daysToShow[$i]['week'] = ltrim(date('W', strtotime($daysToShow[$i]['date'])), '0'); //Remove leading zero.
            //To be used in CSS to color it correctly.
            $daysToShow[$i]['mark'] = "currentmonth";
        }
        return $daysToShow;
    }

    /**
     * Creates an array with the last number of days
     * in the previous month to be presented in the calendar.
     * 
     * @return array with last days of previous month
     */
    protected function getLastDaysPrevMonth() {
        $this->prevMonth = $this->currentMonth - 1;
        $this->yearPrevMonth = $this->currentYear;
        if ($this->prevMonth == 0) {
            $this->prevMonth = 12;
            $this->yearPrevMonth = $this->currentYear - 1;
        }

        $maxDays = cal_days_in_month(CAL_GREGORIAN, $this->prevMonth, $this->yearPrevMonth);

        $date = $this->currentYear . "-" . $this->currentMonth . "-" . 1;
        $firstDayCurrentMonth = date("N", strtotime($date));

        $count = abs(1 - $firstDayCurrentMonth); //How many days to display in calendar. Converted to positive integer.
        for ($i = 1; $i <= $count; $i++) {
            //Day number to be displayed in calendar.
            $daysToShow[$maxDays]['calday'] = $maxDays;
            //Actual date of the day in YYYY-MM-DD format. Can be used to place database calendar events correctly.
            $daysToShow[$maxDays]['date'] = $this->yearPrevMonth . "-" . sprintf("%02s", $this->prevMonth) . "-" . sprintf("%02s", $maxDays);
            //Week of the year this day is in.
            $daysToShow[$maxDays]['week'] = ltrim(date('W', strtotime($daysToShow[$maxDays]['date'])), '0'); //Remove leading zero.
            //To be used in CSS to color it correctly.
            $daysToShow[$maxDays]['mark'] = "notcurrentmonth";

            $maxDays--;
        }

        if (isset($daysToShow)) {
            return array_reverse($daysToShow); //Reverse array for correct display in calendar.
        }
    }

    /**
     * Creates an array with the first number of days
     * in the next month to be presented in the calendar.
     * 
     * @return array with first days of next month
     */
    protected function getFirstDaysNextMonth() {
        $this->nextMonth = $this->currentMonth + 1;
        $this->yearNextMonth = $this->currentYear;
        if ($this->nextMonth == 13) {
            $this->nextMonth = 1;
            $this->yearNextMonth = $this->currentYear + 1;
        }

        $date = $this->currentYear . "-" . $this->currentMonth . "-" . $this->currentMonthMaxDays;
        $lastDayCurrentMonth = date("N", strtotime($date));

        $count = 7 - $lastDayCurrentMonth; //How many days of next month will show in calendar.
        for ($i = 1; $i <= $count; $i++) {
            //$daysToShow[$i] = $i; //Value used in CSS in child class render method.
            //Find weekday for date in loop?
            $daysToShow[$i]['calday'] = $i; //Value used in CSS in child class render method.
            //Actual date of the day in YYYY-MM-DD format. Can be used to place database calendar events correctly.
            $daysToShow[$i]['date'] = $this->yearNextMonth . "-" . sprintf("%02s", $this->nextMonth) . "-" . sprintf("%02s", $i);
            //Week of the year this day is in.
            $daysToShow[$i]['week'] = ltrim(date('W', strtotime($daysToShow[$i]['date'])), '0'); //Remove leading zero.
            //To be used in CSS to color it correctly. Add highlights to show current day or red days in child class.
            $daysToShow[$i]['mark'] = "notcurrentmonth";
        }

        if (isset($daysToShow)) {
            return $daysToShow;
        }
    }

    /**
     * Merges days to show in calendar to one array.
     * Last days previous month first, the current month
     * and then first days of next month.
     * Checks so no array is empty before merging.
     * 
     * @return array merge from one or more functions.
     * @todo find a better way to return arrays.
     */
    protected function getFullMonthView() {
        $prevM = $this->getLastDaysPrevMonth();
        $thisM = $this->getDaysThisMonth();
        $nextM = $this->getFirstDaysNextMonth();

        /**
         * Return only if functions return valid array.
         */
        if (isset($prevM) && isset($nextM)) {
            return array_merge($prevM, $thisM, $nextM);
        } elseif (!isset($prevM) && isset($nextM)) {
            return array_merge($thisM, $nextM);
        } elseif (isset($prevM) && !isset($nextM)) {
            return array_merge($prevM, $thisM);
        } else {
            return $thisM;
        }
    }

    /**
     * Splits an array into a array with each main key
     * representing a week row, each row contains data on
     * seven days. Also creates week number to first level
     * of the array instead of saving it on each day only.
     * 
     * @return array with 7 days in each key.
     */
    protected function getWeekRows() {
        $month = $this->getFullMonthView();
        $weekrows = array_chunk($month, 7);

        return $weekrows;
    }

    /**
     * This method can be used in child class to fetch 
     * week number from part of array after getWeekRows 
     * in a loop to return current week number.
     * 
     * @return string with weeknumber. 
     */
    protected function getWeekNumber($week) {
        $weeknumber = $week['0']['week'];
        return $weeknumber;
    }

    /**
     * Public method to return dumped WeekRow array only, 
     * to show on example page, for debug purposes.
     * 
     * @return array with calendar array.
     */
    public function arrayExample() {
        return $this->getWeekRows();
    }

}
