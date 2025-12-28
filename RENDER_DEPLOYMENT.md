# Render Deployment Guide

## 🚀 Deploy Personal Financial Tracker to Render

This guide will help you deploy the Personal Financial Tracker application to Render.com using Docker.

## 📋 Prerequisites

- GitHub account with your code repository
- Render account (free tier available)
- Docker knowledge (basic)

## 🎯 Quick Deployment (Recommended)

### Option 1: Using render.yaml (Automatic)

1. **Push to GitHub**
   ```bash
   git add .
   git commit -m "Add Render deployment configuration"
   git push origin main
   ```

2. **Connect to Render**
   - Go to [Render Dashboard](https://dashboard.render.com)
   - Click "New +" → "Blueprint"
   - Connect your GitHub repository
   - Render will detect `render.yaml` and set up services automatically

3. **Configure Environment Variables**
   - Render will create the web service and database
   - Set `APP_URL` to your Render app URL (e.g., `https://your-app.onrender.com`)
   - Generate `APP_KEY`: `php artisan key:generate --show`

### Option 2: Manual Setup

1. **Create Web Service**
   - Go to Render Dashboard
   - Click "New +" → "Web Service"
   - Connect your GitHub repository
   - Configure as follows:

   ```
   Name: personal-financial-tracker
   Environment: Docker
   Dockerfile Path: ./Dockerfile.render
   Plan: Starter (or higher)
   ```

2. **Create Database**
   - Click "New +" → "PostgreSQL"
   - Name: `fintrack-db`
   - Plan: Starter (or higher)
   - Note the connection details

3. **Set Environment Variables**
   ```
   APP_NAME=Personal Financial Tracker
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:your-generated-key
   APP_URL=https://your-app.onrender.com
   DATABASE_URL=postgresql://user:pass@host:port/dbname
   LOG_CHANNEL=stderr
   LOG_LEVEL=error
   SESSION_DRIVER=file
   CACHE_DRIVER=file
   QUEUE_CONNECTION=sync
   MAIL_MAILER=log
   BCRYPT_ROUNDS=12
   ```

## 🔧 Configuration Details

### Required Environment Variables

| Variable | Value | Description |
|----------|-------|-------------|
| `APP_NAME` | Personal Financial Tracker | Application name |
| `APP_ENV` | production | Environment |
| `APP_DEBUG` | false | Debug mode (must be false) |
| `APP_KEY` | base64:... | Laravel encryption key |
| `APP_URL` | https://your-app.onrender.com | Your app URL |
| `DATABASE_URL` | postgresql://... | Database connection |
| `LOG_CHANNEL` | stderr | Logging channel |
| `SESSION_DRIVER` | file | Session storage |
| `CACHE_DRIVER` | file | Cache storage |

### Optional Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `MAIL_MAILER` | log | Email driver |
| `QUEUE_CONNECTION` | sync | Queue driver |
| `BCRYPT_ROUNDS` | 12 | Password hashing rounds |
| `LOG_LEVEL` | error | Logging level |

## 🗄️ Database Setup

### Using PostgreSQL (Recommended for Render)

1. **Create PostgreSQL Database**
   - Render provides managed PostgreSQL
   - Automatically sets `DATABASE_URL`

2. **Update Laravel Configuration**
   The app will automatically use PostgreSQL when `DATABASE_URL` is set.

### Using MySQL (Alternative)

If you prefer MySQL, you can use external services like:
- PlanetScale
- AWS RDS
- DigitalOcean Managed Databases

Set the `DATABASE_URL` accordingly:
```
DATABASE_URL=mysql://user:password@host:port/database
```

## 🚀 Deployment Process

### Automatic Deployment

1. **Push to GitHub**
   ```bash
   git push origin main
   ```

2. **Render Auto-deploys**
   - Render detects changes
   - Builds Docker image
   - Deploys automatically
   - Runs health checks

### Manual Deployment

1. **Trigger Deploy**
   - Go to Render Dashboard
   - Click "Manual Deploy"
   - Select branch to deploy

## 📊 Monitoring & Logs

### View Logs
```bash
# In Render Dashboard
- Go to your service
- Click "Logs" tab
- View real-time logs
```

### Health Checks
- Render automatically monitors `/health` endpoint
- Service restarts if health check fails
- View status in dashboard

### Performance Monitoring
- Monitor response times
- Check memory usage
- View error rates

## 🔧 Troubleshooting

### Common Issues

#### 1. Build Failures
**Problem**: Docker build fails
**Solution**: 
- Check Dockerfile syntax
- Verify all files exist
- Check build logs in Render

#### 2. Database Connection Issues
**Problem**: Can't connect to database
**Solution**:
- Verify `DATABASE_URL` is set
- Check database service is running
- Ensure database allows connections

#### 3. Application Errors
**Problem**: 500 errors or crashes
**Solution**:
- Check application logs
- Verify environment variables
- Ensure `APP_KEY` is set
- Check file permissions

#### 4. Slow Performance
**Problem**: App is slow
**Solution**:
- Upgrade Render plan
- Optimize database queries
- Enable OPcache (already enabled)
- Check resource usage

### Debug Commands

```bash
# Check environment variables
echo $APP_KEY
echo $DATABASE_URL

# Test database connection
php artisan migrate:status

# Clear caches
php artisan cache:clear
php artisan config:clear

# Check application status
curl https://your-app.onrender.com/health
```

## 🔒 Security Considerations

### Production Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] Strong `APP_KEY` generated
- [ ] HTTPS enabled (automatic on Render)
- [ ] Database credentials secure
- [ ] No sensitive data in logs
- [ ] Regular security updates

### Environment Security

- [ ] Use Render's environment variables (encrypted)
- [ ] Don't commit `.env` files
- [ ] Rotate keys regularly
- [ ] Monitor access logs

## 📈 Scaling

### Vertical Scaling
- Upgrade Render plan for more CPU/RAM
- Monitor resource usage
- Optimize application performance

### Horizontal Scaling
- Render Pro plans support multiple instances
- Use external database for session storage
- Implement proper caching strategy

## 💰 Cost Optimization

### Free Tier Limitations
- Service spins down after 15 minutes of inactivity
- 750 hours per month
- Slower cold starts

### Paid Plans Benefits
- Always-on services
- Faster performance
- More resources
- Better support

## 🔄 CI/CD Pipeline

### Automatic Deployment
```yaml
# .github/workflows/deploy.yml
name: Deploy to Render

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to Render
        run: |
          # Render auto-deploys on push
          echo "Deployment triggered"
```

### Manual Deployment
- Use Render dashboard
- Deploy specific commits
- Rollback if needed

## 📚 Additional Resources

### Render Documentation
- [Render Docs](https://render.com/docs)
- [Docker on Render](https://render.com/docs/docker)
- [Environment Variables](https://render.com/docs/environment-variables)

### Laravel Resources
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Laravel Configuration](https://laravel.com/docs/configuration)

### Support
- [Render Community](https://community.render.com)
- [Render Support](https://render.com/support)

---

## 🎉 Success!

Once deployed, your Personal Financial Tracker will be available at:
`https://your-app-name.onrender.com`

### Next Steps
1. Test all functionality
2. Set up monitoring
3. Configure custom domain (optional)
4. Set up backups
5. Monitor performance

**Happy deploying! 🚀**