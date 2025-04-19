# E-Commerce Template

A complete, reusable, full-stack e-commerce platform template for organizations to customize and launch their own storefront.

## Tech Stack

- **Frontend**: HTML, Tailwind CSS, minimal custom CSS
- **Interactivity**: Vanilla JavaScript
- **Backend**: PHP (no frameworks)
- **Database**: MySQL

## Features

### Landing Page
- Marketing section for the organization and store
- Customizable via admin dashboard
- Highlights store categories (e.g., Groceries, Bags, Shoes)
- Call-to-action buttons: "View Products", "Shop Now"

### Product Page
- Search bar for filtering by name or keyword
- Category and subcategory filters
- Product listings with image, title, price
- "View Details" button that opens product in a modal
- Empty results message when no products match filters

### Cart System
- Uses PHP Sessions for cart management
- Cart icon opens a modal with:
  - Product name, quantity, price
  - Total sum
  - Two action buttons: "Make Inquiry" (WhatsApp) and "Place Order"

### Make Inquiry (via WhatsApp)
- Opens WhatsApp with pre-filled message containing cart items

### Place Order
- Opens a modal after cart
- User chooses:
  - Delivery or Pickup
  - Self or Someone Else
- Collects:
  - Full name, Phone number
  - Address (if delivery)
  - Extra note (optional)
- Sends detailed WhatsApp message with order details

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone the repository**
   ```
   git clone https://github.com/yourusername/ecommerce-template.git
   cd ecommerce-template
   ```

2. **Database Setup**
   - Create a MySQL database
   - Import database schema from `config/database.sql`
   ```
   mysql -u username -p your_database_name < config/database.sql
   ```

3. **Configuration**
   - Edit `config/db.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'your_database_name');
   ```

4. **Store Configuration**
   - Edit store settings in the `store_settings` table:
     - `store_name`: Your store name
     - `store_description`: Brief description of your store
     - `whatsapp_number`: Your WhatsApp number (without +)
     - `currency_symbol`: Your currency symbol

5. **Web Server Configuration**
   - Point your web server to the project root directory
   - Ensure proper permissions: `chmod -R 755 .`

## Folder Structure

```
/ecommerce-template/
├── /assets/               ← JS, CSS, images
├── /config/               ← db.php, store settings
├── /includes/             ← header, navbar, footer
├── /middleware/           ← auth checks
├── /utils/                ← cart, WhatsApp, validation
├── /pages/                ← landing.php, products.php
├── /modals/               ← product, cart, order modals
├── /admin/                ← dashboard, manage content
└── index.php
```

## Customization

### Modifying the Theme
- Edit the theme color in the `store_settings` table, value for `theme_color`
- The template uses Tailwind CSS for styling - customize in `assets/css/style.css`

### Adding Products
- Use the admin dashboard or directly add to the database in the `products` table

### Setting Up WhatsApp Integration
- Update your WhatsApp number in the `store_settings` table
- Customize message templates in the `whatsapp_message_template` setting

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

Created by [Your Name/Company]

## Support

For support, contact [your email address] 