<?php

// Database connection
$host = 'localhost';
$db = 'ecommerce';
$user = 'root';
$pass = ''; // replace with your MySQL root password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Initialize $searchQuery
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Default page number
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Number of products per page
$products_per_page = 12;

// Initialize total products count
$total_products = 0; // You should set this by counting total records

// Fetch total products from the database
$total_products_sql = "SELECT COUNT(*) as total FROM products";
if ($searchQuery) {
    $total_products_sql .= " WHERE name LIKE ? OR description LIKE ? OR category_name LIKE ?";
}
$stmt_total = $conn->prepare($total_products_sql);
if ($searchQuery) {
    $searchQueryParam = '%' . $searchQuery . '%';
    $stmt_total->bind_param("sss", $searchQueryParam, $searchQueryParam, $searchQueryParam);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_row = $result_total->fetch_assoc();
$total_products = $total_row['total'];
$stmt_total->close();

// Calculate total pages
$total_pages = ceil($total_products / $products_per_page);

// Ensure the current page is within range
$page = max(1, min($page, $total_pages));

// Calculate offset for SQL query
$offset = ($page - 1) * $products_per_page;


// Initialize SQL query for products with pagination
$products_sql = "SELECT * FROM products";
$params = [];
$types = "";

// Apply search filter if needed
if ($searchQuery) {
    $searchQuery = '%' . $searchQuery . '%';
    $products_sql .= " WHERE name LIKE ? OR description LIKE ? OR category_name LIKE ?";
    $params = [$searchQuery, $searchQuery, $searchQuery];
    $types = "sss";
}

// Add pagination
$products_sql .= " LIMIT ? OFFSET ?";
$params[] = $products_per_page;
$params[] = $offset;
$types .= "ii";

// Fetch products
$stmt_products = $conn->prepare($products_sql);
if ($types) {
    $stmt_products->bind_param($types, ...$params);
}
$stmt_products->execute();
$result = $stmt_products->get_result();

// Initialize products array
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt_products->close();



// Fetch categories and their products
$sql_categories = "SELECT categories.id as cat_id, categories.name as cat_name, products.id as prod_id, products.name as prod_name 
                    FROM categories 
                    LEFT JOIN products ON categories.id = products.category_id";
$result = $conn->query($sql_categories);

// Initialize categories array
$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[$row['cat_id']]['name'] = $row['cat_name'];
        if ($row['prod_id']) {
            $categories[$row['cat_id']]['products'][] = ['id' => $row['prod_id'], 'name' => $row['prod_name']];
        }
    }
}

// Handle AJAX search request
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Initialize SQL query for products
    $products_sql = "SELECT * FROM products";
    $params = [];
    $types = "";

    // Apply search filter if needed
    if ($searchQuery) {
        $searchQuery = '%' . $searchQuery . '%';
        $products_sql .= " WHERE name LIKE ? OR description LIKE ? OR category_name LIKE ?";
        $params = [$searchQuery, $searchQuery, $searchQuery];
        $types = "sss";
    }

    // Fetch products without limit and offset
    $stmt_products = $conn->prepare($products_sql);
    if ($types) {
        $stmt_products->bind_param($types, ...$params);
    }
    $stmt_products->execute();
    $result = $stmt_products->get_result();

    // Initialize products array
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt_products->close();

    // Return products as JSON
    header('Content-Type: application/json');
    echo json_encode($products);
    exit();
}


// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ksmEkRzOHAEGFsk13fA4KaDzf6b6mxrhCD3w5xqYd67/hvG1R34tHhCR6bdSlcG3" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles.css">
    <title>Innovatech</title>

</head>

<body>
    <header class="top-header">
        <div class="container">
            <div class="user-actions">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
                <!-- User Icon -->
                <a href="profile.php" class="user-icon">
                    <i class="fas fa-user"></i>
                </a>
                <!-- Cart Icon -->
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count"><?php echo htmlspecialchars(count($_SESSION['cart'] ?? [])); ?></span>
                </a>
                <!-- Search Bar -->
                <div class="search-bar" id="search-bar" style="display: none;">
                    <input type="text" id="search-input" placeholder="Search categories or products..."
                        aria-label="Search">
                    <div id="suggestions" class="suggestions" role="listbox"></div>
                </div>

            </div>
        </div>
    </header>

    <header class="main-header">
        <div class="container">
            <!-- Button to open offcanvas sidebar -->
            <a class="nav-link active" href="#" data-bs-toggle="offcanvas" data-bs-target="#demo">
                <img src="ico/menu1.png" alt="Menu Icon" style="width: 3rem;">
            </a>
            <div class="innovatech">
                <div class="box">I</div>
                <div class="box">N</div>
                <div class="box">N</div>
                <div class="box">O</div>
                <div class="box">V</div>
                <div class="box">A</div>
                <div class="box">T</div>
                <div class="box">E</div>
                <div class="box">C</div>
                <div class="box">H</div>
            </div>
        </div>
    </header>
    <header class="second-header">
        <div class="container">
            <nav>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#"></a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div id="heroCarousel" class="carousel slide"  data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="img/ad5.jpg" alt="Carousel Item 1">
            </div>
            <div class="carousel-item">
                <img src="img/ad6.jpg" alt="Carousel Item 2">
            </div>
            <div class="carousel-item">
                <img src="img/ad8.jpg" alt="Carousel Item 3">
            </div>
            <div class="carousel-item">
                <img src="img/ad9.jpg" alt="Carousel Item 4">
            </div>
            <div class="carousel-item">
                <img src="img/ad4.jpg" alt="Carousel Item 5">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Sidebar menu -->
    <div class="offcanvas offcanvas-start text-bg-dark" id="demo">
        <div class="offcanvas-header">
            <h1 class="offcanvas-title">Categories</h1>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <?php if (!empty($categories)): ?>
                <ul class="nav flex-column">
                    <?php foreach ($categories as $categoryId => $category): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#"
                                id="navbarDropdown<?php echo htmlspecialchars($categoryId); ?>" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                            <?php if (!empty($category['products'])): ?>
                                <ul class="dropdown-menu dropdown-menu-dark"
                                    aria-labelledby="navbarDropdown<?php echo htmlspecialchars($categoryId); ?>">
                                    <?php foreach ($category['products'] as $product): ?>
                                        <li>
                                            <a class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#productModal<?php echo htmlspecialchars($product['id']); ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-light">No categories available.</p>
            <?php endif; ?>
        </div>
    </div>

   <!-- Products Section -->
<section class="products-section">
    <div class="container">
        <h2>Our Products</h2>
        <div class="product-grid" id="products-container">
            <?php foreach ($products as $product): ?>
                <div class="product" data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                    <img src="https://via.placeholder.com/150" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['category_name']); ?></p>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p>$<?php echo htmlspecialchars($product['price']); ?></p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#productModal<?php echo htmlspecialchars($product['id']); ?>">
                        View Details
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- All Modals Section -->
<section class="all-modals">
    <?php foreach ($products as $product): ?>
        <!-- Product Modal -->
        <div class="modal fade" id="productModal<?php echo htmlspecialchars($product['id']); ?>" tabindex="-1"
            aria-labelledby="productModalLabel<?php echo htmlspecialchars($product['id']); ?>"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel<?php echo htmlspecialchars($product['id']); ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="https://via.placeholder.com/150" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

    <?php if (!$searchQuery): ?>
        <div class="pagination-container">
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page)
                            echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
                        
    <div class="custom-divider"></div>
    <section id="contact" class="container">
    <div class="contact-wrapper">
        <h2 style="text-align: center; margin: 0 auto;">Contact Us</h2>
        <form action="contact_form_handler.php" method="post" id="contactForm">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="4" required></textarea>

            <button type="submit">Submit</button>
        </form>
    </div>
    </section>
    
    <hr>       
        <footer>
            <div class="container">
                <p>&copy; <?php echo date("Y"); ?> Innovatech</p>
            </div>
        </footer>

        <script>
            // Waits for the DOM to fully load before attaching event listeners and manipulating elements
            document.addEventListener('DOMContentLoaded', function () {
                const searchBar = document.getElementById('search-bar');
                const searchInput = document.getElementById('search-input');
                const suggestionsContainer = document.getElementById('suggestions');
                const productsContainer = document.getElementById('products-container');
                const loadingIndicator = document.createElement('div');

                // Set up loading indicator
                loadingIndicator.className = 'loading-indicator';
                loadingIndicator.innerText = 'Loading...';
                searchBar.appendChild(loadingIndicator);
                loadingIndicator.style.display = 'none'; // Initially hidden

                let debounceTimer;  // Timer ID for debouncing search input

                // Toggle the visibility of the search bar when the search icon is clicked
                document.querySelector('.search-icon').addEventListener('click', () => {
                    searchBar.style.display = searchBar.style.display === 'block' ? 'none' : 'block';
                });

                // Hide the search bar when clicking outside of it
                document.addEventListener('click', (event) => {
                    if (!searchBar.contains(event.target) && !event.target.closest('.search-icon')) {
                        searchBar.style.display = 'none'; // Close search bar
                    }
                });

                // Handle user input in the search field and fetch suggestions/products
                searchInput.addEventListener('input', function () {
                    const query = this.value.trim(); // Get trimmed user query
                    if (query.length > 0) {
                        clearTimeout(debounceTimer); // Clear previous timer to prevent multiple calls
                        debounceTimer = setTimeout(() => {
                            fetchSuggestions(query); // Fetch search suggestions based on user input
                            fetchProducts(query); // Fetch products based on user input
                        }, 300); // 300ms debounce time
                    } else {
                        suggestionsContainer.innerHTML = ''; // Clear suggestions if input is empty
                        productsContainer.innerHTML = ''; // Clear products if input is empty
                    }
                });

                // Fetches search suggestions from the server
                function fetchSuggestions(query) {
                    fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(suggestions => {
                            displaySuggestions(suggestions); // Display fetched suggestions
                        })
                        .catch(error => {
                            console.error('Error fetching suggestions:', error); // Log error to console
                            suggestionsContainer.innerHTML = '<p>Error loading suggestions.</p>'; // Show error message
                        });
                }

                // Displays the suggestions to the user
                function displaySuggestions(suggestions) {
                    suggestionsContainer.innerHTML = ''; // Clear existing suggestions

                    if (suggestions.length > 0) {
                        suggestions.forEach(suggestion => {
                            const suggestionElement = document.createElement('div'); // Create div for suggestion
                            suggestionElement.classList.add('suggestion-item'); // Add suggestion item class
                            suggestionElement.innerText = suggestion; // Set suggestion text
                            suggestionsContainer.appendChild(suggestionElement); // Add suggestion element to container

                            // Add click event to each suggestion to allow selection
                            suggestionElement.addEventListener('click', function () {
                                searchInput.value = this.textContent; // Set the input to the clicked suggestion
                                suggestionsContainer.innerHTML = ''; // Clear suggestions after selection
                                fetchProducts(searchInput.value.trim()); // Fetch products based on selected suggestion
                            });
                        });
                    } else {
                        suggestionsContainer.innerHTML = '<p>No suggestions found.</p>'; // Notify if no suggestions
                    }
                }

                // Fetches products based on the search query from the server
                function fetchProducts(query) {
                    loadingIndicator.style.display = 'block'; // Show loading indicator

                    fetch(`?ajax=true&search=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(products => {
                            displayProducts(products); // Display fetched products
                            loadingIndicator.style.display = 'none'; // Hide loading indicator
                        })
                        .catch(error => {
                            console.error('Error fetching products:', error); // Log error to console
                            productsContainer.innerHTML = '<p>Something went wrong. Please try again.</p>'; // Show error message
                            loadingIndicator.style.display = 'none'; // Hide loading indicator
                        });
                }

                // Displays the fetched products
                function displayProducts(products) {
                    productsContainer.innerHTML = ''; // Clear existing products

                    if (products.length > 0) {
                        products.forEach(product => {
                            const productElement = document.createElement('div'); // Create a product element
                            productElement.classList.add('product'); // Add product class
                            productElement.setAttribute('data-product-name', product.name); // Set data attribute for product name
                            productElement.innerHTML = `
                        <img src="https://via.placeholder.com/150" alt="${product.name}">
                <h3>${product.name}</h3>
                <p>${product.category_name}</p>
                <p>${product.description}</p>
                <p>$${product.price}</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal${product.id}">
                    View Details
                </button>
                <div class="modal fade" id="productModal${product.id}" tabindex="-1" aria-labelledby="productModalLabel${product.id}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="productModalLabel${product.id}">
                                    ${product.name}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <img src="https://via.placeholder.com/150" alt="${product.name}">
                                <p>${product.description}</p>
                                <p>Price: $${product.price}</p>
                                <p>Category: ${product.category_name}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="addToCart(${product.id})">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
                    `;
                            productsContainer.appendChild(productElement); // Append product element to the products container
                        });

                        // Initialize modals for newly added products
                        document.querySelectorAll('.modal').forEach(modal => {
                            new bootstrap.Modal(modal);
                        });

                    } else {
                        productsContainer.innerHTML = '<p>No products found.</p>'; // Notify if no products found
                    }
                }

                // Refresh the page when the Home link is clicked
                document.querySelector('nav a[href="#"]').addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent default link action
                    window.location.href = window.location.pathname; // Redirect to the base URL of the page
                });
            });

            // add products in cart
            function addToCart(productId) {
                // Create an AJAX request to add the product to the cart
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'add_to_cart.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            // Parse the JSON response
                            var response = JSON.parse(xhr.responseText);

                            if (response.total_quantity !== undefined) {
                                // Update the cart count on the page
                                var cartCountElement = document.getElementById('cart-count');
                                if (cartCountElement) {
                                    cartCountElement.textContent = response.total_quantity;
                                }

                                // Close the modal window
                                var modalElement = document.getElementById('productModal' + productId);
                                var modalInstance = bootstrap.Modal.getInstance(modalElement);
                                if (!modalInstance) {
                                    modalInstance = new bootstrap.Modal(modalElement);
                                }
                                modalInstance.hide();
                            } else if (response.error) {
                                // Handle error messages
                                alert(response.error);
                            }
                        } else {
                            // Handle HTTP errors
                            alert('An error occurred while adding the product to the cart.');
                        }
                    }
                };

                xhr.send('product_id=' + encodeURIComponent(productId));
            }
           
        </script>

</body>

</html>