importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

// Initialize Firebase with your config
firebase.initializeApp({
    apiKey: "AIzaSyCa_0FbCFzKJzb3oYMc_CVB9ODFC-G3Rao",
    authDomain: "aktiflaunrdy.firebaseapp.com",
    databaseURL: "https://aktiflaunrdy-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "aktiflaunrdy",
    storageBucket: "aktiflaunrdy.firebasestorage.app",
    messagingSenderId: "780891292368",
    appId: "1:780891292368:web:3ae1d2e279d0b51f6bd4d5",
    measurementId: "G-0EX9XR618C"
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
