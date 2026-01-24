# Troubleshooting 500 Internal Server Error

## Common Causes and Solutions

### 1. Missing APP_KEY
**Symptom**: 500 error on all pages
**Cause**: Laravel requires an encryption key to function
**Solution**: 
- The start script now automatically generates an APP_KEY if missing
- Alternatively, set it manually in Render dashboard:
  ```
  APP_KEY=base64:your-generated-key-here
  ```

### 2. Database Connection Issues
**Symptom**: 500 error, especially on pages that use database
**Cause**: Invalid DATABASE_URL or database not ready
**Solution**:
- Ensure DATABASE_URL is properly set by Render
- Check database service is running
- Migrations may fail initially but should retry

### 3. File Permissions
**Symptom**: 500 error with file write operations
**Cause**: Laravel cannot write to storage or cache directories
**Solution**: 
- The Dockerfile sets proper permissions
- If issues persist, check container logs

### 4. Missing Environment Variables
**Symptom**: Various 500 errors
**Cause**: Required environment variables not set
**Solution**: Ensure these are set in Render:
```
APP_NAME=Personal Financial Tracker
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-key
LOG_CHANNEL=stderr
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

## Debugging Steps

### Step 1: Check Render Logs
1. Go to your Render dashboard
2. Click on your service
3. Go to "Logs" tab
4. Look for error messages during startup

### Step 2: Check Health Endpoint
Visit: `https://your-app.onrender.com/health`

Expected response:
```json
{
  "status": "healthy",
  "timestamp": "2026-01-23T...",
  "app": "Personal Financial Tracker",
  "version": "1.0.0"
}
```

### Step 3: Enable Debug Mode (Temporarily)
In Render dashboard, set:
```
APP_DEBUG=true
```
**⚠️ Remember to set back to false after debugging!**

### Step 4: Check Environment Variables
In Render dashboard, verify all required environment variables are set.

### Step 5: Manual Debug (Advanced)
If you have shell access to the container:
```bash
# Run the debug script
php debug-500-error.php

# Check Laravel logs
tail -f storage/logs/laravel.log

# Test artisan commands
php artisan --version
php artisan config:show
```

## Quick Fixes

### Fix 1: Redeploy
Sometimes a simple redeploy fixes transient issues:
1. Go to Render dashboard
2. Click "Manual Deploy"
3. Wait for deployment to complete

### Fix 2: Clear Caches
The start script clears caches, but you can also:
1. Set environment variable: `CLEAR_CACHE=true`
2. Redeploy

### Fix 3: Check Database
Ensure the database service is running and connected:
1. Check database service status in Render
2. Verify DATABASE_URL is automatically set
3. Check database connection in logs

## Environment Variables Checklist

Required variables (automatically set by render.yaml):
- ✅ APP_NAME
- ✅ APP_ENV=production
- ✅ APP_DEBUG=false
- ✅ APP_KEY (auto-generated)
- ✅ LOG_CHANNEL=stderr
- ✅ SESSION_DRIVER=file
- ✅ CACHE_DRIVER=file
- ✅ QUEUE_CONNECTION=sync
- ✅ MAIL_MAILER=log

Automatically set by Render:
- ✅ DATABASE_URL (when database service is connected)
- ✅ PORT (set by Render platform)

Optional (set manually if needed):
- APP_URL (your app's URL)
- Any custom application settings

## Still Having Issues?

1. **Check Render Status**: Visit status.render.com for platform issues
2. **Review Logs**: Look for specific error messages in deployment logs
3. **Test Locally**: Build and run the Docker image locally to test
4. **Contact Support**: If all else fails, contact Render support with logs

## Local Testing

To test the Docker image locally:
```bash
# Build the image
docker build -f Dockerfile.render -t fintrack-test .

# Run with environment variables
docker run -p 8080:80 \
  -e APP_KEY=base64:test-key-here \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  fintrack-test

# Test the application
curl http://localhost:8080/health
```

This should help identify if the issue is with the Docker image or the Render environment.