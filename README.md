# Innovatech eCommerce Site

## Overview

The Innovatech eCommerce site is a web application designed for managing and displaying products. It features a product catalog with search and pagination functionalities, and integrates a shopping cart for user convenience. This README provides an overview of the project, setup instructions, and usage details.

## Features

- **Product Catalog**: Displays a list of products with images, names, descriptions, and prices.
- **Categories**: Displays product categories in a sidebar with dropdowns for each category's products.
- **Search Functionality**: Allows users to search for products by name, description, or category.
- **Modals**: Provides detailed product information in modal windows.
- **AJAX Search**: Enables dynamic product search and suggestions without reloading the page.
- **Cart Management**: Allows users to add products to their cart with live updates.
- **Pagination**: Supports pagination for browsing through products.

## Technologies Used

- **Backend**: PHP for server-side scripting and MySQL for database management.
- **Frontend**: HTML, CSS, and JavaScript. Uses Bootstrap for responsive design and modals.
- **AJAX**: For asynchronous search and cart updates.


### Installation

1. **Clone the repository:**

   ```bash
   git clone <repository-url>
   cd <repository-directory>

## Setup

### Configure the Database

1. **Create a MySQL database:**
   - Name the database `ecommerce`.

2. **Import the database schema:**
   - Import the provided SQL file into your newly created database.

### Update Database Credentials

1. **Edit the PHP configuration file:**
   - Locate the PHP file that contains the database connection code.
   - Update the following variables with your database credentials:
     - `$host` - Database host
     - `$user` - Database username
     - `$pass` - Database password
     - `$db` - Database name

### Set Up the Web Server

1. **Place the code:**
   - Move the project files into your web server's root directory.

2. **Configure the web server:**
   - Ensure the web server is properly configured to serve PHP files.

### Install Dependencies

1. **Check internet access:**
   - Ensure you have access to the internet to fetch Bootstrap and FontAwesome from their CDN.

### Access the Site

1. **Open your web browser:**
   - Navigate to your local server's address (e.g., `http://localhost`).

## Usage

- **Search Products:** Use the search bar to find products by name, description, or category.
- **View Products:** Click on "View Details" to see more information about a product.
- **Add to Cart:** Click the "Add to Cart" button in the product modal to add items to your cart.
- **Pagination:** Use the pagination controls to navigate through multiple pages of products.

## Contribution

If you'd like to contribute to this project, please fork the repository and submit a pull request with your changes. Ensure your code adheres to the project's coding standards and includes appropriate tests.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any questions or feedback, please contact [dule.stankovski@gmail.com](mailto:your-email@example.com).   
