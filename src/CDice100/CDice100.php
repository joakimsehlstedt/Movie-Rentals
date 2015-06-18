<?php

/**
 * CDice100 
 * The main class for the Dice 100 game.
 * Needs a user interface like dice100.php, the CDiceImage class and CDice class. 
 * 
 * Author: Joakim Sehlstedt, March 2014
 */
class CDice100 {

    // declare properies
    private $game = array();        // array for all active CDiceImage objects
    private $scoreBoard = array();  // array to store scores for all rounds
    private $html = "";             // string for html output
    private $scoreHtml = "";        // string for scoreboard html output
    private $numberOfPlayers = 1;   // 1 to 4 players
    private $rollingPlayer;         // player id 0 to 3
    private $round = 0;             // indicates current round (first is 0)
    private $totals = array();

    /**
     * CDice100 game constructor.
     * @param integer $numberOfPlayers, 1-4 allowed
     */
    public function __construct($numberOfPlayers = 1) {
        $this->scoreBoard = array();
        $this->rollingPlayer = 0;
        $this->html = "";
        $this->scoreHtml = "";
        $this->numberOfPlayers = $numberOfPlayers;
        $this->round = 0;
        $this->totals = array();

        if ($numberOfPlayers > 0 && $numberOfPlayers <= 4) {
            if ($numberOfPlayers >= 1) {
                $this->game[] = new CDiceImage();
            }
            if ($numberOfPlayers >= 2) {
                $this->game[] = new CDiceImage();
            }
            if ($numberOfPlayers >= 3) {
                $this->game[] = new CDiceImage();
            }
            if ($numberOfPlayers == 4) {
                $this->game[] = new CDiceImage();
            }
        } else {
            echo "Error! Only 1 to 4 players allowed.";
        }
    }

    /**
     * AnotherRoll executes the next roll of the active game.
     * @return type string, html string.
     */
    public function AnotherRoll() {
        $this->game[$this->rollingPlayer]->RollDice(1);
        $this->html = "<h3>PLAYER " . ($this->rollingPlayer + 1) . "</h3><br>";
        $this->html .= $this->game[$this->rollingPlayer]->GetRollsGraphicList();
        $this->GameStatus();
        return $this->html;
    }

    /**
     * SaveMyRolls saves rollingPlayer's score to scoreboard and activates next player.
     * @return type string, html string.
     */
    public function SaveMyRolls() {
        $this->NextPlayer();
        return $this->html;
    }

    /**
     * ScoreBoard formats a string containing a html table of all the scores from
     * the active game. Gets data from the scoreBoard array.
     * @return type string, html string.
     */
    public function ScoreBoard() {
        if ($this->numberOfPlayers > 0) {
            $this->scoreHtml = "<table style='width:100%;text-align:left;font-size:18px;'>"
                    . "<tr style='color:#00CA89;'><th>Round</th><th>P1</th>";
        }
        if ($this->numberOfPlayers > 1) {
            $this->scoreHtml .= "<th>P2</th>";
        }
        if ($this->numberOfPlayers > 2) {
            $this->scoreHtml .= "<th>P3</th>";
        }
        if ($this->numberOfPlayers > 3) {
            $this->scoreHtml .= "<th>P4</th>";
        }
        $this->scoreHtml .= "</tr>";

        foreach ($this->scoreBoard as $key => $round) {
            $html = "<tr><td>" . ($key + 1) . "</td>";
            foreach ($round as $playerScore) {
                $html .= "<td>" . $playerScore . "</td>";
            }

            $html .= "</tr>";
            $this->scoreHtml .= $html;
        }

        $this->scoreHtml .= "<tr><td>Total</td>";
        for ($i = 0; $i < $this->numberOfPlayers; $i++) {
            $this->totals[$i] = $this->GetPlayerTotal($i);
        }

        foreach ($this->totals as $key => $value) {
            if ($value == max($this->totals) && $value > 0) {
                $this->scoreHtml .= "<td style='background:#00CA89;'>" . $value . "</td>";
            } else {
                $this->scoreHtml .= "<td>" . $value . "</td>";
            }
        }
        $this->scoreHtml .= "</tr></table>";
        return $this->scoreHtml;
    }

    /**
     * NextPlayer activates the next player in line or moves to the next round
     * and player one if the last player has finished his roll.
     */
    private function NextPlayer() {
        $this->scoreBoard[$this->round][$this->rollingPlayer] = $this->game[$this->rollingPlayer]->GetTotal();
        $this->game[$this->rollingPlayer]->ResetRolls();
        if ($this->rollingPlayer < $this->numberOfPlayers - 1) {
            $this->rollingPlayer++;
        } else {
            $this->rollingPlayer = 0;
            $this->round++;
        }
        $this->html .= "<p>Next player, please press the 'roll' button for your first roll.</p>";
    }

    /**
     * GameStatus adds the status of the active player's round to the html variable.
     */
    private function GameStatus() {
        if ($this->game[$this->rollingPlayer]->GetLastRoll() == 1) {
            $this->html .= "<p>You got a 1. You lost this round!</p>";
            $this->game[$this->rollingPlayer]->ResetRolls();
            $this->NextPlayer();
        } elseif ((isset($this->totals[$this->rollingPlayer]) ? $this->totals[$this->rollingPlayer] : 0) +
                $this->game[$this->rollingPlayer]->GetTotal() >= 100) {
            $this->html .= "<p>YOU WON!</p>";
            $_SESSION['rounds'] = $this->round;
            dump($_SESSION['rounds']);
            unset($_SESSION['dice100game']);
        } else {
            $this->html .= "<p>Your total is: " . $this->game[$this->rollingPlayer]->GetTotal() . "</p>";
        }
    }

    /**
     * Sums the player's total score
     * 
     * @param type $player integer
     * @return type integer
     */
    private function GetPlayerTotal($player) {
        $total = 0;
        foreach ($this->scoreBoard as $round) {
            if (isset($round[$player])) {
                $total += $round[$player];
            }
        }
        return $total;
    }

}
