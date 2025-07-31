import './bootstrap';

window.Echo.channel('public-updates')
    .listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (e) => {
        console.log("🔔 New Notification:", e.message);
        alert(e.message);
    });