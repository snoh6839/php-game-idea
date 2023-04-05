<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php

    class BlackjackGame
    {
        private $deck = array();
        private $playerHand = array();
        private $dealerHand = array();
        private $playerScore = 0;
        private $dealerScore = 0;
        private $gameResult = '';

        public function __construct()
        {
            $suits = array('H', 'D', 'C', 'S');
            $ranks = array('A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K');

            foreach ($suits as $suit) {
                foreach ($ranks as $rank) {
                    $this->deck[] = $rank . $suit;
                }
            }

            shuffle($this->deck);
        }

        public function play()
        {
            if (!isset($_COOKIE['playerHand']) && !isset($_COOKIE['dealerHand'])) {
                $this->dealCards();
                $this->calculateScore();
            } else if (isset($_POST['hit'])) {
                $this->playerHand[] = array_shift($this->deck);
                $this->calculateScore();
            } else if (isset($_POST['stand'])) {
                $this->dealerTurn();
                $this->calculateScore();
                $this->calculateResult();
            } else if (isset($_POST['reset'])) {
                $this->resetGame();
            }

            $this->saveGame();
            $this->render();
        }

        private function dealCards()
        {
            $this->playerHand[] = array_shift($this->deck);
            $this->dealerHand[] = array_shift($this->deck);
            $this->playerHand[] = array_shift($this->deck);
            $this->dealerHand[] = array_shift($this->deck);
        }

        private function calculateScore()
        {
            $this->playerScore = $this->calculateHandScore($this->playerHand);
            $this->dealerScore = $this->calculateHandScore($this->dealerHand);

            if ($this->playerScore > 21) {
                $this->gameResult = 'You busted!';
            } else if ($this->dealerScore > 21) {
                $this->gameResult = 'Dealer busted!';
            }
        }

        private function calculateHandScore($hand)
        {
            $score = 0;
            $numAces = 0;

            foreach ($hand as $card) {
                $rank = substr($card, 0, -1);

                if ($rank == 'A') {
                    $numAces++;
                    $score += 11;
                } else if (in_array($rank, array('K', 'Q', 'J'))) {
                    $score += 10;
                } else {
                    $score += $rank;
                }
            }

            while ($score > 21 && $numAces > 0) {
                $score -= 10;
                $numAces--;
            }

            return $score;
        }

        private function dealerTurn()
        {
            while ($this->dealerScore < 17) {
                $this->dealerHand[] = array_shift($this->deck);
                $this->calculateScore();
            }
        }
        private function calculateResult()
        {
            if ($this->playerScore > $this->dealerScore) {
                $this->gameResult = 'You win!';
            } else if ($this->dealerScore > $this->playerScore) {
                $this->gameResult = 'Dealer wins!';
            } else {
                $this->gameResult = 'Tie game!';
            }
        }

        private function resetGame()
        {
            $this->deck = array();
            $this->playerHand = array();
            $this->dealerHand = array();
            $this->playerScore = 0;
            $this->dealerScore = 0;
            $this->gameResult = '';

            $this->__construct();
        }

        private function saveGame()
        {
            setcookie('playerHand', serialize($this->playerHand), time() + (86400 * 30), '/');
            setcookie('dealerHand', serialize($this->dealerHand), time() + (86400 * 30), '/');
            setcookie('playerScore', $this->playerScore, time() + (86400 * 30), '/');
            setcookie('dealerScore', $this->dealerScore, time() + (86400 * 30), '/');
            setcookie('gameResult', $this->gameResult, time() + (86400 * 30), '/');
        }

        private function render()
        {
            echo '<h1>Blackjack Game</h1>';

            if (isset($_COOKIE['playerHand']) && isset($_COOKIE['dealerHand'])) {
                $playerHand = unserialize($_COOKIE['playerHand']);
                $dealerHand = unserialize($_COOKIE['dealerHand']);
                $playerScore = $_COOKIE['playerScore'];
                $dealerScore = $_COOKIE['dealerScore'];
                $gameResult = $_COOKIE['gameResult'];

                echo '<p>Your Hand: ' . implode(', ', $playerHand) . ' (' . $playerScore . ')</p>';
                echo '<p>Dealer Hand: ' . implode(', ', $dealerHand) . ' (' . $dealerScore . ')</p>';

                if ($gameResult == '') {
                    echo '<form method="post">';
                    echo '<input type="submit" name="hit" value="Hit">';
                    echo '<input type="submit" name="stand" value="Stand">';
                    echo '</form>';
                } else {
                    echo '<p>' . $gameResult . '</p>';
                    echo '<form method="post">';
                    echo '<input type="submit" name="reset" value="Play Again">';
                    echo '</form>';
                }
            } else {
                echo '<form method="post">';
                echo '<input type="submit" name="play" value="Start Game">';
                echo '</form>';
            }
        }
    }
    $game = new BlackjackGame;
    $game->play();
    ?>
</body>

</html>