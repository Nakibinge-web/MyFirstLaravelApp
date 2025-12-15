# Production Docker Configuration

This document provides an overview of the production Docker setup for the Personal Financial Tracker application.

## Files Created

### 1. Production PHP Configuration
- **docker/php/Dockerfile.prod** - Multi-stage production Dockerfile with optimizations
- **docker/php/php.prod.ini** - Production PHP settings with OPcache enabled
- **docker/php/www.prod.conf** - Production PHP-FPM pool configuration

### 2. Production Nginx Configuration
- **docker/nginx/default.prod.conf** - Production Nginx config with:
  - Gzip compression
  - FastCGI caching
  - Security headers
  - Browser caching for static assets
  - Enhanced security rules

### 3. Production Docker Compose
- **docker-compose.prod.yml** - Production orchestration with:
  - Resource limits for all services
  - Health checks
  - Logging configuration
  - No development tools (phpMyAdmin, Vite dev server removed)
  - Security hardening

### 4. Production Environment Template
- **.env.production.example** - Production environment variables with:
  - Security recommendations
  - Deployment checklist
  - Secret management notes

## Key Production Features

### Security Enhancements
- Non-root user for PHP-FPM
- Disabled debug mode
- Security headers (X-Frame-Options, X-Content-Type-Options, etc.)
- Hidden sensitive files (.env, composer.json, etc.)
- No exposed development tools
- Optional Redis password protection

### Performance Optimizations
- Multi-stage Docker builds (smaller images)
- OPcache enabled with optimal settings
- FastCGI caching in Nginx
- Gzip compression
- Browser caching for static assets
- Optimized PHP-FPM pool settings

### Resource Management
- CPU and memory limits for all services
- Health checks for automatic recovery
- Restart policies (always)
- Log rotation configured

### Monitoring & Logging
- JSON file logging with rotation
- Health check endpoints
- Structured logging output

## Deployment Instructions

### 1. Pre-Deployment

```bash
# Build production assets
npm run build

# Ensure .env.production is configured
cp .env.production.example .env.production
# Edit .env.production with actual credentials

# Generate application key
php artisan key:generate --env=production
```

### 2. Build and Deploy

```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Start services
docker-compose -f docker-compose.prod.yml up -d
```

### 3. Post-Deployment

```bash
# Run migrations
docker-compose -f docker-compose.prod.yml exec php php artisan migrate --force

# Cache configuration
docker-compose -f docker-compose.prod.yml exec php php artisan config:cache
docker-compose -f docker-compose.prod.yml exec php php artisan route:cache
docker-compose -f docker-compose.prod.yml exec php php artisan view:cache

# Set proper permissions
docker-compose -f docker-compose.prod.yml exec php chown -R www:www storage bootstrap/cache
```

### 4. Verify Deployment

```bash
# Check service status
docker-compose -f docker-compose.prod.yml ps

# Check logs
docker-compose -f docker-compose.prod.yml logs

# Test application
curl http://localhost
```

## Differences from Development Setup

| Feature | Development | Production |
|---------|-------------|------------|
| Debug Mode | Enabled | Disabled |
| Code Mounting | Bind mount (live sync) | Copied into image |
| phpMyAdmin | Included | Removed |
| Vite Dev Server | Included | Removed (pre-built assets) |
| Port Exposure | All ports exposed | Only Nginx exposed |
| Resource Limits | None | CPU/Memory limits set |
| Health Checks | Basic | Comprehensive |
| Logging | Verbose | Error level only |
| OPcache | Disabled | Enabled |
| FastCGI Cache | Disabled | Enabled |
| User | Root | Non-root (www) |

## Security Checklist

Before deploying to production:

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Generate new `APP_KEY`
- [ ] Use HTTPS (update `APP_URL`)
- [ ] Set strong database passwords
- [ ] Configure Redis password
- [ ] Enable `SESSION_SECURE_COOKIE=true`
- [ ] Review and restrict database access
- [ ] Remove or secure phpMyAdmin
- [ ] Configure firewall rules
- [ ] Set up SSL/TLS certificates
- [ ] Enable log monitoring
- [ ] Configure automated backups
- [ ] Test backup restoration
- [ ] Scan images for vulnerabilities

## Performance Tuning

### PHP-FPM Settings (docker/php/www.prod.conf)
- `pm.max_children = 50` - Adjust based on available memory
- `pm.start_servers = 10` - Initial worker processes
- `pm.min_spare_servers = 5` - Minimum idle workers
- `pm.max_spare_servers = 20` - Maximum idle workers

### MySQL Settings (docker/mysql/my.cnf)
- `innodb_buffer_pool_size` - Set to 70% of available RAM
- `max_connections` - Adjust based on expected load

### Redis Settings (docker/redis/redis.conf)
- `maxmemory` - Set appropriate memory limit
- `maxmemory-policy = allkeys-lru` - Eviction policy

## Monitoring Recommendations

1. **Application Monitoring**
   - New Relic, Datadog, or similar APM tool
   - Track response times, error rates, throughput

2. **Infrastructure Monitoring**
   - Prometheus + Grafana for metrics
   - Monitor CPU, memory, disk usage
   - Track container health

3. **Log Aggregation**
   - ELK Stack (Elasticsearch, Logstash, Kibana)
   - CloudWatch Logs (AWS)
   - Splunk or similar

4. **Uptime Monitoring**
   - Pingdom, UptimeRobot, or similar
   - Alert on downtime

## Backup Strategy

### Database Backups
```bash
# Manual backup
docker-compose -f docker-compose.prod.yml exec mysql mysqldump -u root -p financial_tracker > backup.sql

# Automated backup (add to cron)
0 2 * * * docker-compose -f docker-compose.prod.yml exec mysql mysqldump -u root -p financial_tracker > /backups/db_$(date +\%Y\%m\%d).sql
```

### Storage Backups
```bash
# Backup storage volume
docker run --rm -v fintrack_storage_data:/data -v $(pwd)/backups:/backup alpine tar czf /backup/storage_$(date +\%Y\%m\%d).tar.gz /data
```

## Troubleshooting

### Container Won't Start
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs [service_name]

# Check health status
docker-compose -f docker-compose.prod.yml ps
```

### Performance Issues
```bash
# Check resource usage
docker stats

# Check PHP-FPM status
docker-compose -f docker-compose.prod.yml exec php php-fpm -t
```

### Database Connection Issues
```bash
# Test MySQL connection
docker-compose -f docker-compose.prod.yml exec php php artisan tinker
>>> DB::connection()->getPdo();
```

## Scaling Considerations

For high-traffic production environments:

1. **Horizontal Scaling**
   - Use Docker Swarm or Kubernetes
   - Load balance across multiple PHP containers
   - Use external managed database (RDS, Cloud SQL)

2. **Caching**
   - Implement Redis cluster for high availability
   - Use CDN for static assets
   - Enable full-page caching where appropriate

3. **Database**
   - Use read replicas for read-heavy workloads
   - Implement connection pooling
   - Consider database sharding for very large datasets

## Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Docker Production Best Practices](https://docs.docker.com/develop/dev-best-practices/)
- [Nginx Performance Tuning](https://www.nginx.com/blog/tuning-nginx/)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.configuration.php)
