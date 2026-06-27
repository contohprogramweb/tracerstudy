/**
 * PWA jQuery Wrapper for Survey Application
 * Handles service worker registration, offline storage, and background sync
 */

(function($) {
    'use strict';

    // Configuration
    const PWA_CONFIG = {
        swPath: '/public/sw.js',
        manifestPath: '/public/manifest.json',
        storagePrefix: 'ts_',
        syncTag: 'sync-survey-data'
    };

    // Storage keys
    const STORAGE_KEYS = {
        USER_TOKEN: PWA_CONFIG.storagePrefix + 'user_token',
        OFFLINE_QUEUE: PWA_CONFIG.storagePrefix + 'offline_queue',
        getSurveyProgress: function(surveyId) {
            return PWA_CONFIG.storagePrefix + 'survey_' + surveyId + '_progress';
        },
        getSurveyStatus: function(surveyId) {
            return PWA_CONFIG.storagePrefix + 'survey_' + surveyId + '_status';
        }
    };

    // PWA Manager Object
    window.PWAManager = {
        registration: null,
        isOnline: navigator.onLine,

        /**
         * Initialize PWA functionality
         */
        init: function() {
            this.bindEvents();
            this.registerServiceWorker();
            this.checkOnlineStatus();
            this.processOfflineQueue();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            var self = this;

            // Online/Offline status
            $(window).on('online', function() {
                self.isOnline = true;
                self.showOnlineNotification();
                self.processOfflineQueue();
            });

            $(window).on('offline', function() {
                self.isOnline = false;
                self.showOfflineNotification();
            });

            // Install button click
            $(document).on('click', '[data-install-pwa]', function(e) {
                e.preventDefault();
                self.promptInstall();
            });

            // Manual sync button
            $(document).on('click', '[data-sync-data]', function(e) {
                e.preventDefault();
                self.syncData();
            });
        },

        /**
         * Register Service Worker
         */
        registerServiceWorker: function() {
            if ('serviceWorker' in navigator) {
                var self = this;
                navigator.serviceWorker.register(PWA_CONFIG.swPath)
                    .then(function(registration) {
                        self.registration = registration;
                        console.log('Service Worker registered:', registration.scope);
                        
                        // Check for updates
                        registration.addEventListener('updatefound', function() {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    self.showUpdateNotification();
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.error('Service Worker registration failed:', error);
                    });
            }
        },

        /**
         * Check current online status
         */
        checkOnlineStatus: function() {
            this.isOnline = navigator.onLine;
            if (!this.isOnline) {
                this.showOfflineNotification();
            }
        },

        /**
         * Show offline notification
         */
        showOfflineNotification: function() {
            const notification = `
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-wifi"></i> Anda sedang offline. Data akan disimpan sementara.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            this.showNotification(notification, 'offline-notification');
        },

        /**
         * Show online notification
         */
        showOnlineNotification: function() {
            const notification = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> Anda kembali online. Menyinkronkan data...
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            this.showNotification(notification, 'online-notification');
        },

        /**
         * Show update available notification
         */
        showUpdateNotification: function() {
            const notification = `
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-sync"></i> Versi baru tersedia. <a href="#" onclick="location.reload()">Refresh</a> untuk update.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            this.showNotification(notification, 'update-notification');
        },

        /**
         * Show notification in the page
         */
        showNotification: function(html, id) {
            let container = $('#pwa-notifications');
            if (container.length === 0) {
                container = $('<div id="pwa-notifications" style="position:fixed;top:20px;right:20px;z-index:9999;width:350px;"></div>');
                $('body').append(container);
            }
            
            // Remove existing notification with same ID
            container.find('[data-notification-id="' + id + '"]').remove();
            
            const $notification = $(html).attr('data-notification-id', id);
            container.append($notification);
            
            // Auto dismiss after 5 seconds
            setTimeout(function() {
                $notification.alert('close');
            }, 5000);
        },

        /**
         * Prompt user to install PWA
         */
        promptInstall: function() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                this.deferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    }
                    this.deferredPrompt = null;
                }.bind(this));
            } else {
                alert('Untuk menginstall aplikasi:\n\n' +
                      'Chrome/Edge: Klik menu ⋮ > "Install App"\n' +
                      'Safari: Tap Share > "Add to Home Screen"\n' +
                      'Firefox: Klik menu ☰ > "Install"');
            }
        },

        /**
         * Save survey progress to localStorage
         */
        saveSurveyProgress: function(surveyId, data) {
            const progressKey = STORAGE_KEYS.getSurveyProgress(surveyId);
            const statusKey = STORAGE_KEYS.getSurveyStatus(surveyId);
            
            const progressData = {
                answers: data.answers || {},
                timestamp: Date.now(),
                last_question: data.last_question || null
            };
            
            try {
                localStorage.setItem(progressKey, JSON.stringify(progressData));
                localStorage.setItem(statusKey, 'in_progress');
                console.log('Survey progress saved:', surveyId);
                return true;
            } catch (e) {
                console.error('Failed to save survey progress:', e);
                return false;
            }
        },

        /**
         * Get survey progress from localStorage
         */
        getSurveyProgress: function(surveyId) {
            const progressKey = STORAGE_KEYS.getSurveyProgress(surveyId);
            try {
                const data = localStorage.getItem(progressKey);
                return data ? JSON.parse(data) : null;
            } catch (e) {
                console.error('Failed to get survey progress:', e);
                return null;
            }
        },

        /**
         * Get survey status
         */
        getSurveyStatus: function(surveyId) {
            const statusKey = STORAGE_KEYS.getSurveyStatus(surveyId);
            return localStorage.getItem(statusKey) || null;
        },

        /**
         * Mark survey as completed
         */
        completeSurvey: function(surveyId) {
            const statusKey = STORAGE_KEYS.getSurveyStatus(surveyId);
            try {
                localStorage.setItem(statusKey, 'completed');
                console.log('Survey marked as completed:', surveyId);
                return true;
            } catch (e) {
                console.error('Failed to complete survey:', e);
                return false;
            }
        },

        /**
         * Clear survey data
         */
        clearSurveyData: function(surveyId) {
            const progressKey = STORAGE_KEYS.getSurveyProgress(surveyId);
            const statusKey = STORAGE_KEYS.getSurveyStatus(surveyId);
            try {
                localStorage.removeItem(progressKey);
                localStorage.removeItem(statusKey);
                console.log('Survey data cleared:', surveyId);
                return true;
            } catch (e) {
                console.error('Failed to clear survey data:', e);
                return false;
            }
        },

        /**
         * Set user token
         */
        setUserToken: function(token) {
            try {
                localStorage.setItem(STORAGE_KEYS.USER_TOKEN, token);
                return true;
            } catch (e) {
                console.error('Failed to set user token:', e);
                return false;
            }
        },

        /**
         * Get user token
         */
        getUserToken: function() {
            return localStorage.getItem(STORAGE_KEYS.USER_TOKEN);
        },

        /**
         * Add request to offline queue
         */
        addToOfflineQueue: function(requestData) {
            try {
                let queue = this.getOfflineQueue();
                queue.push({
                    data: requestData,
                    timestamp: Date.now(),
                    attempts: 0
                });
                localStorage.setItem(STORAGE_KEYS.OFFLINE_QUEUE, JSON.stringify(queue));
                console.log('Added to offline queue:', requestData);
                return true;
            } catch (e) {
                console.error('Failed to add to offline queue:', e);
                return false;
            }
        },

        /**
         * Get offline queue
         */
        getOfflineQueue: function() {
            try {
                const queue = localStorage.getItem(STORAGE_KEYS.OFFLINE_QUEUE);
                return queue ? JSON.parse(queue) : [];
            } catch (e) {
                console.error('Failed to get offline queue:', e);
                return [];
            }
        },

        /**
         * Process offline queue when back online
         */
        processOfflineQueue: function() {
            if (!this.isOnline) {
                return;
            }

            var self = this;
            var queue = this.getOfflineQueue();
            
            if (queue.length === 0) {
                return;
            }

            console.log('Processing offline queue:', queue.length, 'items');

            // Process each item in the queue
            $.each(queue, function(index, item) {
                if (item.attempts < 3) {
                    self.sendQueuedRequest(item, index);
                } else {
                    console.log('Max attempts reached for queued item:', item);
                }
            });
        },

        /**
         * Send a queued request
         */
        sendQueuedRequest: function(item, index) {
            var self = this;
            
            $.ajax({
                url: item.data.url || '/survey/submit',
                type: item.data.method || 'POST',
                data: item.data.payload,
                headers: {
                    'Authorization': 'Bearer ' + this.getUserToken()
                },
                success: function(response) {
                    console.log('Queued request successful:', response);
                    self.removeFromOfflineQueue(index);
                    
                    // Show success message
                    self.showNotification(
                        '<div class="alert alert-success alert-dismissible fade show">' +
                        '<i class="fas fa-check"></i> Data berhasil disinkronkan.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>',
                        'sync-success'
                    );
                },
                error: function(xhr, status, error) {
                    console.error('Queued request failed:', error);
                    self.updateQueueItemAttempts(index, item.attempts + 1);
                }
            });
        },

        /**
         * Remove item from offline queue
         */
        removeFromOfflineQueue: function(index) {
            try {
                let queue = this.getOfflineQueue();
                queue.splice(index, 1);
                localStorage.setItem(STORAGE_KEYS.OFFLINE_QUEUE, JSON.stringify(queue));
                return true;
            } catch (e) {
                console.error('Failed to remove from offline queue:', e);
                return false;
            }
        },

        /**
         * Update attempts count for queue item
         */
        updateQueueItemAttempts: function(index, attempts) {
            try {
                let queue = this.getOfflineQueue();
                if (queue[index]) {
                    queue[index].attempts = attempts;
                    localStorage.setItem(STORAGE_KEYS.OFFLINE_QUEUE, JSON.stringify(queue));
                }
                return true;
            } catch (e) {
                console.error('Failed to update queue item:', e);
                return false;
            }
        },

        /**
         * Sync data with server
         */
        syncData: function() {
            if (!this.isOnline) {
                alert('Anda sedang offline. Tidak dapat melakukan sinkronisasi.');
                return;
            }

            var self = this;
            
            // Trigger background sync if supported
            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                this.registration.sync.register(PWA_CONFIG.syncTag)
                    .then(function() {
                        console.log('Background sync registered');
                    })
                    .catch(function(error) {
                        console.error('Background sync failed:', error);
                        self.processOfflineQueue();
                    });
            } else {
                // Fallback to manual sync
                this.processOfflineQueue();
            }
        },

        /**
         * Request notification permission
         */
        requestNotificationPermission: function() {
            if ('Notification' in window) {
                Notification.requestPermission().then(function(permission) {
                    console.log('Notification permission:', permission);
                    if (permission === 'granted') {
                        // Subscribe to push notifications (optional - integrate with OneSignal or custom)
                        self.subscribeToPush();
                    }
                });
            }
        },

        /**
         * Subscribe to push notifications (placeholder for OneSignal or custom)
         */
        subscribeToPush: function() {
            // This is a placeholder for push notification subscription
            // Integrate with OneSignal or your custom push service here
            
            if ('serviceWorker' in navigator && 'PushManager' in window) {
                this.registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array('YOUR_VAPID_PUBLIC_KEY')
                })
                .then(function(subscription) {
                    console.log('Push subscription:', subscription);
                    // Send subscription to your server
                })
                .catch(function(error) {
                    console.error('Push subscription failed:', error);
                });
            }
        },

        /**
         * Helper: Convert VAPID key
         */
        urlBase64ToUint8Array: function(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        },

        /**
         * Check if app is installed
         */
        isInstalled: function() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                   window.navigator.standalone === true;
        },

        /**
         * Get all stored survey data
         */
        getAllStoredSurveys: function() {
            var surveys = [];
            for (var i = 0; i < localStorage.length; i++) {
                var key = localStorage.key(i);
                if (key && key.indexOf(PWA_CONFIG.storagePrefix + 'survey_') === 0) {
                    try {
                        var data = JSON.parse(localStorage.getItem(key));
                        surveys.push({
                            key: key,
                            data: data
                        });
                    } catch (e) {
                        // Skip non-JSON items
                    }
                }
            }
            return surveys;
        },

        /**
         * Clear all stored data
         */
        clearAllData: function() {
            var self = this;
            for (var i = 0; i < localStorage.length; i++) {
                var key = localStorage.key(i);
                if (key && key.indexOf(PWA_CONFIG.storagePrefix) === 0) {
                    localStorage.removeItem(key);
                }
            }
            console.log('All PWA data cleared');
        }
    };

    // Auto-initialize on document ready
    $(document).ready(function() {
        window.PWAManager.init();
    });

})(jQuery);
