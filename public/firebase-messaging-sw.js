// Impor skrip Firebase (sesuai versi 12.5.0 Anda)
importScripts("https://www.gstatic.com/firebasejs/12.5.0/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/12.5.0/firebase-messaging.js");

// ======================================================
// PASTE OBJECT 'firebaseConfig' YANG SAMA DI SINI LAGI
// ======================================================
const firebaseConfig = {
    apiKey: "AIzaSyCdEV7DdFYMP5L4rV5qK4JIGjn2I2hO7lA",
    authDomain: "pstore-absensi.firebaseapp.com",
    projectId: "pstore-absensi",
    storageBucket: "pstore-absensi.firebasestorage.app",
    messagingSenderId: "550936407111",
    appId: "1:550936407111:web:cc466d98a2434440bf1562",
    measurementId: "G-L04DJFBZHH"
};
// ======================================================

initializeApp(firebaseConfig);
const messaging = getMessaging();

// Menerima notifikasi saat website DITUTUP (background)
self.addEventListener('push', (event) => {
    const payload = event.data.json();
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/assets/images/logo-mini.svg',
        sound: '/sounds/suara-notif.mp3' // <-- GANTI DI SINI
    };

    event.waitUntil(self.registration.showNotification(notificationTitle, notificationOptions));
});
