FROM node:20-alpine

# Install git and build essentials required by npm/baileys
RUN apk add --no-cache git python3 make g++

# Set working directory
WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm install --omit=dev

# Copy source files
COPY . .

# Expose port (Render automatically maps PORT)
EXPOSE 3000

# Start bot service
CMD ["node", "index.js"]

