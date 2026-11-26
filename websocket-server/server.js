const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const redis = require('redis');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: { origin: '*', methods: ['GET', 'POST'] }
});

// Use environment variables for Docker compatibility, fallback to localhost for local development
const redisHost = process.env.REDIS_HOST || '127.0.0.1';
const redisPort = process.env.REDIS_PORT || 6379;

const subscriber = redis.createClient({ host: redisHost, port: redisPort });

subscriber.on('ready', () => {
    console.log(`[WS] Redis subscriber ready. Connected to ${redisHost}:${redisPort}`);
    subscriber.subscribe('comments_channel');
});

subscriber.on('message', (channel, message) => {
    if (channel === 'comments_channel') {
        console.log('[WS] Received event:', message);
        io.emit('comment_event', JSON.parse(message));
    }
});

subscriber.on('error', (err) => console.error('[WS] Redis error:', err));

let adminCount = 0;
io.on('connection', (socket) => {
    adminCount++;
    console.log(`[WS] Admin connected. Total online: ${adminCount}`);
    
    socket.on('disconnect', () => {
        adminCount--;
        console.log(`[WS] Admin disconnected. Total online: ${adminCount}`);
    });
});

// Health check endpoint for Docker or monitoring tools
app.get('/health', (req, res) => res.json({ status: 'ok', admins: adminCount }));

// Use environment variable for port, fallback to 3000
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`[WS] Server running on http://localhost:${PORT}`);
});