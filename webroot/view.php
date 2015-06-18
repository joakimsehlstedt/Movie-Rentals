<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

// Get parameters for sorting
$id = isset($_GET['id']) && is_numeric($_GET['id']) ?
        htmlentities($_GET['id']) : null;

$db = new CDatabase($ami['database']);
$sql = "SELECT * FROM RM_Movie WHERE id = {$id};";
$result = $db->ExecuteSelectQueryAndFetchAll($sql);
$res = $result[0];

// Add content to the Ami array. -----------------------------------

$ami['main'] = "<figure class='seven columns'><img class='scale-with-grid' src='img.php?src=" . $res->image
        . "&amp;width=400&amp;save-as=jpg&amp;quality=60' alt=''/></figure>"
        . "<article class='nine columns'><h3>" . $res->title . "</h3>"
        . "<strong>Year: </strong>" . $res->year . "<br>"
        . "<strong>Lenght: </strong>" . $res->length . "<br>"
        . "<strong>Director: </strong>" . $res->director . "<br><br>"
        . "<div id='plot'><strong>Plot: </strong>" . $res->plot . "</div><br>"
        . "<strong>Subtitles: </strong>" . $res->subtext . "<br>"
        . "<strong>Speech: </strong>" . $res->speech . "<br>"
        . "<strong>Quality: </strong>" . $res->quality . "<br>"
        . "<strong>Format: </strong>" . $res->format . "<br><br>"
        . "<strong>Price: </strong><br><br><h2><span id='price'>£" . $res->price . "</span></h2><br>"
        . "<h4>For more info...</h4>"
        . "<strong>IMDb page: </strong><a target='_blank' href='" . $res->imdb . "'>{$res->imdb}</a><br>"
        . "<strong>Youtube trailer: </strong><a target='_blank' href='" . $res->youtube . "'>{$res->youtube}</a><br><br>"
        . "</article>";


$ami['pageID'] = "view";
$ami['title'] = "Movie Details";


// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);
