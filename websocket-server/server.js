const express = require('express');
const http    = require('http');
const { Server } = require('socket.io');
const redis   = require('redis');

const app    = express();
const server = http.createServer(app);
const io     = new Server(server, {
    cors: { origin: '*', methods: ['GET', 'POST'] }
});

const subscriber = redis.createClient({ host: '127.0.0.1', port: 6379 });

subscriber.on('ready', () => {
    console.log('[WS] Redis готов');
    subscriber.subscribe('comments_channel');
});

subscriber.on('message', (channel, message) => {
    if (channel === 'comments_channel') {
        console.log('[WS] Событие:', message);
        io.emit('comment_event', JSON.parse(message));
    }
});

subscriber.on('error', (err) => console.error('[WS] Redis error:', err));

let adminCount = 0;
io.on('connection', (socket) => {
    adminCount++;
    console.log(`[WS] Админ подключился. Онлайн: ${adminCount}`);
    socket.on('disconnect', () => {
        adminCount--;
        console.log(`[WS] Админ отключился. Онлайн: ${adminCount}`);
    });
});

app.get('/health', (req, res) => res.json({ status: 'ok', admins: adminCount }));

const PORT = 3000;
server.listen(PORT, () => {
    console.log(`[WS] Сервер на http://localhost:${PORT}`);
});