importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

// Initialize Firebase with your config
firebase.initializeApp({
    apiKey: "AIzaSyArP2I3J92Hzt_ThtOUC5mFJTwBZGZBudU",
    authDomain: "aktiflaundry-8ce34.firebaseapp.com",
    projectId: "aktiflaundry-8ce34",
    storageBucket: "aktiflaundry-8ce34.firebasestorage.app",
    messagingSenderId: "523711351540",
    appId: "1:523711351540:web:2ea8258b264da736f4aca1",
    measurementId: "G-GSNG5V5RSX"
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
