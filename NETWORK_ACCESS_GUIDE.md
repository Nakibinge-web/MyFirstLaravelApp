# Network Access Guide for Personal Financial Tracker

## Accessing the Application from Other Devices on Your Local Network

### Step 1: Find Your Server's IP Address

#### On Windows:
```cmd
ipconfig
```
Look for "IPv4 Address" under your active network adapter (usually WiFi or Ethernet).
Example: `192.168.1.100`

#### On Linux/Mac:
```bash
ip addr show
# or
ifconfig
```
Look for `inet` address (not 127.0.0.1).
Example: `192.168.1.100`

### Step 2: Verify Docker is Running

Make sure your Docker containers are running:
```bash
docker-compose ps
```

You should see all services running (nginx, php, mysql, redis).

### Step 3: Access from Other Devices

Once you have your server's IP address, you can access the application from any device on the same network:

#### Main Application:
```
http://YOUR_SERVER_IP
```
Example: `http://192.168.1.100`

#### phpMyAdmin (Database Management):
```
http://YOUR_SERVER_IP:8080
```
Example: `http://192.168.1.100:8080`

### Step 4: Update APP_URL in .env (Important!)

To ensure the application works correctly when accessed from other devices, update your `.env` file:

```env
APP_URL=http://YOUR_SERVER_IP
```
Example: `APP_URL=http://192.168.1.100`

After updating, restart your Docker containers:
```bash
docker-compose down
docker-compose up -d
```

### Step 5: Configure Firewall (If Needed)

If you can't access the application from other devices, you may need to allow traffic through your firewall:

#### Windows Firewall:
1. Open Windows Defender Firewall
2. Click "Advanced settings"
3. Click "Inbound Rules" → "New Rule"
4. Select "Port" → Next
5. Select "TCP" and enter port `80` → Next
6. Select "Allow the connection" → Next
7. Check all profiles → Next
8. Name it "Docker Web Server" → Finish

#### Linux (UFW):
```bash
sudo ufw allow 80/tcp
sudo ufw allow 8080/tcp
```

### Step 6: Test Access

From another device on the same network:
1. Open a web browser
2. Navigate to `http://YOUR_SERVER_IP`
3. You should see the login page

### Troubleshooting

#### Can't Access from Other Devices?

1. **Check if Docker is running:**
   ```bash
   docker-compose ps
   ```

2. **Check if ports are listening:**
   ```bash
   # Windows
   netstat -an | findstr :80
   
   # Linux/Mac
   netstat -tuln | grep :80
   ```

3. **Verify network connectivity:**
   ```bash
   # From another device, ping your server
   ping YOUR_SERVER_IP
   ```

4. **Check Docker network binding:**
   The nginx service in `docker-compose.yml` should have:
   ```yaml
   ports:
     - "80:80"  # This binds to all network interfaces (0.0.0.0:80)
   ```

5. **Restart Docker containers:**
   ```bash
   docker-compose down
   docker-compose up -d
   ```

### Security Considerations

When making your application accessible on the local network:

1. **Use Strong Passwords:** Ensure all user accounts have strong passwords
2. **Keep it Local:** Don't expose port 80 to the internet without proper security
3. **Use HTTPS:** For production, consider setting up SSL/TLS certificates
4. **Firewall Rules:** Only allow access from trusted devices
5. **Regular Updates:** Keep your application and Docker images updated

### Advanced: Using a Custom Domain (Optional)

If you want to use a custom domain name instead of IP address:

1. **Edit hosts file on client devices:**
   
   **Windows:** `C:\Windows\System32\drivers\etc\hosts`
   **Linux/Mac:** `/etc/hosts`
   
   Add this line:
   ```
   192.168.1.100  fintrack.local
   ```

2. **Update APP_URL:**
   ```env
   APP_URL=http://fintrack.local
   ```

3. **Access via:** `http://fintrack.local`

### Port Reference

- **80** - Main application (HTTP)
- **8080** - phpMyAdmin
- **3306** - MySQL (for database clients)
- **6379** - Redis (for Redis clients)
- **5173** - Vite dev server (if running npm run dev)

### Example Access URLs

Assuming your server IP is `192.168.1.100`:

- Application: `http://192.168.1.100`
- phpMyAdmin: `http://192.168.1.100:8080`
- API endpoints: `http://192.168.1.100/api/...`

### Mobile Access

The application is fully responsive and works great on mobile devices:

1. Connect your phone/tablet to the same WiFi network
2. Open a browser on your mobile device
3. Navigate to `http://YOUR_SERVER_IP`
4. Login and use the application

The responsive design ensures a great experience on all screen sizes!

---

## Quick Start Commands

```bash
# Find your IP address
ipconfig  # Windows
ip addr   # Linux

# Start Docker containers
docker-compose up -d

# Check container status
docker-compose ps

# View logs
docker-compose logs -f

# Restart containers
docker-compose restart

# Stop containers
docker-compose down
```

## Need Help?

If you encounter any issues:
1. Check the Docker logs: `docker-compose logs -f nginx php`
2. Verify all containers are running: `docker-compose ps`
3. Test from the server itself first: `curl http://localhost`
4. Check firewall settings
5. Ensure all devices are on the same network
