// Impor skrip Firebase (sesuai versi 12.5.0 Anda)
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.5.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.5.0/firebase-messaging.js";

// ======================================================
// PASTE OBJECT 'firebaseConfig' DARI LANGKAH 1 DI SINI
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

// Inisialisasi Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// Fungsi untuk meminta izin dan mendapatkan token
function requestPermission() {
    console.log('Requesting permission...');
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            console.log('Notification permission granted.');

            getToken(messaging, {
                // NANTI KITA ISI VAPID KEY DI SINI
                vapidKey: 'KwG-nvXzEGELhAwTfR2Zk1WXIPdxT_r2XViMslV5eA0'
            }).then((currentToken) => {
                if (currentToken) {
                    console.log('FCM Token:', currentToken);
                    sendTokenToServer(currentToken);
                } else {
                    console.log('No registration token available.');
                }
            }).catch((err) => {
                console.log('An error occurred while retrieving token. ', err);
            });
        } else {
            console.log('Unable to get permission to notify.');
        }
    });
}

// Fungsi untuk mengirim token ke Laravel (via API)
function sendTokenToServer(token) {
    fetch('/api/save-fcm-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            // 'Authorization': 'Bearer ' + 'TOKEN_JIKA_PAKAI_SANCTUM' // Sesuaikan jika API Anda perlu auth
        },
        body: JSON.stringify({ token: token })
    }).then(response => response.json())
        .then(data => console.log(data.message))
        .catch(err => console.error('Error sending token to server:', err));
}

// Menerima notifikasi saat website DIBUKA (foreground)
onMessage(messaging, (payload) => {
    console.log('Message received. ', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/assets/images/logo-mini.svg',
        sound: '/sounds/suara-notif.mp3' // <-- GANTI DI SINI
    };

    new Notification(notificationTitle, notificationOptions);
    // Mainkan suara
    new Audio('/sounds/suara-notif.mp3').play(); // <-- GANTI DI SINI JUGA
});

// Ekspor fungsi agar bisa dipanggil dari <script> lain
window.requestPermission = requestPermission;
