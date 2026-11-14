ARG NODE_VERSION=24.22.1
FROM node:${NODE_VERSION}-alpine

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
