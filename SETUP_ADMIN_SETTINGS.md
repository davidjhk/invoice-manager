# Admin Settings Setup Guide

## Current Status
The admin settings system has been implemented but requires database table creation to function properly.

## Quick Fix Options

### Option 1: Automated Script (Recommended)
Execute the automated setup script:
```bash
cd /opt/bitnami/apps/jdosa/invoice-manager
./create_admin_settings_table.sh
```

### Option 2: Manual Database Setup
Run the following SQL commands in your MySQL database:

```sql
-- Connect to database
mysql -u bn_wordpress -pd6ab501583 -D bitnami_wordpress

-- Create admin settings table
CREATE TABLE IF NOT EXISTS `jdosa_admin_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text,
    `description` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT IGNORE INTO `jdosa_admin_settings` (`setting_key`, `setting_value`, `description`) VALUES
('allow_signup', '0', 'Allow new user registration (1 = enabled, 0 = disabled)'),
('max_users', '100', 'Maximum number of users allowed'),
('site_maintenance', '0', 'Site maintenance mode (1 = enabled, 0 = disabled)'),
('password_min_length', '6', 'Minimum password length requirement'),
('session_timeout', '3600', 'Session timeout in seconds'),
('email_notifications', '1', 'Enable email notifications'),
('backup_enabled', '1', 'Enable automatic backups'),
('max_companies_per_user', '5', 'Maximum companies per user');
```

### Option 3: Yii2 Migration
Run the migration system:
```bash
cd /opt/bitnami/apps/jdosa/invoice-manager
./yii migrate --interactive=0
```

## What This Setup Provides

### 1. Admin-Only User Creation
- Public user registration is disabled by default
- Only administrators can create new accounts
- Access via: `https://invoice.jdosa.com/admin/create-user`

### 2. Admin Settings Management
- Configure system-wide settings
- Access via: `https://invoice.jdosa.com/admin/settings`
- Toggle user registration on/off
- Set system limits and security options

### 3. Enhanced Security
- Signup page is hidden from public access
- Login page no longer shows "Create Account" button
- Admin-controlled user provisioning

## Admin Panel Features

### Dashboard
- User statistics and overview
- Quick access to user management
- System status indicators

### User Management
- View all users
- Create new users
- Edit user details
- Toggle user active/inactive status
- Reset user passwords
- Delete users (with protection for self-deletion)

### System Settings
- **Allow User Registration**: Enable/disable public signup
- **Maximum Users**: Set user limit
- **Maintenance Mode**: Put site in maintenance mode
- **Password Requirements**: Set minimum password length
- **Session Timeout**: Configure session duration
- **Email Notifications**: Enable/disable email features
- **Backup Settings**: Configure automatic backups
- **Company Limits**: Set max companies per user

## Security Considerations

1. **Default Settings**: Registration is disabled by default
2. **Admin Access**: Only admin users can access admin panel
3. **Self-Protection**: Admins cannot delete or deactivate themselves
4. **Session Security**: Configurable session timeout
5. **Password Policy**: Minimum length requirements

## Testing the Setup

1. Create the database table using one of the options above
2. Access: `https://invoice.jdosa.com/admin/settings`
3. Verify all settings are loaded correctly
4. Test user creation via: `https://invoice.jdosa.com/admin/create-user`
5. Confirm signup page is inaccessible to public

## Troubleshooting

### "Table doesn't exist" Error
- Run one of the setup options above
- Check database connection credentials
- Verify table prefix is correct (`jdosa_`)

### Permission Issues
- Ensure you're logged in as an admin user
- Check user role in database (`role` field should be 'admin')
- Verify admin access control is working

### Settings Not Saving
- Check database permissions
- Verify table structure is correct
- Look for PHP error logs

## Files Modified

The following files have been created or modified:
- `controllers/AdminController.php` - Admin panel controller
- `models/AdminSettings.php` - Settings model
- `views/admin/` - Admin panel views
- `views/site/login.php` - Removed create account button
- `migrations/m250711_000003_create_admin_settings_table.php` - Database migration
- `create_admin_settings_table.sh` - Automated setup script

## Next Steps

1. Execute the database setup
2. Test the admin panel functionality
3. Configure settings as needed
4. Create user accounts via admin panel
5. Remove this setup guide if desired