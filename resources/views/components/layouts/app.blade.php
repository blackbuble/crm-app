<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#FF1493">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CRM Kiosk">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icon-192.png">
    <link rel="apple-touch-icon" href="/images/icon-192.png">

    <title>{{ config('app.name', 'CRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    {{ $slot }}

    @livewireScripts
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registered:', registration.scope);
                        
                        // Check for updates periodically
                        setInterval(() => {
                            registration.update();
                        }, 60000); // Check every minute
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });

            // Handle offline form submissions
            window.addEventListener('offline', () => {
                console.log('App is offline - submissions will be queued');
                showOfflineNotification();
            });

            window.addEventListener('online', () => {
                console.log('App is back online - syncing data');
                if ('serviceWorker' in navigator && 'sync' in navigator.serviceWorker) {
                    navigator.serviceWorker.ready.then(registration => {
                        return registration.sync.register('sync-leads');
                    });
                }
                showOnlineNotification();
            });
        }

        function showOfflineNotification() {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #ff9800;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                font-family: sans-serif;
            `;
            notification.innerHTML = '📡 Offline - Data akan tersimpan dan dikirim otomatis';
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        }

        function showOnlineNotification() {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4caf50;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 9999;
                font-family: sans-serif;
            `;
            notification.innerHTML = '✅ Online - Menyinkronkan data...';
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    </script>
</body>
</html>
