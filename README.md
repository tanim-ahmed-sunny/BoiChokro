# Boichokro - Community Book Sharing Platform

## Installation Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser

### Setup Steps

1. **Database Setup**
   - Create a MySQL database
   - Import the schema file:
     ```bash
     mysql -u root -p < database/schema.sql
     ```
   - Update database credentials in `config/database.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'boichokro');
     ```

2. **File Permissions**
   - Create uploads directory:
     ```bash
     mkdir uploads
     chmod 777 uploads
     ```

3. **Configuration**
   - Update `config/config.php` with your settings:
     - Base URL
     - Email settings (for OTP)
     - Payment gateway credentials (if using)

4. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure mod_rewrite is enabled (for Apache)
   - PHP should have file upload enabled

5. **Default Admin Account**
   - Username: `admin`
   - Password: `admin123`
   - **Important**: Change this password after first login!

### Features Implemented

#### Core Features (Normal Requirements)
- ✅ User, Library, and Admin management with role-based access control
- ✅ Secure password hashing
- ✅ Email OTP verification
- ✅ Book listings (sale, swap, donation)
- ✅ Admin approval system for listings
- ✅ Library registration and management
- ✅ Transaction system
- ✅ Communication & notifications

#### Expected Features
- ✅ Wishlist with automated alerts
- ✅ Transaction history
- ✅ Review and rating system (community posts)
- ✅ Library booking system
- ✅ Community discussions
- ✅ Chat module
- ✅ Report inappropriate content

#### Exciting Features
- ✅ Environmental impact dashboard
- ✅ Track reused and donated books
- ✅ Calculate paper and trees saved
- ✅ Visual dashboards showing contribution impact
- ✅ Geo-location search (by area)

### File Structure

```
boichokro/
├── api/              # PHP API endpoints
│   ├── auth.php
│   ├── books.php
│   ├── libraries.php
│   ├── transactions.php
│   ├── wishlist.php
│   ├── community.php
│   ├── chat.php
│   ├── notifications.php
│   └── environmental.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── api.js
│   │   ├── main.js
│   │   ├── books.js
│   │   ├── dashboard.js
│   │   └── community.js
│   └── images/
├── config/
│   ├── config.php
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   └── auth.php
├── uploads/           # User uploaded files
├── index.html
├── login.html
├── register.html
├── dashboard.html
├── books.html
├── add-book.html
├── community.html
└── README.md
```

### Usage

1. **User Registration**
   - Visit `register.html`
   - Fill in details
   - Verify email with OTP

2. **Book Listing**
   - Login and go to Dashboard
   - Click "Add New Book"
   - Fill in book details
   - Wait for admin approval

3. **Browse Books**
   - Visit `books.html`
   - Use filters to find books
   - Click on book to view details

4. **Community**
   - Visit `community.html`
   - Create posts, reviews, recommendations
   - Interact with other users

5. **Libraries**
   - Libraries can register and add books
   - Users can request library books
   - Libraries manage bookings

### API Endpoints

All APIs return JSON responses:

- `api/auth.php` - Authentication (login, register, OTP)
- `api/books.php` - Book management
- `api/libraries.php` - Library operations
- `api/transactions.php` - Transaction handling
- `api/wishlist.php` - Wishlist management
- `api/community.php` - Community posts
- `api/chat.php` - Messaging
- `api/notifications.php` - Notifications
- `api/environmental.php` - Environmental impact stats

### Security Notes

- Passwords are hashed using PHP's `password_hash()`
- Sessions are used for authentication
- SQL injection protection via prepared statements
- File upload validation
- Role-based access control

### Future Enhancements

- Real-time chat using WebSockets
- Payment gateway integration
- Email service integration
- Advanced search with filters
- Mobile app version
- Social media integration

### Support

For issues or questions, please check the code comments or contact the development team.
