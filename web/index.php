<?php
// Simple router
$request_uri = $_SERVER['REQUEST_URI'];

if ($request_uri == '/') {
    echo "Hello World"; // You can change this to a more useful API message if needed.
} elseif ($request_uri == '/recipes') {
    // Handle listing all recipes or other logic
    echo json_encode(getRecipes()); // Assuming a function like getRecipes() is defined below.
} elseif (preg_match('/^\/recipes\/(\d+)$/', $request_uri, $matches)) {
    // Handle fetching a single recipe by ID
    $id = $matches[1];
    echo json_encode(getRecipeById($id)); // Assuming a function like getRecipeById() is defined below.
} else {
    // If no route matched, return a 404 error
    http_response_code(404);
    echo json_encode(["message" => "Not Found"]);
}

function getRecipes() {
    // Assuming you have a DB connection and a query to fetch all recipes.
    // Return a list of recipes from the database.
    return [
        ["id" => 1, "name" => "Pasta", "prep_time" => 30, "difficulty" => 2, "vegetarian" => true],
        ["id" => 2, "name" => "Pizza", "prep_time" => 20, "difficulty" => 1, "vegetarian" => false]
    ];
}

function getRecipeById($id) {
    // Assuming you have a DB connection and a query to fetch a single recipe by ID.
    // Return a single recipe from the database based on the ID.
    return ["id" => $id, "name" => "Pasta", "prep_time" => 30, "difficulty" => 2, "vegetarian" => true];
}
