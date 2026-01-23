# Render 500 Error Debugging Guide

## Current Issue
The application is returning a 500 Internal Server Error, which indicates a server-side problem.

## Most Common Causes & Solutions

### 1. Missing APP_KEY in Production
**Problem**: Laravel requires an APP_KEY for encryption/decryption
**Check**: Verify APP_KEY is set in Render environment variables
**Solution**: Generate and set APP_KEY in Render dashboard

### 2. Database Connection Issues
**Problem**: Cannot connect to database or database doesn't exist
**Symptoms**: 500 error when trying to access any page that uses database
**Solution**: Verify DATABASE_URL is correctly set

### 3. Missing Storage Directories
**Problem**: Laravel cannot write to storage directories
**Solution**: Ensure storage directories are created and writable

### 4. Cache/Config Issues
**Problem**: Cached configuration conflicts with production environment
**Solution**: Clear caches during deployment

### 5. Missing Dependencies
**Problem**: Required PHP extensions or packages not installed
**Solution**: Verify all dependencies are included in Docker image

## Debugging Steps

### Step 1: Check Render Logs
1. Go to Render Dashboard
2. Click on your service
3. Go to "Logs" tab
4. Look for specific error messages

### Step 2: Check Environment Variables
Ensure these are set in Render:
```
APP_KEY=base64:your-generated-key
APP_ENV=production
APP_DEBUG=false
DATABASE_URL=postgresql://user:pass@host:port/dbname
APP_URL=https://your-app.onrender.com
```

### Step 3: Test Health Endpoint
Try accessing: `https://your-app.onrender.com/health`
- If this works: Issue is with main application
- If this fails: Issue is with basic setup

## Quick Fixes to Try

### Fix 1: Update Environment Variables in Render
Set these in Render Dashboard → Environment:
```
APP_ENV=production
APP_DEBUG=true  # Temporarily enable for debugging
LOG_CHANNEL=stderr
LOG_LEVEL=debug
```

### Fix 2: Check Database Connection
If using PostgreSQL, ensure DATABASE_URL format:
```
DATABASE_URL=postgresql://username:password@hostname:port/database_name
```

### Fix 3: Verify APP_KEY
Generate a new APP_KEY:
```bash
# Run locally to generate
php artisan key:generate --show
```
Then set in Render environment variables.

## Common Error Patterns

### "Class not found" Errors
- **Cause**: Autoloader issues
- **Fix**: Ensure `composer dump-autoload --optimize` runs in Dockerfile

### "Permission denied" Errors  
- **Cause**: File permission issues
- **Fix**: Check storage directory permissions in Dockerfile

### "Connection refused" Errors
- **Cause**: Database connection issues
- **Fix**: Verify DATABASE_URL and database service status

### "Key not found" Errors
- **Cause**: Missing APP_KEY
- **Fix**: Set APP_KEY in environment variables

## Immediate Action Plan

1. **Enable Debug Mode Temporarily**:
   - Set `APP_DEBUG=true` in Render environment
   - Set `LOG_LEVEL=debug`
   - Redeploy and check logs

2. **Check Specific Error**:
   - Look at Render logs for exact error message
   - Check if it's database, cache, or application error

3. **Test Basic Functionality**:
   - Try `/health` endpoint first
   - Then try main routes

4. **Verify Environment**:
   - Ensure all required environment variables are set
   - Check DATABASE_URL format is correct

## Next Steps Based on Error Type

### If Database Error:
1. Verify DATABASE_URL format
2. Check if database exists and is accessible
3. Run migrations if needed

### If Cache/Session Error:
1. Set `CACHE_DRIVER=file`
2. Set `SESSION_DRIVER=file`
3. Redeploy

### If Permission Error:
1. Check Dockerfile permissions setup
2. Verify storage directories are writable

### If Class/Autoload Error:
1. Check composer.json dependencies
2. Verify autoloader optimization in Dockerfile

## Monitoring Commands

Once you identify the specific error, you can:

1. **Check Application Logs**:
   ```bash
   # In Render logs, look for Laravel error messages
   ```

2. **Test Database Connection**:
   ```bash
   # Add to start.sh temporarily
   php artisan migrate:status
   ```

3. **Verify Configuration**:
   ```bash
   # Add to start.sh temporarily  
   php artisan config:show
   ```

## Recovery Steps

If the issue persists:

1. **Rollback to Working Version**:
   - Use Render's rollback feature
   - Deploy previous working commit

2. **Simplify Configuration**:
   - Use file-based cache/sessions temporarily
   - Disable non-essential features

3. **Test Locally**:
   - Build Docker image locally
   - Test with production-like environment variables

## Contact Information

If you need help debugging:
1. Share the specific error from Render logs
2. Confirm which environment variables are set
3. Let me know if `/health` endpoint works

The key is to get the specific error message from the logs to identify the exact cause.