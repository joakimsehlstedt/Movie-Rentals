<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

/**
 * Generate calendar.
 */
/**
 * Set month to view in calendar. If not sent in $_GET, sets todays values.
 * Also checks numeric format: 'MM-YYYY'.
 */
if (isset($_GET['month']) && preg_match('/^[0-9]{2}+-[0-9]{4}$/', $_GET['month'])) {
    $month = $_GET['month'];
} else {
    $month = date("m") . "-" . date("Y"); //Ex. 'Sep-2013'.
}

$myCalendar = new CCalendarRenderBig();
$myCalendar->setMonth($month);
$output = $myCalendar->Render();

// Add content to the Ami array.
$ami['title'] = "Calendar";

$ami['main'] = <<<EOD
<article>
<h2 class='sixteen columns'>Movie of the month</h2>
{$output}
</article>
EOD;

$ami['pageID'] = "calendar";

// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);
