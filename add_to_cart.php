<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'ecommerce';
$user = 'root';
$pass = ''; // replace with your MySQL root password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error); // Log the error
    die(json_encode(["error" => "Database connection failed."])); // Return JSON response
}

// Check if the product ID is sent via POST
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $session_id = session_id(); // Get the current session ID

    // Check if the product is already in the cart
    $checkCartQuery = "SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?";
    $stmtCheck = $conn->prepare($checkCartQuery);
    if ($stmtCheck) {
        $stmtCheck->bind_param("si", $session_id, $product_id);
        if ($stmtCheck->execute()) {
            $result = $stmtCheck->get_result();

            if ($result->num_rows > 0) {
                // If the product is already in the cart, update the quantity
                $cartItem = $result->fetch_assoc();
                $newQuantity = $cartItem['quantity'] + 1;

                $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ?";
                $stmtUpdate = $conn->prepare($updateQuery);
                if ($stmtUpdate) {
                    $stmtUpdate->bind_param("ii", $newQuantity, $cartItem['id']);
                    if (!$stmtUpdate->execute()) {
                        error_log("Error executing update statement: " . $stmtUpdate->error);
                        echo json_encode(["error" => "Could not update cart."]);
                    }
                    $stmtUpdate->close();
                } else {
                    error_log("Error preparing update statement: " . $conn->error);
                    echo json_encode(["error" => "Could not prepare update statement."]);
                }
            } else {
                // If the product is not in the cart, add it
                $insertQuery = "INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, 1)";
                $stmtInsert = $conn->prepare($insertQuery);
                if ($stmtInsert) {
                    $stmtInsert->bind_param("si", $session_id, $product_id);
                    if (!$stmtInsert->execute()) {
                        error_log("Error executing insert statement: " . $stmtInsert->error);
                        echo json_encode(["error" => "Could not add product to cart."]);
                    }
                    $stmtInsert->close();
                } else {
                    error_log("Error preparing insert statement: " . $conn->error);
                    echo json_encode(["error" => "Could not prepare insert statement."]);
                }
            }

            $stmtCheck->close();
        } else {
            error_log("Error executing check statement: " . $stmtCheck->error);
            echo json_encode(["error" => "Could not check cart."]);
        }
    } else {
        error_log("Error preparing check statement: " . $conn->error);
        echo json_encode(["error" => "Could not prepare check statement."]);
    }

    // Get updated cart count
    $countQuery = "SELECT SUM(quantity) as total_quantity FROM cart WHERE session_id = ?";
    $stmtCount = $conn->prepare($countQuery);
    if ($stmtCount) {
        $stmtCount->bind_param("s", $session_id);
        if ($stmtCount->execute()) {
            $result = $stmtCount->get_result();
            $row = $result->fetch_assoc();
            echo json_encode(["total_quantity" => $row['total_quantity']]); // Send back the updated cart count in JSON
            $stmtCount->close();
        } else {
            error_log("Error executing count statement: " . $stmtCount->error);
            echo json_encode(["error" => "Could not retrieve cart count."]);
        }
    } else {
        error_log("Error preparing count statement: " . $conn->error);
        echo json_encode(["error" => "Could not prepare count statement."]);
    }

} else {
    echo json_encode(["total_quantity" => 0]); // Return 0 in JSON format if product_id is not set
}



$conn->close();
