<?php

/**
 * Class for rendering HTML and CSS to CCalendar. Smaller
 * version to show in a side-bar, footer or similar.
 *
 * @author      Pär Eriksson <par.erikson@telia.com>
 * @copyright   Pär Eriksson 2013
 * @license     http://opensource.org/licenses/MIT - MIT
 * @package     Partus framework - Calendar
 */
class CCalendarRenderSmall extends CCalendar {

    /**
     * Properties.
     */
    protected $actualDate;
    protected $html;
    //Labels for calendar displays
    protected $labelsMonths = array('01' => 'Jan',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Apr',
        '05' => 'May',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Aug',
        '09' => 'Sep',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dec');
    protected $labelDays = array('1' => 'M',
        '2' => 'T',
        '3' => 'W',
        '4' => 'T',
        '5' => 'F',
        '6' => 'S',
        '7' => 'S');

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

        //1. Print out month and year 
        $this->html .= "<table class='calendarsmall'>\n<tbody>\n";
        $this->html .= "<tr><td class='smallmonthline' colspan='8'>" . $this->labelsMonths[$this->currentMonth] . " - " . $this->currentYear . "</td></tr>";

        //2. Print out first row with week and day-labels.
        $this->html .= "<tr class='smalldayrow'>\n
      <td>v.</td>\n
      <td class=''>" . $this->labelDays['1'] . "</td>\n
      <td class=''>" . $this->labelDays['2'] . "</td>\n
      <td class=''>" . $this->labelDays['3'] . "</td>\n
      <td class=''>" . $this->labelDays['4'] . "</td>\n
      <td class=''>" . $this->labelDays['5'] . "</td>\n
      <td class=''>" . $this->labelDays['6'] . "</td>\n
      <td class=''>" . $this->labelDays['7'] . "</td>\n
      </tr>\n";

        //3. Loop out rows with days and weeks.
        foreach ($calendar as $key => $val) {
            $this->html .= "<tr class='smalldays'>";
            $this->html .= "<td>" . $this->getWeekNumber($calendar[$key]) . "</td>\n";

            $is_sunday = 1; //When == 7; iteration is on sunday, mark red, but only current month.
            foreach ($calendar[$key] as $key2 => $value) {
                $red = $is_sunday == 7 && $calendar[$key][$key2]['mark'] == "currentmonth" ? " redday" : "";
                //Highlight if this is today.
                $today = $this->actualDate == $calendar[$key][$key2]['date'] ? " today" : "";
                $this->html .= "<td class='smallday " . $calendar[$key][$key2]['mark'] . $red . $today . "'>" . $calendar[$key][$key2]['calday'] . "</td>\n";
                $is_sunday++;
            }

            $this->html .= "</tr>\n";
        }

        //6. Close loop and calendar HTML.
        $this->html .= "</tbody>\n</table>\n";

        return $this->html;
    }

}
