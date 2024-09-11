<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session to get the session ID
session_start();
$session_id = session_id(); // Use session ID instead of user ID

// Handle delete action before fetching the cart items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = $_POST['product_id'];

    // Prepare the DELETE statement
    $delete_sql = "DELETE FROM cart WHERE session_id = ? AND product_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("si", $session_id, $product_id);
    $stmt->execute();

    // Close the statement after deletion
    $stmt->close();

    // Redirect back to the cart page to refresh the items
    header("Location: cart.php");
    exit();
}

// Fetch cart items for the current session
$sql = "SELECT cart.quantity, products.id, products.name, products.description, products.price, products.image
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;

// Calculate the total price and store cart items
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_price += $row['price'] * $row['quantity'];
}

// Close connections
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* Your existing styles here */
        .payment-info {
            background: blue;
            padding: 10px;
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
        }

        .product-details {
            padding: 10px;
        }

        body {
            background: #eee;
        }

        .cart {
            background: #fff;
        }

        .p-about {
            font-size: 12px;
        }

        .table-shadow {
            -webkit-box-shadow: 5px 5px 15px -2px rgba(0, 0, 0, 0.42);
            box-shadow: 5px 5px 15px -2px rgba(0, 0, 0, 0.42);
        }

        .type {
            font-weight: 400;
            font-size: 10px;
        }

        label.radio {
            cursor: pointer;
        }

        label.radio input {
            position: absolute;
            top: 0;
            left: 0;
            visibility: hidden;
            pointer-events: none;
        }

        label.radio span {
            padding: 1px 12px;
            border: 2px solid #ada9a9;
            display: inline-block;
            color: #8f37aa;
            border-radius: 3px;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 300;
        }

        label.radio input:checked+span {
            border-color: #fff;
            background-color: blue;
            color: #fff;
        }

        .credit-inputs {
            background: rgb(102, 102, 221);
            color: #fff !important;
            border-color: rgb(102, 102, 221);
        }

        .credit-inputs::placeholder {
            color: #fff;
            font-size: 13px;
        }

        .credit-card-label {
            font-size: 9px;
            font-weight: 300;
        }

        .form-control.credit-inputs:focus {
            background: rgb(102, 102, 221);
            border: rgb(102, 102, 221);
        }

        .line {
            border-bottom: 1px solid rgb(102, 102, 221);
        }

        .information span {
            font-size: 12px;
            font-weight: 500;
        }

        .information {
            margin-bottom: 5px;
        }

        .items {
            -webkit-box-shadow: 5px 5px 4px -1px rgba(0, 0, 0, 0.25);
            box-shadow: 5px 5px 4px -1px rgba(0, 0, 0, 0.08);
        }

        .spec {
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="container mt-5 p-3 rounded cart">
        <div class="row no-gutters">
            <div class="col-md-8">
                <div class="product-details mr-2">
                    <div class="d-flex flex-row align-items-center"><i class="fa fa-long-arrow-left"></i><a
                            href="index.php" class="ml-2">Continue Shopping</a></div>
                    <hr>
                    <h6 class="mb-0">Shopping cart</h6>
                    <div class="d-flex justify-content-between"><span>You have <?php echo count($cart_items); ?> items
                            in your cart</span>
                        <div class="d-flex flex-row align-items-center"><span class="text-black-50">Sort by:</span>
                            <div class="price ml-2"><span class="mr-1">price</span><i class="fa fa-angle-down"></i>
                            </div>
                        </div>
                    </div>
                    <?php foreach ($cart_items as $item) { ?>
                        <div class="d-flex justify-content-between align-items-center mt-3 p-2 items rounded">
                            <div class="d-flex flex-row">
                                <img class="rounded" src="<?php echo htmlspecialchars($item['image']); ?>" width="40">
                                <div class="ml-2">
                                    <span
                                        class="font-weight-bold d-block"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="spec"><?php echo htmlspecialchars($item['description']); ?></span>
                                </div>
                            </div>
                            <div class="d-flex flex-row align-items-center">
                                <span class="d-block"><?php echo htmlspecialchars($item['quantity']); ?></span>
                                <span
                                    class="d-block ml-5 font-weight-bold">$<?php echo number_format($item['price'], 2); ?></span>
                                <form method="POST" action="cart.php" class="ml-3">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-link text-black-50 p-0"><i
                                            class="fa fa-trash-o"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="payment-info">
                    <div class="d-flex justify-content-between align-items-center"><span>Card details</span><img
                            class="rounded" src="https://i.imgur.com/WU501C8.jpg" width="30"></div><span
                        class="type d-block mt-3 mb-1">Card type</span>
                    <label class="radio"> <input type="radio" name="card" value="payment" checked> <span><img width="30"
                                src="https://img.icons8.com/color/48/000000/mastercard.png" /></span> </label>
                    <label class="radio"> <input type="radio" name="card" value="payment"> <span><img width="30"
                                src="https://img.icons8.com/officel/48/000000/visa.png" /></span> </label>
                    <label class="radio"> <input type="radio" name="card" value="payment"> <span><img width="30"
                                src="https://img.icons8.com/ultraviolet/48/000000/amex.png" /></span> </label>
                    <label class="radio"> <input type="radio" name="card" value="payment"> <span><img width="30"
                                src="https://img.icons8.com/officel/48/000000/paypal.png" /></span> </label>
                    <div><label class="credit-card-label">Name on card</label><input type="text"
                            class="form-control credit-inputs" placeholder="Name"></div>
                    <div><label class="credit-card-label">Card number</label><input type="text"
                            class="form-control credit-inputs" placeholder="0000 0000 0000 0000"></div>
                    <div class="row">
                        <div class="col-md-6"><label class="credit-card-label">Date</label><input type="text"
                                class="form-control credit-inputs" placeholder="12/24"></div>
                        <div class="col-md-6"><label class="credit-card-label">CVV</label><input type="text"
                                class="form-control credit-inputs" placeholder="342"></div>
                    </div>
                    <hr class="line">
                    <div class="d-flex justify-content-between information">
                        <span>Subtotal</span><span>$<?php echo number_format($total_price, 2); ?></span></div>
                    <div class="d-flex justify-content-between information"><span>Shipping</span><span>$20.00</span>
                    </div>
                    <div class="d-flex justify-content-between information"><span>Total(Incl.
                            taxes)</span><span>$<?php echo number_format($total_price + 20, 2); ?></span></div><button
                        class="btn btn-primary btn-block d-flex justify-content-between mt-3"
                        type="button"><span>$<?php echo number_format($total_price + 20, 2); ?></span><span>Checkout<i
                                class="fa fa-long-arrow-right ml-1"></i></span></button>
                </div>
            </div>
        </div>
    </div>
    <!-- External JS links -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>

</html>