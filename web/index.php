<?php

$request_uri = $_SERVER['REQUEST_URI'];

if ($request_uri == '/') {
    echo "Hello World"; 
} elseif ($request_uri == '/recipes') {

    echo json_encode(getRecipes()); 
} elseif (preg_match('/^\/recipes\/(\d+)$/', $request_uri, $matches)) {

    $id = $matches[1];
    echo json_encode(getRecipeById($id)); 
} else {

    http_response_code(404);
    echo json_encode(["message" => "Not Found"]);
}

function getRecipes() {

    return [
        ["id" => 1, "name" => "Pasta", "prep_time" => 30, "difficulty" => 2, "vegetarian" => true],
        ["id" => 2, "name" => "Pizza", "prep_time" => 20, "difficulty" => 1, "vegetarian" => false]
    ];
}

function getRecipeById($id) {

    return ["id" => $id, "name" => "Pasta", "prep_time" => 30, "difficulty" => 2, "vegetarian" => true];
}