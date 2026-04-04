# Implementation Plan: Admin Panel

## Overview

This implementation plan breaks down the admin panel feature into discrete coding tasks. The feature adds role-based access control, system monitoring, user management, database backups, and activity logging to the Laravel personal financial tracker. Tasks are organized to build incrementally, with testing integrated throughout to catch errors early.

## Tasks

- [x] 1. Database migrations and model setup
  - [x] 1.1 Create migration to add admin fields to users table
    - Add is_admin (boolean, default false), is_active (boolean, default true), last_login_at (timestamp, nullable)
    - Add indexes on is_admin and is_active columns
    - _Requirements: 1.1, 1.5, 9.1, 9.2, 11.1, 11.2, 11.3, 12.3_
  
  - [x] 1.2 Create activity_logs table migration
    - Create table with fields: id, user_id (foreign key nullable), action (string 50), description (string 500), ip_address (string 45), user_agent (text), metadata (json), timestamps
    - Add indexes on action, user_id, and created_at columns
    - _Requirements: 3.9, 3.10, 3.11, 11.4, 11.5, 11.6, 12.2_
  
  - [x] 1.3 Create backups table migration
    - Create table with fields: id, filename (unique), path (string 500), size (bigInteger), description (text nullable), created_by (foreign key), status (enum: pending/completed/failed), timestamps
    - Add indexes on status and created_at columns
    - _Requirements: 5.1, 5.2, 11.7, 11.8, 11.9_
  
  - [x] 1.4 Create system_settings table migration
    - Create table with fields: id, key (unique string 100), value (text), type (enum: string/integer/boolean/json), description (string 500 nullable), timestamps
    - Add index on key column
    - _Requirements: 10.5, 11.10, 11.11_
  
  - [x] 1.5 Extend User model with admin functionality
    - Add fillable fields: is_admin, is_active, last_login_at
    - Add casts for boolean and datetime fields
    - Implement methods: isAdmin(), isActive(), activate(), deactivate()
    - _Requirements: 1.1, 9.1, 9.2, 11.1, 11.2, 11.3_

- [x] 2. Create core models
  - [x] 2.1 Create ActivityLog model
    - Define fillable fields and casts
    - Implement user() relationship (belongsTo)
    - Implement methods: getActionLabel(), getMetadataValue()
    - _Requirements: 3.9, 3.10, 3.11, 11.4, 11.5, 11.6_
  
  - [x] 2.2 Create Backup model
    - Define fillable fields and casts
    - Implement creator() relationship (belongsTo User)
    - Implement methods: getFormattedSize(), exists(), delete() override
    - _Requirements: 5.2, 5.4, 11.7, 11.8, 11.9_
  
  - [x] 2.3 Create SystemSetting model
    - Define fillable fields and casts
    - Implement static methods: get(), set()
    - Implement getCastValue() method for type casting
    - _Requirements: 10.2, 10.3, 10.5, 10.6, 11.10, 11.11_

- [x] 3. Implement IsAdmin middleware
  - [x] 3.1 Create IsAdmin middleware class
    - Implement handle() method to check auth()->user()->is_admin
    - Return 403 Forbidden if not admin
    - Allow request to proceed if admin
    - _Requirements: 1.1, 1.2, 1.4_
  
  - [ ]* 3.2 Write property test for IsAdmin middleware
    - **Property 1: Admin Access Control**
    - **Validates: Requirements 1.1, 1.2, 1.5**
    - Generate random users with varying is_admin values
    - Verify admin users can access routes and non-admin users receive 403
  
  - [x] 3.3 Register IsAdmin middleware in bootstrap/app.php
    - Add middleware alias 'admin' => IsAdmin::class
    - _Requirements: 1.4_

- [x] 4. Implement ActivityLogService
  - [x] 4.1 Create ActivityLogService class
    - Implement log() method to create activity log entries
    - Capture IP address and user agent from request
    - Store metadata as JSON
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11_
  
  - [x] 4.2 Implement activity log retrieval methods
    - Implement getActivityLogs() with filtering support (user_id, action, date_from, date_to, search)
    - Implement getUserActivity() to get logs for specific user
    - Implement getActionTypes() to return available action types
    - Order by created_at descending, paginate 50 per page
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 12.6_
  
  - [x] 4.3 Implement deleteOldLogs() method
    - Delete logs older than specified days (default 90)
    - Return count of deleted records
    - _Requirements: 12.5_
  
  - [ ]* 4.4 Write property test for activity logging
    - **Property 2: Activity Log Completeness**
    - **Validates: Requirements 3.1-3.11, 15.1-15.6**
    - Perform critical actions and verify corresponding log entries exist
    - Verify log contains correct action, timestamp, user_id, IP, and metadata
  
  - [ ]* 4.5 Write property test for activity log filtering
    - **Property 8: Activity Log Filtering**
    - **Validates: Requirements 4.1-4.8**
    - Generate random filters and verify returned logs match all criteria
    - Verify no matching logs are excluded

- [x] 5. Implement DashboardService
  - [x] 5.1 Create DashboardService class
    - Implement getSystemMetrics() to aggregate all dashboard data
    - Implement getTotalUsers(), getActiveUsers(), getTotalTransactions()
    - Implement getTransactionVolume(), getRecentActivity()
    - Implement getUserGrowthData(), getTransactionTrends()
    - Cache metrics for 5 minutes
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 12.1_
  
  - [ ]* 5.2 Write property test for dashboard metrics
    - **Property 6: Metric Accuracy**
    - **Validates: Requirements 2.1-2.8**
    - Query metrics then independently count database records
    - Verify all counts and sums match exactly

- [x] 6. Implement BackupService
  - [x] 6.1 Create BackupService class with createBackup() method
    - Generate unique filename with timestamp format
    - Create backup directory if not exists
    - Create backup record with status 'pending'
    - Copy database file to backup location
    - Update backup record with size and status 'completed'
    - Log action via ActivityLogService
    - Handle failures by marking status 'failed' and logging error
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.9_
  
  - [x] 6.2 Implement backup management methods
    - Implement listBackups() ordered by created_at descending
    - Implement getBackup(), downloadBackup(), deleteBackup()
    - Implement getBackupSize() with human-readable formatting
    - Implement cleanupOldBackups() to keep only last 10 backups
    - Log all backup operations via ActivityLogService
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8_
  
  - [ ]* 6.3 Write property test for backup integrity
    - **Property 3: Backup Integrity**
    - **Validates: Requirements 5.3, 5.4, 6.2**
    - Create backups and verify file exists with matching size
    - Verify all completed backups have valid files
  
  - [ ]* 6.4 Write property test for backup retention
    - **Property 7: Backup Retention**
    - **Validates: Requirements 6.6**
    - Create more than 10 backups
    - Verify oldest backups are automatically deleted
    - Verify count never exceeds MAX_BACKUPS

- [x] 7. Implement UserManagementService
  - [x] 7.1 Create UserManagementService class
    - Implement getAllUsers() with filtering and pagination (25 per page)
    - Implement getUserDetails() to return user profile and relationships
    - Implement getUserStatistics() to calculate transaction, budget, goal stats
    - Ensure net amount equals income minus expenses
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8, 8.9, 12.4, 12.7_
  
  - [x] 7.2 Implement user status management methods
    - Implement toggleUserStatus() to toggle is_active field
    - Prevent self-deactivation with exception
    - Log all status changes via ActivityLogService
    - Implement deleteUser() with cascade handling
    - _Requirements: 7.5, 7.6, 7.7, 9.3, 9.4_
  
  - [x] 7.3 Implement admin role management methods
    - Implement promoteToAdmin() to set is_admin = true
    - Implement revokeAdmin() to set is_admin = false
    - Log all role changes via ActivityLogService
    - _Requirements: 1.5, 7.8, 7.9, 7.10_
  
  - [ ]* 7.4 Write property test for user status consistency
    - **Property 4: User Status Consistency**
    - **Validates: Requirements 9.1, 9.2, 9.3, 9.4**
    - Create users and deactivate them
    - Verify deactivated users cannot log in
  
  - [ ]* 7.5 Write property test for self-protection
    - **Property 5: Self-Protection**
    - **Validates: Requirements 7.6, 14.3**
    - Attempt to deactivate currently authenticated admin
    - Verify operation throws exception
  
  - [ ]* 7.6 Write property test for user statistics consistency
    - **Property 9: User Statistics Consistency**
    - **Validates: Requirements 8.1-8.9**
    - Calculate statistics for multiple users
    - Verify net = income - expenses for each user
  
  - [ ]* 7.7 Write property test for idempotent status toggle
    - **Property 10: Idempotent Status Toggle**
    - **Validates: Requirements 7.5**
    - Record initial status, toggle twice
    - Verify status matches initial state

- [-] 8. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Create AdminController
  - [x] 9.1 Create AdminController with dashboard method
    - Inject DashboardService
    - Implement dashboard() to fetch metrics and return view
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_
  
  - [x] 9.2 Implement user management controller methods
    - Implement users() to list all users with pagination
    - Implement userShow() to display user details and statistics
    - Implement userToggleStatus() to activate/deactivate users
    - Handle exceptions and return appropriate flash messages
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 8.1-8.9_
  
  - [x] 9.3 Implement activity log controller methods
    - Implement activityLogs() to display logs with filtering
    - Accept filter parameters from request (user_id, action, date_from, date_to, search)
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8_
  
  - [x] 9.4 Implement backup controller methods
    - Implement backups() to list all backups
    - Implement createBackup() to trigger backup creation
    - Implement downloadBackup() to serve backup file
    - Implement deleteBackup() to remove backup
    - Verify file exists before download, return 404 if missing
    - Log all backup operations
    - _Requirements: 5.1-5.9, 6.1-6.8, 14.2_
  
  - [x] 9.5 Implement system settings controller methods
    - Implement systemSettings() to display all settings
    - Implement updateSystemSettings() to save setting changes
    - Validate setting values according to type
    - Log all setting changes via ActivityLogService
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_

- [x] 10. Create admin views
  - [x] 10.1 Create admin layout template
    - Create resources/views/layouts/admin.blade.php
    - Include navigation menu with links to dashboard, users, activity logs, backups, settings
    - Use Tailwind CSS for styling (consistent with existing app)
    - _Requirements: 1.1_
  
  - [x] 10.2 Create dashboard view
    - Create resources/views/admin/dashboard.blade.php
    - Display metric cards for users, transactions, financial summary
    - Display recent activity table
    - Display system health metrics (database size, backups)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_
  
  - [x] 10.3 Create user management views
    - Create resources/views/admin/users/index.blade.php for user list
    - Create resources/views/admin/users/show.blade.php for user details
    - Include pagination, filtering, and action buttons
    - Display user statistics and recent activity
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 8.1-8.9_
  
  - [x] 10.4 Create activity logs view
    - Create resources/views/admin/activity-logs/index.blade.php
    - Include filter form (user, action, date range, search)
    - Display logs in table with pagination
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8_
  
  - [x] 10.5 Create backup management views
    - Create resources/views/admin/backups/index.blade.php
    - Display backup list with create, download, delete actions
    - Show backup status, size, and creation date
    - _Requirements: 5.1-5.9, 6.1-6.8_
  
  - [x] 10.6 Create system settings view
    - Create resources/views/admin/settings/index.blade.php
    - Display settings form with appropriate input types
    - Include CSRF protection
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_

- [x] 11. Configure routes
  - [x] 11.1 Add admin routes to routes/web.php
    - Create route group with 'auth' and 'admin' middleware
    - Add routes for dashboard, users, activity logs, backups, settings
    - Use prefix 'admin' and name prefix 'admin.'
    - _Requirements: 1.1, 1.2, 1.3, 1.4_
  
  - [x] 11.2 Add rate limiting to admin routes
    - Configure rate limiter for 60 requests per minute
    - Apply to admin route group
    - _Requirements: 13.2_

- [x] 12. Implement authentication enhancements
  - [x] 12.1 Update login controller to track last_login_at
    - Update User model's last_login_at on successful login
    - Log login action via ActivityLogService
    - _Requirements: 3.1, 11.3, 15.1, 15.2, 15.3, 15.4, 15.5_
  
  - [x] 12.2 Add is_active check to authentication
    - Modify authentication to reject users with is_active = false
    - Return appropriate error message
    - _Requirements: 9.1, 9.2, 9.3, 9.4_

- [x] 13. Create scheduled jobs
  - [x] 13.1 Create CleanupOldLogsJob
    - Implement job to delete activity logs older than 90 days
    - Schedule to run daily in app/Console/Kernel.php
    - _Requirements: 12.5_
  
  - [x] 13.2 Create BackupCleanupJob
    - Implement job to maintain backup retention (keep last 10)
    - Schedule to run after each backup creation
    - _Requirements: 6.6_

- [x] 14. Add security controls
  - [x] 14.1 Verify CSRF protection on all admin forms
    - Ensure @csrf directive in all form views
    - _Requirements: 13.1_
  
  - [x] 14.2 Verify input validation and sanitization
    - Add validation rules to all controller methods
    - Use Laravel's validation for filter parameters
    - _Requirements: 13.4, 13.6, 14.5_
  
  - [x] 14.3 Configure secure session settings
    - Verify httpOnly and secure flags in config/session.php
    - _Requirements: 13.8_
  
  - [x] 14.4 Add backup file storage security
    - Verify backups stored in storage/app/backups (not public)
    - Add .gitignore entry for backup directory
    - _Requirements: 13.3_

- [ ] 15. Create database seeders
  - [ ] 15.1 Create AdminUserSeeder
    - Create seeder to generate first admin user
    - Set is_admin = true, is_active = true
    - Use secure password hashing
    - _Requirements: 1.1, 11.1, 11.2, 13.9_
  
  - [ ] 15.2 Create SystemSettingsSeeder
    - Create seeder for default system settings
    - Include settings for backup retention, log retention, etc.
    - _Requirements: 10.5, 11.10, 11.11_

- [x] 16. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 17. Integration and documentation
  - [x] 17.1 Add admin panel link to main navigation
    - Update resources/views/layouts/app.blade.php
    - Show admin link only for users with is_admin = true
    - _Requirements: 1.1_
  
  - [ ]* 17.2 Write integration tests for complete workflows
    - Test admin login → dashboard → user management flow
    - Test backup creation → download → delete flow
    - Test activity log filtering and pagination
    - _Requirements: All requirements_
  
  - [x] 17.3 Update README with admin panel documentation
    - Document how to create first admin user
    - Document admin panel features and usage
    - Document security considerations

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property tests validate universal correctness properties from the design
- Unit tests validate specific examples and edge cases
- All services use dependency injection for testability
- Cache is used for expensive dashboard queries (5-minute TTL)
- Activity logs and backups have automatic cleanup to prevent unbounded growth
- Security controls (CSRF, rate limiting, validation) are integrated throughout
