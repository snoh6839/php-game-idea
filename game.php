<?php
class UpgradeGame {
private $items = array(
array("name" => "검", "type" => "item", "level" => 1, "ingredients" => array("철", "나무")),
array("name" => "방패", "type" => "item", "level" => 1, "ingredients" => array("나무", "가죽")),
array("name" => "반지", "type" => "item", "level" => 1, "ingredients" => array("금", "다이아몬드")),
);
private $type = array("검", "방패", "반지");
private $ingredients = array("철", "나무", "가죽", "금", "다이아몬드");
private $mathOperators = array("+", "-", "*");

function generateMathProblem() {
// Generate a simple math problem for the player to solve
$a = rand(1, 10);
$b = rand(1, 10);
$operator = $this->mathOperators[array_rand($this->mathOperators)];
$problem = "$a $operator $b";
return array("problem" => $problem, "answer" => eval("return $problem;"));
}

function checkAnswer($problem, $answer) {
// Check the player's answer to the math problem
return $answer == $problem["answer"];
}

function getIngredient() {
// Get a random ingredient for the player to obtain
return $this->ingredients[array_rand($this->ingredients)];
}

function getItem() {
    return $this->type[array_rand($this->type)];
}

function upgradeItem($itemName) {
// Upgrade the specified item if the player has all the required ingredients
$itemIndex = array_search($itemName, array_column($this->items, 'name'));
$item = $this->items[$itemIndex];
$ingredients = $item["ingredients"];
$inventory = $_SESSION['inventory'];
$ingredientQuantities = array_column($inventory, 'quantity', 'name');
$hasAllIngredients = true;
foreach ($ingredients as $ingredient) {
if (!array_key_exists($ingredient, $ingredientQuantities) || $ingredientQuantities[$ingredient] == 0) {
$hasAllIngredients = false;
break;
}
}
if ($hasAllIngredients) {
$itemLevel = $item["level"];
if ($itemLevel < 3) { foreach ($ingredients as $ingredient) { $ingredientIndex=array_search($ingredient, array_column($inventory, 'name' )); $_SESSION['inventory'][$ingredientIndex]['quantity']--; if ($_SESSION['inventory'][$ingredientIndex]['quantity']==0) { unset($_SESSION['inventory'][$ingredientIndex]); } } $this->items[$itemIndex]["level"] = $itemLevel + 1;
    return true;
    } else {
    return "아이템이 이미 최고 레벨입니다.";
    }
    } else {
    return "필요한 모든 재료를 가지고 있지 않습니다.";
    }
    }

    function displayInventory() {
    // Display the player's current inventory of ingredients and items
    $inventory = $_SESSION['inventory'];
    echo "<h2>인벤토리:</h2>";
    
    if (count($inventory) == 0) {
    echo "<p>아이템이나 재료가 없습니다.</p>";
    } else {
    foreach ($inventory as $item) {
    if ($item['type'] == 'item') {
    $itemIndex = array_search($item['name'], array_column($this->items, 'name'));
    $itemLevel = $this->items[$itemIndex]['level'];
    echo "<p>{$item['name']} (레벨 {$itemLevel})</p>";
    } else {
    echo "<p>{$item['name']} ({$item['quantity']}개)</p>";
    }
    }
    }
    }

    function startGame() {
    // Start the game by displaying the initial inventory and generating the first math problem
    session_start();
    if (!isset($_SESSION['inventory'])) {
    $_SESSION['inventory'] = array();
    }
    $this->displayInventory();
    $problem = $this->generateMathProblem();
    $_SESSION['problem'] = $problem;
    echo "<p>다음 문제를 푸세요: {$problem['problem']}</p><form method='post'><label for='answer'>답변:</label><input type='text' id='answer' name='answer'><button type='submit'>확인</button></form>";
    }

    function playGame() {
    // Play the game by checking the player's answer to the math problem and awarding ingredients or upgrading items
    session_start();
    $problem = $_SESSION['problem'];
    $answer = $_POST['answer'];
    if ($this->checkAnswer($problem, $answer)) {
    $ingredient = $this->getIngredient();
    $inventory = $_SESSION['inventory'];
    $ingredientIndex = array_search($ingredient, array_column($inventory, 'name'));
    $itemIndex = array_search($this->getItem(), array_column($inventory, 'name'));
    if ($ingredientIndex === false) {
    array_push($_SESSION['inventory'], array('name' => $ingredient, 'quantity' => 1, 'type' => 'ingredient'));
    array_push($_SESSION['inventory'], array('name' => $this->getItem(), 'quantity' => 1, 'type' => 'item'));
    } else {
    $_SESSION['inventory'][$ingredientIndex]['quantity']++;
    $_SESSION['inventory'][$itemIndex]['quantity']++;
    }
    $this->displayInventory();
    echo "<p>{$ingredient}을(를) 얻었습니다!</p>";
    } else {
    echo "<p>틀렸습니다. {$this->getItem()}을(를) 드릴테니 다시 시도하세요.</p>";
    }
    unset($_SESSION['problem']);
    $item = $_POST['upgrade'];
    
    if ($item) {
    echo "<form method='post'><button type='submit' value='upgrade'>강화</button>";
    $upgradeResult = $this->upgradeItem($item);
    if ($upgradeResult === true) {
    $this->displayInventory();
    echo "<p>{$item}이(가) 업그레이드 되었습니다!</p>";
    } else {
    echo "<p>{$upgradeResult}</p>";
    }
    }
    $problem = $this->generateMathProblem();
    $_SESSION['problem'] = $problem;
    echo "<p>다음 문제를 푸세요: {$problem['problem']}</p><form method='post'><label for='answer'>답변:</label><input type='text' id='answer' name='answer'><button type='submit'>확인</button></form>";
    }
    }
    

    $game = new UpgradeGame();

    if (isset($_POST['answer']) || isset($_POST['upgrade'])) {
    $game->playGame();
    } else {
    $game->startGame();
    }

?>
