<?php

$file = "data.json";

function loadData($file){
    if(!file_exists($file)){
        file_put_contents($file , json_encode([], JSON_PRETTY_PRINT));
    }
    $json = file_get_contents($file);
    return json_decode($json, true) ?? [];
}

function saveData($file , $data){
    file_put_contents($file , json_encode($data , JSON_PRETTY_PRINT));
}

function getNextId($data){
    $ids = array_column($data , "id");
    return empty($ids) ? 1 : max($ids) + 1;
}

function printData($expense){
    echo "[{$expense['id']}] [{$expense['date']}] | [{$expense['description']}] | [{$expense['amount']}]\n";
}



if ($argc < 2) {
    echo "Usage: php expense-tracker.php [add|list|summary|delete] [options]\n";
    exit;
}

$command = $argv[1];
$data = loadData($file);
$now = date("Y-m-d"); 

switch ($command) {
    case 'add':
        if ($argc < 4){
            echo "Usage: php expense-tracker.php add \"Description\" Amount\n";
            exit;
        }

        $description = $argv[2];
        $amount = (float)$argv[3];

        if ($amount <= 0) {
            echo "Amount must be greater than 0.\n";
            exit;
        }

        $expense = [
            "id" => getNextId($data),
            "date" => $now,
            "description" => $description,
            "amount" => $amount
        ];

        $data[] = $expense;
        saveData($file , $data);

        echo "Expense added successfully (ID: {$expense['id']})\n";
        break;

    case 'list':
        if (empty($data)) {
            echo "No expenses found.\n";
        } else {
            foreach ($data as $expense) {
                printData($expense);
            }
        }
        break;

    case 'summary':
        $total = array_sum(array_column($data, "amount"));
        echo "Total expenses: $total\n";
        break;

    case 'delete':
        if ($argc < 3) {
            echo "Usage: php expense-tracker.php delete ID\n";
            exit;
        }
        $id = (int)$argv[2];
        $found = false;

        foreach ($data as $index => $expense) {
            if ($expense['id'] == $id) {
                unset($data[$index]);
                $data = array_values($data); // reindex
                saveData($file, $data);
                echo "Expense deleted successfully (ID: $id)\n";
                $found = true;
                break;
            }
        }

        if (!$found) {
            echo "Expense with ID $id not found.\n";
        }
        break;

    default:
        echo "Unknown command: $command\n";
        break;
}

?>
