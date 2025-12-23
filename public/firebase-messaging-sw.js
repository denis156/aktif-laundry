importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

// Initialize Firebase with your config
firebase.initializeApp({
    apiKey: "AIzaSyAWkQmro1JLDjcI2Xtr7Pz77zxrFdniZKc",
    authDomain: "aktiflaundry-8ce34.firebaseapp.com",
    databaseURL: "https://aktiflaundry-8ce34-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "aktiflaundry-8ce34",
    storageBucket: "aktiflaundry-8ce34.firebasestorage.app",
    messagingSenderId: "523711351540",
    appId: "1:523711351540:web:f820c918bf2fa91ef4aca1",
    measurementId: "G-2DVZT9RGLY"
});

// Retrieve an instance of Firebase Messaging
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const notificationTitle = payload.notification?.title || payload.data?.title || 'Aktif Laundry';
    const notificationOptions = {
        body: payload.notification?.body || payload.data?.body || 'You have a new notification',
        icon: payload.notification?.icon || payload.data?.icon || '/icons/icon-512x512.png',
        badge: '/icons/icon-512x512.png',
        tag: payload.data?.tag || 'default',
        requireInteraction: false,
        vibrate: [200, 100, 200], // Vibration pattern: vibrate 200ms, pause 100ms, vibrate 200ms
        data: payload.data || {}
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[firebase-messaging-sw.js] Notification click received.', event);

    event.notification.close();

    // Custom action based on notification data
    const urlToOpen = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // If a window is already open, focus it
                for (const client of clientList) {
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Otherwise, open a new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});
