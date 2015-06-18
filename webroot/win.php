<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

// game instructions
$instructions = "<hr><h4>Instructions</h4><br><p>1. [Roll] -> Roll the dice.</p>"
        . "<p>2. [Save] -> Save your current total and start next roll.</p>"
        . "<p>3. [Start/Restart] -> Restart the whole game (wipe scoreboard).</p>";

// contains html code for user input and active player data
$diceHtml = "<div>"
        . "<form method='post' action='win.php'>"
        . "<input type='submit' value='Roll' name='doRoll' />"
        . "<input type='submit' value='Save' name='doSave' />"
        . "<input type='submit' value='Start/Restart' name='doRestart' /></form>";

// html string for the scoreboard
$scoreHtml = "<div class='five columns'>";

// form if winning
$winForm = "<div class='sixteen columns'><form method='post' action='win.php'>"
        . "<label>Please give us your full name to claim the price:</label>"
        . "<input type='text' name='fullName' />"
        . "<input type='submit' value='Submit' name='claimWin' /></form></div>";

// Restart session (game)
if (isset($_POST['doRestart']) && htmlentities($_POST['doRestart']) == "Start/Restart") {
    if (isset($_SESSION['dice100game'])) {
        unset($_SESSION['dice100game']);
    }
}

/**
 * This codeblock contains the logic for the user input.
 */
if (isset($_SESSION['dice100game']) && ($_SESSION['dice100game'] instanceof CDice100)) {
    $game = $_SESSION['dice100game'];
    // play the game
    if (isset($_POST['doRoll']) && htmlentities($_POST['doRoll']) == "Roll") {
        $diceHtml .= $game->AnotherRoll();
    }
    if (isset($_POST['doSave']) && htmlentities($_POST['doSave']) == "Save") {
        $diceHtml .= $game->SaveMyRolls();
    }
    $scoreHtml .= $game->ScoreBoard() . "</div>";
} else if (isset($_POST['fullName']) && isset($_POST['claimWin'])) {
    // connect to database
    $db = new CDatabase($ami['database']);
    // add winner to database
    $fullName = htmlentities($_POST['fullName']);
    $sql = "INSERT INTO Winners(name) VALUES ('" . $fullName . "');";
    $res = $db->ExecuteQuery($sql);
    if ($res) {
        $diceHtml = "<div class='sixteen columns'>"
                . "<h2>Thank you for your participation {$fullName}.</h2>"
                . "<h3>We will contact you shortly to inform you how to "
                . "claim your price.</h3></div>";
    } else {
        $diceHtml = "<div class='sixteen columns'>"
                . "<h2>Your entry was not successful.</h2>"
                . "<h3>Please try again later.</h3></div>";
    }
    $scoreHtml = "";
} else {
    // start a new game
    $game = new CDice100(1);
    $_SESSION['dice100game'] = $game;
    $diceHtml .= "<div><p>Please start the game and your first roll by "
            . "pressing the 'Roll' button. First to 100 wins!</p>";
    $scoreHtml .= "</div>";
}

if (isset($_SESSION['rounds']) && ($_SESSION['rounds'] <= 15)) {
    $winHtml = "<div class='sixteen columns'><br><h2>You did it in "
            . $_SESSION['rounds'] . " rounds! You've won free rentals!</h2></div>"
            . $winForm;
    unset($_SESSION['rounds']);
} else if (isset($_SESSION['rounds']) && ($_SESSION['rounds'] > 15)) {
    $winHtml .= "<div class='sixteen columns'><br><h2>You did it in "
            . $_SESSION['rounds'] . " rounds. Sorry, you didn't qualify for the free rentals.</h2></div>";
    unset($_SESSION['rounds']);
} else {
    $winHtml = "";
}

$diceHtml .= "</div>";

// load the ami array with relevant data
$ami['stylesheets'][] = 'css/CDiceImage.css';
$ami['main'] = "<div class='sixteen columns'>"
        . "<img class='floatright' src='img.php?src=system/win.png&amp;width=300&amp;"
        . "save-as=png&amp;quality=1' alt='N/A'/>"
        . "<h2>WIN free rentals!</h2>"
        . "<p>Play the first to 100 dice game and claim the reward! "
        . "If you manage to get to 100 points in 15 rounds or less, you are entitled "
        . "to 3 free DVD or Blu-Ray rentals. Play the game, and if successful, we "
        . "will ask for your contact details at the end. Good Luck!</p>"
        . $diceHtml . $winHtml . $scoreHtml . $instructions . "</div></div>";


// Add content to the Ami array.
$ami['title'] = "WIN free rentals!";


// Add content to the Ami array. -----------------------------------
$ami['pageID'] = "win";

// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);
