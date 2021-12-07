<?php 

$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER["REQUEST_URI"]);
$path = $parsed["path"];

$routes = [
    "GET" => [
        "/" => "homeHandler",
        "/termekek" => "productListHandler",
        
    ],
    "POST" => [
        "/termekek" => "createProductHandler",
        "/delete-product" => "deleteProductHandler",
        "/update-product" => "updateProductHandler",
        
    ],
];

$handlerFunction = $routes[$method][$path] ?? "notFountHandler";

$safeHandlerFunction = function_exists($handlerFunction) ? $handlerFunction : "notFoundHandler";

$safeHandlerFunction();

function compileTemplate($filePath, $params = []): string {

    ob_start();
    require $filePath;
    return ob_get_clean();

}



function homeHandler() {

    $homeTemplate = compileTemplate('./views/home.php');

    echo compileTemplate('./views/wrapper.php', [
        'innerTemplate' => $homeTemplate,
        'activeLink' => '/',
    ]);
    

};

function productListHandler() {

    
    $contents = file_get_contents("./products.json");
    $products = json_decode($contents, true);
    $isSuccess = isset($_GET["siker"]);

    $productsListTemplate = compileTemplate("./views/product-list.php", [
        "products" => $products,
        "isSuccess" => $isSuccess,
        "editedProductId" => $_GET["szerkesztes"] ?? ""
       ]);

    echo compileTemplate('./views/wrapper.php', [
        'innerTemplate' => $productsListTemplate,
        'activeLink' => '/termekek',
    ]);
    

};




function createProductHandler() {

    

    $newProduct = [
        "id" => uniqid(),
        "name" => $_POST["name"],
        "price" => (int)$_POST["price"],
        "quantity" => (int)$_POST["quantity"],
        "discount" => (float)$_POST["discount"],
        "description" => $_POST["description"]
        
    ];

    $contents = file_get_contents("./products.json");
    $products = json_decode($contents, true);

    array_push($products, $newProduct);

    $json = json_encode($products);
    file_put_contents("./products.json", $json);

    header("Location: /termekek?siker=1");

};

function notFountHandler() {

    echo "Oldal nem található!";

};

function deleteProductHandler() {
    $deleteProductId = $_GET["id"] ?? "";
    $products = json_decode(file_get_contents("./products.json"), true);

    $foundProductsIndex = -1;

    foreach($products as $index => $product) {
        if($product["id"] === $deleteProductId) {
            $foundProductIndex = $index;
            break;
        }
    }

    if($foundProductIndex === -1) {
        header("Location: /termekek");
        return;
    }

    array_splice($products, $foundProductIndex, 1);

    file_put_contents("./products.json", json_encode($products));
    header("Location: /termekek");


};

function updateProductHandler(){
    $updatedProductId = $_GET["id"] ?? "";
    $products = json_decode(file_get_contents("./products.json"), true);

    $foundProductsIndex = -1;

    foreach($products as $index => $product) {
        if($product["id"] === $updatedProductId) {
            $foundProductIndex = $index;
            break;
        }
    }

    if($foundProductIndex === -1) {
        header("Location: /termekek");
        return;
    }

    $updateProduct = [
        "id" => $updatedProductId,
        "name" => $_POST["name"],
        "price" => (int)$_POST["price"],
        "quantity" => (int)$_POST["quantity"],
        "discount" => (float)$_POST["discount"],
        "description" => $_POST["description"],
    ];

    $products[$foundProductIndex] = $updateProduct;

    file_put_contents("./products.json", json_encode($products));
    header("Location: /termekek");

};