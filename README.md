# GitHub-Style Authentication System

A complete authentication system that mimics GitHub's sign-in/sign-up interface.

## Setup Instructions

### Prerequisites
- Web server with PHP (Apache, Nginx, etc.)
- MySQL database
- Modern web browser

### Installation Steps

1. **Create the database:**
   - Open your MySQL administration tool (phpMyAdmin, MySQL Workbench, or command line)
   - Run the SQL commands from `database.sql` to create the database and table

2. **Configure the database connection:**
   - Open `auth.php` in a text editor
   - Update the database credentials in the configuration section:
     ```php
     $host = 'localhost';
     $dbname = 'github_auth';
     $username = 'root';
     $password = 'your_password';
     ```

3. **Upload files to your server:**
   - Place all files in your web server's document root directory
   - Ensure the web server has write permissions to the directory

4. **Access the application:**
   - Open your web browser and navigate to the URL where you uploaded the files
   - The application should load with the GitHub-style authentication interface

### Features
- GitHub-style UI with dark theme
- Responsive design
- Form validation
- Toggle between sign in and sign up
- Random username and password generation
- Backend authentication with secure password hashing
- SQL injection prevention with prepared statements
- CORS headers for API access

### Security Notes
- For production use, implement HTTPS
- Add CSRF protection
- Implement rate limiting
- Use environment variables for database credentials
- Regularly update dependencies