<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

// connect to database
$db = new CDatabase($ami['database']);

// render new releases feature
$sql = "SELECT id, title, image FROM RM_Movie ORDER BY updated DESC LIMIT 3;";
$res = $db->ExecuteSelectQueryAndFetchAll($sql);
$main_img = "<div class='sixteen columns'><h2>New releases</h2></div><div id='new_releases'>";
foreach ($res as $key => $movie) {
    $float = ($key == 0) ? "" : "";
    $title = (strlen($movie->title) > 33) ? 
            substr($movie->title, 0, 31) . '...' : $movie->title;
    $main_img .= "<a href='view.php?id={$movie->id}'><figure class='one-third column {$float}'>";
    $main_img .= "<img class='scale-with-grid' src='img.php?src=" . htmlentities($movie->image, null, 'UTF-8');
    $main_img .= "&amp;width=300&amp;height=150&amp;crop-to-fit&amp;save-as=jpg&amp;quality=60' alt='N/A'/>";
    $main_img .= "<figcaption class='main_figcap'>{$title}</figcaption></figure></a>";   
}
$main_img .= "</div>";
        
// render genre feature
$sql = "SELECT * FROM RM_Genre;";
$res = $db->ExecuteSelectQueryAndFetchAll($sql);
$main_genre = "<ul class='genre'>";
foreach ($res as $value) {
    $main_genre .= "<li class='genre-{$value->id}'><a href='list.php?g={$value->id}'>$value->name</a></li>";
}
$main_genre .= "</ul>";

// Create CPage objects
$options1 = array(
    'database' => $ami['database'],
    'url' => 'article1',
);
$article1 = new CPage($options1);

$options2 = array(
    'database' => $ami['database'],
    'url' => 'article2',
);
$article2 = new CPage($options2);

// Create CBlog object
$options_blog = array(
    'database' => $ami['database'],
    'limit' => 3,
);
$blog = new CBlog($options_blog);

// Add content to the Ami array.
$ami['title'] = "Home";
$ami['main'] = $main_img
        . "<article id='genremenutitle' class='two columns'><h4>Browse genre</h4></article>"
        . "<article id='genremenu' class='fourteen columns'>"
        . $main_genre . "</article>"
        . "<article class='eight columns'><h2>" . $article1->getHtmlTitle() . "</h2>"
        . "<p>" . $article1->getHtmlMain() . "</p></article>"
        . "<article class='eight columns'><h2>" . $article2->getHtmlTitle() . "</h2>"
        . "<p>" . $article2->getHtmlMain() . "</p></article>"
        . "<article class='sixteen columns'><br><h2>Latest News</h2>" 
        . $blog->getHtml() . "</article>";

// Add content to the Ami array. -----------------------------------
$ami['pageID'] = "home";

// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);
