FROM node:20-alpine

# Set working directory
WORKDIR /var/www/html

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Expose Vite dev server port
EXPOSE 5173

# Default command to run Vite dev server with host 0.0.0.0
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
