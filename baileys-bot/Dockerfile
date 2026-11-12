FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install dependencies first for efficient layer caching
COPY package*.json ./
RUN npm ci --omit=dev

# Copy source files
COPY . .

# Expose port (Render automatically maps this)
EXPOSE 3000

# Start bot service
CMD ["node", "index.js"]
