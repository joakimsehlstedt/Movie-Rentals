<?php

/**
 * Class for rendering HTML and CSS to CCalendar. Big version with
 * pictures to represent each month.
 *
 * @author      Pär Eriksson <par.erikson@telia.com>
 * @copyright   Pär Eriksson 2013
 * @license     http://opensource.org/licenses/MIT - MIT
 * @package     Partus framework - Calendar
 */
class CCalendarRenderBig extends CCalendar {

    /**
     * Properties.
     */
    protected $actualDate;
    protected $html;
    //Labels for calendar displays
    protected $labelsMonths = array('01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December');
    protected $labelDays = array('1' => 'Mon',
        '2' => 'Tue',
        '3' => 'Wed',
        '4' => 'Thu',
        '5' => 'Fri',
        '6' => 'Sat',
        '7' => 'Sun');

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct() {
        //Property used to highlight todays date in calendar.
        $this->actualDate = date("Y-m-d");
    }

    /**
     * Creates HTML output for calendar-array recieved from 
     * parent::getWeekRows(). 
     * 
     * @return string containing HTML output.
     */
    public function Render() {
        $calendar = parent::getWeekRows();

        //1. Start of calendar HTML
        $this->html .= "<div class='container'>\n";
        //2. Verify month and year, print correct image for display. Default image if no exists.
        $pathtoimg = "img/calendar/";
        $img = $this->currentDate . ".jpg";
        if (!glob($pathtoimg . $img)) {
            $img = "default.jpg";
        }

        $this->html .= "<figure class='seven columns'>"
                . "<img src='img.php?src=calendar/" . $img 
                . "&amp;width=400&amp;save-as=jpg&amp;quality=60' alt=''/></figure>\n";

        //3. Print out month, year and link to prev/next months.
        //Create links, adds leading zero to month if needed for correct link format.
        $prev = "?month=" . sprintf("%02s", $this->prevMonth) . "-" . $this->yearPrevMonth;
        $next = "?month=" . sprintf("%02s", $this->nextMonth) . "-" . $this->yearNextMonth;

        $this->html .= "<div class='nine columns'><div class='monthline'>\n";
        $this->html .= "<a href='" . $prev . "' class='left'>&lt;&lt;&lt;</a>\n";
        $this->html .= $this->labelsMonths[$this->currentMonth] . " - " . $this->currentYear;
        $this->html .= "<a href='" . $next . "' class='right'>&gt;&gt;&gt;</a>\n";
        $this->html .= "</div>\n";

        //4. Print out first row with week and day-labels.
        $this->html .= "
      <table class='calendarbig'>\n<tbody>\n<tr class='dayrow'>\n
      <td class='week'>Week</td>\n
      <td class=''>" . $this->labelDays['1'] . "</td>\n
      <td class=''>" . $this->labelDays['2'] . "</td>\n
      <td class=''>" . $this->labelDays['3'] . "</td>\n
      <td class=''>" . $this->labelDays['4'] . "</td>\n
      <td class=''>" . $this->labelDays['5'] . "</td>\n
      <td class=''>" . $this->labelDays['6'] . "</td>\n
      <td class=''>" . $this->labelDays['7'] . "</td>\n
      </tr>\n";

        //5. Loop out rows with days and weeks.
        foreach ($calendar as $key => $val) {
            $this->html .= "<tr class='days'>";
            $this->html .= "<td class='week'>" . $this->getWeekNumber($calendar[$key]) . "</td>\n";

            $is_sunday = 1; //When == 7; iteration is on sunday, mark red, but only current month.
            foreach ($calendar[$key] as $key2 => $value) {
                $red = $is_sunday == 7 && $calendar[$key][$key2]['mark'] == "currentmonth" ? " redday" : "";
                //Highlight if this is today.
                $today = $this->actualDate == $calendar[$key][$key2]['date'] ? " today" : "";
                $this->html .= "<td class='day " . $calendar[$key][$key2]['mark'] . $red . $today . "'>" . $calendar[$key][$key2]['calday'] . "</td>\n";
                $is_sunday++;
            }

            $this->html .= "</tr>\n";
        }

        //6. Close loop and calendar HTML.
        $this->html .= "</tbody>\n</table>\n</div>\n</div>\n";

        return $this->html;
    }

}
