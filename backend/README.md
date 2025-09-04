# Portfolio Admin Panel

A comprehensive PHP/MySQL admin panel for managing your portfolio content including projects and education records.

## Features

- **Dashboard**: Overview with statistics and recent activities
- **Project Management**: Add, edit, delete, and manage project portfolio items
- **Education Management**: Manage educational background and qualifications
- **Image Upload**: Handle project images with automatic file management
- **Activity Logging**: Track all admin actions for audit purposes
- **Responsive Design**: Mobile-friendly admin interface
- **Secure Authentication**: Simple login system with session management

## Setup Instructions

### 1. Database Setup

1. Create a MySQL database named `portfolio_db`
2. Run the setup script to create tables:
   ```bash
   php backend/setup.php
   ```

### 2. Configuration

1. Edit `backend/config/database.php` to match your database credentials:
   ```php
   $host = 'localhost';
   $dbname = 'portfolio_db';
   $username = 'your_username';
   $password = 'your_password';
   ```

2. Change the default admin credentials:
   ```php
   define('ADMIN_USERNAME', 'your_username');
   define('ADMIN_PASSWORD', 'your_secure_password');
   ```

### 3. File Permissions

Ensure the uploads directory is writable:
```bash
chmod 755 backend/uploads/
chmod 755 backend/uploads/projects/
```

### 4. Access the Admin Panel

1. Navigate to: `http://your-domain/backend/admin/`
2. Login with your admin credentials
3. Start managing your portfolio content!

## File Structure

```
backend/
├── admin/                  # Admin panel interface
│   ├── css/
│   │   └── admin.css      # Admin panel styling
│   ├── includes/
│   │   ├── header.php     # Admin header component
│   │   └── sidebar.php    # Admin sidebar navigation
│   ├── dashboard.php      # Main admin dashboard
│   ├── projects.php       # Project management
│   ├── education.php      # Education management
│   ├── login.php          # Admin login page
│   └── logout.php         # Logout handler
├── api/                   # API endpoints
│   ├── projects.php       # Projects API
│   └── education.php      # Education API
├── config/
│   └── database.php       # Database configuration
├── uploads/
│   └── projects/          # Project images storage
└── setup.php             # Database setup script
```

## Database Schema

### Projects Table
- `id` - Primary key
- `title` - Project title
- `description` - Project description
- `technologies` - Technologies used
- `image_path` - Path to project image
- `project_url` - Live project URL
- `github_url` - GitHub repository URL
- `is_featured` - Featured project flag
- `status` - Project status (completed, in_progress, planned)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### Education Table
- `id` - Primary key
- `institution` - Educational institution
- `degree` - Degree/qualification
- `field_of_study` - Field of study
- `start_date` - Start date
- `end_date` - End date
- `grade` - Grade/GPA
- `description` - Additional details
- `is_current` - Currently studying flag
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### Admin Logs Table
- `id` - Primary key
- `action` - Action performed
- `details` - Additional details
- `ip_address` - User IP address
- `created_at` - Action timestamp

## API Endpoints

### Get Projects
```
GET /backend/api/projects.php
```
Returns all active projects in JSON format.

### Get Education
```
GET /backend/api/education.php
```
Returns all education records in JSON format.

## Security Notes

1. **Change Default Credentials**: Always change the default admin username and password
2. **Use HTTPS**: Deploy with SSL certificate in production
3. **File Upload Security**: The system validates file types and sizes for uploads
4. **Session Security**: Sessions are properly managed with secure settings
5. **Input Validation**: All forms include proper validation and sanitization

## Frontend Integration

The admin panel is designed to work with your existing portfolio. Use the API endpoints to fetch dynamic content:

```javascript
// Fetch projects
fetch('/backend/api/projects.php')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Display projects
      console.log(data.data);
    }
  });

// Fetch education
fetch('/backend/api/education.php')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Display education
      console.log(data.data);
    }
  });
```

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **File Upload Issues**
   - Check directory permissions (755)
   - Verify upload directory exists
   - Check PHP file upload settings

3. **Login Issues**
   - Verify admin credentials in `config/database.php`
   - Check session configuration
   - Clear browser cookies/cache

### Error Logs

Check PHP error logs for detailed error information:
```bash
tail -f /var/log/php/error.log
```

## License

This admin panel is part of your portfolio project. Modify and use as needed for your requirements.
