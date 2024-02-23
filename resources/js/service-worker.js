// service-worker.js

// Define max age for cache (in seconds)
//const MAX_AGE_SECONDS = 2 * 24 * 60 * 60; // 2 days
const MAX_AGE_SECONDS = 10; // 60 seconds

// Define the files we want to cache
var CACHE_NAME = "checklist-cache-v"+appVersion;
var urlsToCache = [
    assetURL + "build/css/app.min.css?v="+appVersion,
    assetURL + "build/css/custom.min.css?v="+appVersion,
    assetURL + "build/js/app.js?v="+appVersion
];

// Install a service worker
self.addEventListener("install", (event) => {
    // Perform install steps
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("Opened cache");
            return cache.addAll(urlsToCache);
            //return cache.delete(urlsToCache);
        })
    );
});

// Cache and return requests
/*self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            // Cache hit - return response
            if (response) {
                return response;
            }
            return fetch(event.request);
        })
    );
});*/
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            if (response) {
                return response; // return from cache
            }
            // Clone the request to ensure it's safe to read when adding to cache
            var fetchRequest = event.request.clone();

            return fetch(fetchRequest).then((response) => {
                // Check if we received a valid response
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // IMPORTANT: Clone the response. A response is a stream and because we want the browser to consume the response as well as the cache consuming the response, we need to clone it so we have two streams.
                var responseToCache = response.clone();

                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseToCache);
                });

                return response;
            });
        })
    );
});

// Update a service worker
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    //if (CACHE_NAME.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    //}
                })
            );
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    // Open each cache
                    return caches.open(cacheName).then((cache) => {
                        // Get all entries from the cache
                        return cache.keys().then((cacheEntries) => {
                            return Promise.all(
                                cacheEntries.map((entry) => {
                                    // For each entry, check if it's expired
                                    return cache.match(entry).then((response) => {
                                        let fetchedTime = new Date(response.headers.get('date'));
                                        let currentTime = new Date();
                                        let ageSeconds = (currentTime - fetchedTime) / 1000;
                                        if (ageSeconds > MAX_AGE_SECONDS) {
                                            console.log(`Deleting ${entry.url} from cache`);
                                            return cache.delete(entry);
                                        }
                                    });
                                })
                            );
                        });
                    });
                })
            );
        })
    );
});
