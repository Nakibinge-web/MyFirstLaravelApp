# Requirements Document: Admin Panel

## Introduction

The Admin Panel feature provides system administrators with comprehensive control and visibility over the Laravel personal financial tracker application. This document specifies the functional and non-functional requirements for implementing role-based access control, system monitoring, user management, database backup capabilities, and activity logging. The requirements are derived from the approved technical design and ensure that administrators can effectively monitor system health, manage users, and maintain data integrity while regular users continue to use the application without disruption.

## Glossary

- **System**: The Laravel personal financial tracker application
- **Admin_Panel**: The administrative interface accessible only to users with admin privileges
- **Admin_User**: A user with the is_admin flag set to true
- **Regular_User**: A user with the is_admin flag set to false
- **Activity_Log**: A record of system actions and user activities stored for audit purposes
- **Backup**: A complete copy of the SQLite database file with associated metadata
- **Dashboard_Service**: Service component that aggregates system-wide metrics
- **Activity_Log_Service**: Service component that records and retrieves activity logs
- **Backup_Service**: Service component that manages database backup operations
- **User_Management_Service**: Service component that handles administrative user operations
- **IsAdmin_Middleware**: Middleware component that verifies admin privileges
- **Critical_Action**: System actions that must be logged (login, user deactivation, backup creation, admin role changes)
- **System_Metrics**: Aggregated statistics about users, transactions, and system health

## Requirements

### Requirement 1: Admin Access Control

**User Story:** As a system administrator, I want only authorized admin users to access the admin panel, so that sensitive system operations and data are protected from unauthorized access.

#### Acceptance Criteria

1. WHEN an authenticated user with is_admin = true accesses an admin route, THEN THE System SHALL allow the request to proceed
2. WHEN an authenticated user with is_admin = false accesses an admin route, THEN THE System SHALL return a 403 Forbidden response
3. WHEN an unauthenticated user accesses an admin route, THEN THE System SHALL redirect to the login page
4. THE IsAdmin_Middleware SHALL execute after the authentication middleware in the middleware chain
5. WHEN an admin user's is_admin flag is set to false, THEN THE System SHALL immediately revoke access to all admin routes

### Requirement 2: Dashboard Metrics Display

**User Story:** As a system administrator, I want to view comprehensive system metrics on the admin dashboard, so that I can monitor system health and user activity at a glance.

#### Acceptance Criteria

1. WHEN an admin user accesses the dashboard, THEN THE Dashboard_Service SHALL display the total count of all users
2. WHEN an admin user accesses the dashboard, THEN THE Dashboard_Service SHALL display the count of active users (logged in within 30 days)
3. WHEN an admin user accesses the dashboard, THEN THE Dashboard_Service SHALL display the total count of all transactions
4. WHEN an admin user accesses the dashboard, THEN THE Dashboard_Service SHALL display the total transaction volume (sum of all transaction amounts)
5. WHEN an admin user accesses the dashboard, THEN THE Dashboard_Service SHALL display the total income, total expenses, and net amount
6. WHEN an admin user accesses the dashboard, THEN THE Dashboard_Service SHALL display the most recent 10 activity log entries
7. WHEN an admin user accesses the dashboard, THEN THE Dashboard_Service SHALL display the database size, backup count, and last backup timestamp
8. THE Dashboard_Service SHALL calculate all metrics based on the current database state at the time of the request

### Requirement 3: Activity Logging

**User Story:** As a system administrator, I want all critical system actions to be automatically logged, so that I can maintain an audit trail for security and compliance purposes.

#### Acceptance Criteria

1. WHEN a user logs into the system, THEN THE Activity_Log_Service SHALL create a log entry with action 'user_login'
2. WHEN an admin deactivates a user account, THEN THE Activity_Log_Service SHALL create a log entry with action 'user_deactivated'
3. WHEN an admin activates a user account, THEN THE Activity_Log_Service SHALL create a log entry with action 'user_activated'
4. WHEN a database backup is created, THEN THE Activity_Log_Service SHALL create a log entry with action 'backup_created'
5. WHEN a database backup fails, THEN THE Activity_Log_Service SHALL create a log entry with action 'backup_failed'
6. WHEN a backup is downloaded, THEN THE Activity_Log_Service SHALL create a log entry with action 'backup_downloaded'
7. WHEN an admin role is granted to a user, THEN THE Activity_Log_Service SHALL create a log entry with action 'admin_promoted'
8. WHEN an admin role is revoked from a user, THEN THE Activity_Log_Service SHALL create a log entry with action 'admin_revoked'
9. THE Activity_Log_Service SHALL capture the IP address and user agent for each log entry
10. THE Activity_Log_Service SHALL store optional metadata as JSON for each log entry
11. WHEN a log entry is created, THEN THE Activity_Log_Service SHALL associate it with the user_id of the actor, or null for system actions

### Requirement 4: Activity Log Retrieval and Filtering

**User Story:** As a system administrator, I want to view and filter activity logs, so that I can investigate specific events or user activities.

#### Acceptance Criteria

1. WHEN an admin requests activity logs, THEN THE Activity_Log_Service SHALL return logs ordered by created_at descending
2. WHEN an admin applies a user_id filter, THEN THE Activity_Log_Service SHALL return only logs associated with that user
3. WHEN an admin applies an action filter, THEN THE Activity_Log_Service SHALL return only logs with that action type
4. WHEN an admin applies a date_from filter, THEN THE Activity_Log_Service SHALL return only logs created on or after that date
5. WHEN an admin applies a date_to filter, THEN THE Activity_Log_Service SHALL return only logs created on or before that date
6. WHEN an admin applies a search filter, THEN THE Activity_Log_Service SHALL return only logs where the description contains the search term
7. THE Activity_Log_Service SHALL paginate activity logs with 50 records per page
8. WHEN multiple filters are applied, THEN THE Activity_Log_Service SHALL return logs that match all filter criteria (AND logic)

### Requirement 5: Database Backup Creation

**User Story:** As a system administrator, I want to create database backups on demand, so that I can protect against data loss and maintain recovery points.

#### Acceptance Criteria

1. WHEN an admin initiates a backup, THEN THE Backup_Service SHALL create a backup record with status 'pending'
2. WHEN creating a backup, THEN THE Backup_Service SHALL generate a unique filename using the format "backup_YYYY-MM-DD_HH-MM-SS.sqlite"
3. WHEN creating a backup, THEN THE Backup_Service SHALL copy the database file to storage/app/backups/
4. WHEN a backup file is successfully created, THEN THE Backup_Service SHALL update the backup record with the file size and status 'completed'
5. WHEN a backup creation fails, THEN THE Backup_Service SHALL update the backup record with status 'failed'
6. WHEN a backup is created, THEN THE Backup_Service SHALL log the action via Activity_Log_Service
7. WHEN a backup creation fails, THEN THE Backup_Service SHALL log the failure with error details via Activity_Log_Service
8. THE Backup_Service SHALL store the admin user_id who created the backup in the created_by field
9. WHEN a backup is created, THEN THE Backup_Service SHALL ensure the backup directory exists, creating it if necessary

### Requirement 6: Backup Management

**User Story:** As a system administrator, I want to manage existing backups, so that I can download, delete, and maintain backup retention policies.

#### Acceptance Criteria

1. WHEN an admin requests the backup list, THEN THE Backup_Service SHALL return all backup records ordered by created_at descending
2. WHEN an admin downloads a backup, THEN THE Backup_Service SHALL verify the file exists before allowing download
3. WHEN an admin downloads a backup, THEN THE Backup_Service SHALL log the download action via Activity_Log_Service
4. WHEN an admin deletes a backup, THEN THE Backup_Service SHALL remove both the file from disk and the database record
5. WHEN an admin deletes a backup, THEN THE Backup_Service SHALL log the deletion action via Activity_Log_Service
6. WHEN the backup count exceeds 10 completed backups, THEN THE Backup_Service SHALL automatically delete the oldest backups
7. WHEN a backup file is missing but the record exists, THEN THE System SHALL return a 404 error on download attempts
8. THE Backup_Service SHALL display backup file sizes in human-readable format (KB, MB, GB)

### Requirement 7: User Management Operations

**User Story:** As a system administrator, I want to manage user accounts, so that I can activate, deactivate, and view user information.

#### Acceptance Criteria

1. WHEN an admin requests the user list, THEN THE User_Management_Service SHALL return all users with pagination (25 users per page)
2. WHEN an admin views a user's details, THEN THE User_Management_Service SHALL display the user's profile information
3. WHEN an admin views a user's details, THEN THE User_Management_Service SHALL display the user's transaction statistics
4. WHEN an admin views a user's details, THEN THE User_Management_Service SHALL display the user's budget and goal statistics
5. WHEN an admin toggles a user's status, THEN THE User_Management_Service SHALL change is_active from true to false or false to true
6. WHEN an admin attempts to deactivate their own account, THEN THE User_Management_Service SHALL throw an exception with message "Cannot disable your own account"
7. WHEN an admin toggles a user's status, THEN THE User_Management_Service SHALL log the action via Activity_Log_Service
8. WHEN an admin promotes a user to admin, THEN THE User_Management_Service SHALL set is_admin to true
9. WHEN an admin revokes admin privileges, THEN THE User_Management_Service SHALL set is_admin to false
10. WHEN an admin promotes or revokes admin privileges, THEN THE User_Management_Service SHALL log the action via Activity_Log_Service

### Requirement 8: User Statistics Calculation

**User Story:** As a system administrator, I want to view detailed statistics for each user, so that I can understand user engagement and activity levels.

#### Acceptance Criteria

1. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the total count of the user's transactions
2. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the sum of the user's income transactions
3. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the sum of the user's expense transactions
4. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the net amount (income minus expenses)
5. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the count of the user's budgets (total and active)
6. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the count of the user's goals (total and completed)
7. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the goal completion rate as a percentage
8. WHEN an admin requests user statistics, THEN THE User_Management_Service SHALL calculate the user's account age in days
9. THE User_Management_Service SHALL ensure net amount equals income minus expenses for all calculations

### Requirement 9: User Account Status Enforcement

**User Story:** As a system administrator, I want deactivated users to be unable to log in, so that I can effectively disable accounts when necessary.

#### Acceptance Criteria

1. WHEN a user with is_active = false attempts to log in, THEN THE System SHALL reject the authentication attempt
2. WHEN a user with is_active = true attempts to log in, THEN THE System SHALL allow the authentication attempt to proceed
3. WHEN an admin deactivates a user, THEN THE System SHALL immediately prevent that user from accessing the application
4. WHEN an admin activates a user, THEN THE System SHALL immediately allow that user to log in again

### Requirement 10: System Settings Management

**User Story:** As a system administrator, I want to manage system-wide settings, so that I can configure application behavior without code changes.

#### Acceptance Criteria

1. WHEN an admin views system settings, THEN THE System SHALL display all settings with their current values
2. WHEN an admin updates a setting, THEN THE System SHALL validate the value according to the setting's type
3. WHEN an admin updates a setting, THEN THE System SHALL save the new value to the system_settings table
4. WHEN an admin updates a setting, THEN THE System SHALL log the change via Activity_Log_Service
5. THE System SHALL support setting types: string, integer, boolean, and json
6. WHEN retrieving a setting value, THEN THE System SHALL cast it to the appropriate type based on the type field

### Requirement 11: Data Model Integrity

**User Story:** As a system developer, I want data models to enforce integrity constraints, so that the database remains consistent and valid.

#### Acceptance Criteria

1. THE User model SHALL have is_admin as a boolean field with default value false
2. THE User model SHALL have is_active as a boolean field with default value true
3. THE User model SHALL have last_login_at as a nullable datetime field
4. THE ActivityLog model SHALL have action as a required string field with maximum length 50 characters
5. THE ActivityLog model SHALL have description as a required string field with maximum length 500 characters
6. THE ActivityLog model SHALL have metadata as a JSON field that casts to array
7. THE Backup model SHALL have filename as a required unique string field
8. THE Backup model SHALL have size as an integer field representing bytes
9. THE Backup model SHALL have status as an enum field with values: 'pending', 'completed', 'failed'
10. THE SystemSetting model SHALL have key as a required unique string field with maximum length 100 characters
11. THE SystemSetting model SHALL have type as an enum field with values: 'string', 'integer', 'boolean', 'json'

### Requirement 12: Performance Optimization

**User Story:** As a system administrator, I want the admin panel to load quickly, so that I can efficiently perform administrative tasks.

#### Acceptance Criteria

1. WHEN an admin accesses the dashboard, THEN THE System SHALL cache the metrics for 5 minutes
2. WHEN querying activity logs, THEN THE System SHALL use database indexes on action, user_id, and created_at columns
3. WHEN querying users, THEN THE System SHALL use database indexes on is_admin, is_active, and email columns
4. WHEN loading user details, THEN THE System SHALL use eager loading for related transactions, budgets, and goals
5. THE System SHALL automatically delete activity logs older than 90 days via scheduled job
6. THE System SHALL limit activity log pagination to 50 records per page
7. THE System SHALL limit user list pagination to 25 records per page

### Requirement 13: Security Controls

**User Story:** As a system administrator, I want the admin panel to be secure, so that sensitive data and operations are protected from unauthorized access and attacks.

#### Acceptance Criteria

1. THE System SHALL require CSRF tokens on all admin form submissions
2. THE System SHALL implement rate limiting of 60 requests per minute on admin routes
3. THE System SHALL store backup files outside the public directory
4. THE System SHALL use parameterized queries for all database operations to prevent SQL injection
5. THE System SHALL automatically escape output in Blade templates to prevent XSS attacks
6. THE System SHALL validate and sanitize all user inputs before processing
7. THE System SHALL use HTTPS for all admin routes in production environments
8. THE System SHALL set httpOnly and secure flags on session cookies
9. THE System SHALL hash all passwords using Laravel's built-in hashing
10. THE System SHALL log all admin access attempts via Activity_Log_Service

### Requirement 14: Error Handling

**User Story:** As a system administrator, I want clear error messages when operations fail, so that I can understand and resolve issues quickly.

#### Acceptance Criteria

1. WHEN a backup creation fails, THEN THE System SHALL display a descriptive error message to the admin
2. WHEN a backup file is missing, THEN THE System SHALL return a 404 error with message "Backup file not found"
3. WHEN an admin attempts self-deactivation, THEN THE System SHALL display error message "Cannot disable your own account"
4. WHEN a database connection fails, THEN THE System SHALL log the error and display a 500 error page
5. WHEN invalid filter parameters are provided, THEN THE System SHALL ignore invalid filters and proceed with valid ones
6. WHEN a user deletion fails due to dependencies, THEN THE System SHALL display a descriptive error message
7. THE System SHALL log all errors to the application log file for debugging purposes

### Requirement 15: Audit Trail Completeness

**User Story:** As a system administrator, I want a complete audit trail of all administrative actions, so that I can investigate security incidents and maintain compliance.

#### Acceptance Criteria

1. FOR ALL critical actions, WHEN the action is performed, THEN THE Activity_Log_Service SHALL create a corresponding log entry
2. THE Activity_Log_Service SHALL include the timestamp of each action with precision to the second
3. THE Activity_Log_Service SHALL include the user_id of the actor for all user-initiated actions
4. THE Activity_Log_Service SHALL include the IP address from which the action was performed
5. THE Activity_Log_Service SHALL include the user agent (browser) information for each action
6. THE Activity_Log_Service SHALL include relevant metadata (e.g., target_user_id, backup_id) for each action
7. THE System SHALL ensure activity logs cannot be modified after creation (insert-only table)
