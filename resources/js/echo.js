/*
Running Reverb in Production
In this article, we will walk through the steps required to run Laravel Reverb in production.
To run Laravel Reverb on a production server using Linux Ubuntu with Apache 2, you would typically follow these steps:

Install Supervisor: Reverb should be kept running continuously in the background. This can be achieved using Supervisor, a process monitor for Linux. Install Supervisor on Ubuntu with:

Command:
sudo apt-get install supervisor

Create Supervisor Configuration: Create a Supervisor configuration file for Reverb. This file typically goes in /etc/supervisor/conf.d/reverb.conf. Here’s an example of what this configuration might look like:

reverb.conf:
    [program:reverb]
    command=php /path/to/your/project/artisan reverb:start
    numprocs=1
    autostart=true
    autorestart=true
    stderr_logfile=/var/log/reverb.err.log
    stdout_logfile=/var/log/reverb.out.log

Start the Supervisor Service: After configuring Supervisor, you can start it and enable it to run on boot with:
Command:
sudo systemctl enable supervisor
sudo systemctl start supervisor

Add proxy conf
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo systemctl restart apache2

Control Supervisor: Finally, control the Reverb process using Supervisor commands:
Command:
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
sudo supervisorctl stop reverb
sudo supervisorctl start reverb
sudo supervisorctl restart reverb

Useful Laravel commands:
php artisan reverb:start
php artisan reverb:stop
php artisan reverb:restart

sudo netstat -tlnp | grep 5174

sudo supervisorctl restart reverb
php artisan reverb:restart

*/
import {
    toastAlert,
    sweetAlert,
    sweetWizardAlert,
    bsPopoverTooltip,
    showPreloader,
    searchUser,
    formatDateTime
} from './helpers.js';

import { default as Echo } from '../libs/laravel-echo/echo.js';
//import Pusher from '../libs/pusher-js/web/pusher.min.js';

import '../libs/pusher-js/web/pusher.min.js';
export default Pusher;

//const Echo = window.Echo;
//const Pusher = window.Pusher;

//window.Echo = Echo;
window.Pusher = Pusher;

document.addEventListener("DOMContentLoaded", function () {
    const xCSRFtoken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    /**
     * Setup Echo and global variables
     * Initializes Echo with Pusher as the broadcasting driver and sets CSRF token for security.
     * DOC: https://laravel.com/docs/11.x/broadcasting#client-reverb
     */

    /*
    window.Echo = new Echo({
        broadcaster: "reverb",
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.REVERB_SERVER_PORT ?? 5174,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        //forceTLS: ('http' ?? "https") === "https",
        forceTLS: true,
        enabledTransports: ["ws", "wss"],
        encrypted: true,
        auth: {
            headers: {
                "X-CSRF-TOKEN": xCSRFtoken,
            },
        },
    });
    */
    window.Echo = new Echo({
        broadcaster: "reverb",
        key: '91p2sn4aiaumc8gk5cwk',
        wsHost: window.location.hostname,
        wsPort: 5174,
        wssPort: 443,
        forceTLS: true,
        enabledTransports: ["ws", "wss"],
        encrypted: true,
        auth: {
            headers: {
                "X-CSRF-TOKEN": xCSRFtoken,
            },
        },
    });

    const currentUserId = parseInt(document .querySelector('meta[name="current-user-id"]').getAttribute("content"));

    const userList = document.querySelectorAll('#userList input[name="user_chat"]');

    const messageContainer = document.querySelector("#chat-conversation");
    const conversationContainer = document.querySelector("ul#users-conversation");
    const sendMessageButton = document.getElementById("send-message");
    const messageTextarea = document.getElementById("message-textarea");
    const chatInputfeedback = document.getElementById("chat-input-feedback");

    //Check id the userList element is present.
    if (userList) {
        // Initialize onlineUsers
        let onlineUsers = {};

        /**
         * List online users
         * This script subscribes to a presence channel called 'users-online' to track and display online users.
         * The user data (ID and name) are provided by the server in the routes/channels.php file.
         */
        window.Echo.join(`users-online`)
            .here((users) => {
                console.log('Currently online users in the app:', users);

                onlineUsers = users.map(user => user.id);
                console.log('Online users:', onlineUsers);

                updateUsersStatusBadge(onlineUsers);

            })
            .joining((user) => {
                console.log(`${user.name} has joined.`);

                onlineUsers.push(user.id);
                console.log('Online users:', onlineUsers);

                updateUsersStatusBadge(onlineUsers);
            })
            .leaving((user) => {
                console.log(`${user.name} has left.`);

                onlineUsers = onlineUsers.filter(id => id !== user.id);
                console.log('Online users:', onlineUsers);

                updateUsersStatusBadge(onlineUsers);
            });

        /**
         * Updates the badges to reflect the online status of users.
         */
        function updateUsersStatusBadge(onlineUsers) {
            const statusBadges = document.querySelectorAll('#userList .user-status');

            statusBadges.forEach(badge => {
                const userId = parseInt(badge.getAttribute('data-user-id'));
                const chatUserImg = badge.closest('.chat-user-img');
                if (chatUserImg) { // Ensure the element was found
                    if (onlineUsers.includes(userId)) {
                        chatUserImg.classList.add('online');
                    } else {
                        chatUserImg.classList.remove('online');
                    }
                } else {
                    console.error('Failed to find .chat-user-img for user status badge:', badge);
                }
            });
        }


        /**
         * Check for any radio button that is already checked on page load
         */
        userList.forEach(radio => {
            if (radio.checked) {
                const recipientId = parseInt(radio.value);

                document.querySelector('.user-chat-topbar').classList.add('d-none');
                document.querySelector('.chat-input-section').classList.add('d-none');

                messageTextarea.focus();
                messageTextarea.value = "";

                if (recipientId) {
                    conversationContainer.innerHTML = '';

                    initializeChat(recipientId);
                }

                return;
            }
        });


        /**
         * Add event listeners to each radio button
         */
        userList.forEach(radio => {
            ['click', 'change'].forEach(eventType => {
                radio.addEventListener(eventType, async function(event) {
                    event.preventDefault();

                    document.querySelector('.user-chat-topbar').classList.add('d-none');
                    document.querySelector('.chat-input-section').classList.add('d-none');

                    if (this.checked) {
                        const recipientId = parseInt(this.value);
                        // console.log("Checked recipient ID:", recipientId);

                        /**
                         * This part initialize chat components.
                         */
                        if(recipientId){
                            conversationContainer.innerHTML = '';

                            var userName = this.getAttribute('data-user-name');
                            document.getElementById('username').innerHTML = userName;

                            var userAvatar = this.getAttribute('data-user-avatar');
                            document.getElementById('useravatar').setAttribute('src', userAvatar);

                            initializeChat(recipientId);
                        }

                        return;
                    }
                });
            });
        });


        /**
         * Initializes the chat application by setting up channel listeners and message sending capabilities.c
         */
        function initializeChat(recipientId) {
            console.log("Initializing chat with recipient ID:", recipientId);

            // Initialize variables.
            let currentPage = 1;
            let isLoadingOldMessages = false;

            // Global state to track status between two users
            let statusBetweenUsers = {};

            /*const checkedRadio = userList.checked;
            const recipientId = checkedRadio ? checkedRadio.value : null;*/

            const channelName = generateChannelName(currentUserId, recipientId);

            document.querySelector('.user-chat-topbar').classList.remove('d-none');
            document.querySelector('.chat-input-section').classList.remove('d-none');

            messageTextarea.focus();
            messageTextarea.value = "";

            /**
             * Subscribe to the Channel:
             * Modify a channel and handle the list of online users
             * https://laravel.com/docs/11.x/broadcasting#joining-presence-channels
             */
            console.log("Subscribing to channel:", channelName);
            window.Echo.join(channelName)
                .here((users) => {
                    console.log("Currently online users in this channel:", users);
                    // For now the max is only 2

                    users.forEach(user => {
                        statusBetweenUsers[user.id] = user;
                        updateMessageBadgeStatus(user.id);
                    });

                })
                .joining((user) => {
                    console.log(user.name + " has joined the channel.");

                    statusBetweenUsers[user.id] = user;

                    updateMessageBadgeStatus(user.id);

                    addUserToOnlineList(user);
                })
                .leaving((user) => {
                    console.log(user.name + " has left the channel.");

                    delete statusBetweenUsers[user.id];

                    removeUserFromOnlineList(user);
                })
                .listen(".chat.messages", (event) => {
                    console.log("Received message:", event);
                })
                .error((error) => {
                    console.error(error);
                });


            /**
             * Event listener for the send message button
             */
            sendMessageButton.addEventListener("click", function (event) {
                event.preventDefault();

                var message = messageTextarea.value.trim();

                if (!message) {
                    chatInputfeedback.innerHTML = 'Escreva a mensagem';
                    chatInputfeedback.classList.add("show");
                    setTimeout(function () {
                        chatInputfeedback.classList.remove("show");
                        chatInputfeedback.innerHTML = '';
                    }, 2000);

                    messageTextarea.focus();

                    return;
                }

                if (message && recipientId) {
                    // Send the message to the server.
                    storeMessage(message, recipientId);

                    return;
                }
            });

            /**
             * Sends a new message to the server via AJAX.
             * @param {string} message - The message to send.
             * @param {int} recipientId - The ID of the recipient.
             */
            function storeMessage(message, recipientId) {
                //console.log('Sending message: ', message);

                if (message && recipientId) {
                    fetch(chatStoreURL, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": xCSRFtoken,
                        },
                        body: JSON.stringify({
                            message: message,
                            recipient_id: parseInt(recipientId),
                        })
                    })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(
                                "Failed to send message with status code: " +
                                    response.status
                            );
                        }
                        return response.json();
                    })
                    .then((data) => {
                        console.log('storeMessage:', data);

                        if (!data.success) {
                            // Flag and message in case of validation failure
                            if (data.error) {
                                console.error("Validation Error:", data.error);

                                // Display the error to the user, adjust selector as needed
                                chatInputfeedback.textContent = data.error;
                            } else if (data.errors) {
                                // Handle multiple validation errors (e.g., Laravel validation errors)
                                // Join all error messages into a single string to display
                                const allErrors = Object.values(data.errors)
                                    .map((errorArray) => errorArray.join(", "))
                                    .join("; ");
                                console.error("Validation Errors:", allErrors);

                                chatInputfeedback.textContent = allErrors;
                            }
                            chatInputfeedback.classList.add("show");
                        } else {
                            // Handle success case
                            // console.log('Success:', data.message);

                            messageTextarea.value = ""; // Reset textarea after successful sending
                            chatInputfeedback.innerHTML = ""; // Reset error
                            chatInputfeedback.classList.remove("show");
                        }
                        return;
                    })
                    .catch((error) => {
                        console.error("Fetch Error:", error.message);

                        chatInputfeedback.textContent = "Ocorreu um erro ao enviar a mensagem";

                        chatInputfeedback.classList.add("show");
                    });
                }
            }

            /**
             * Fetches messages from the server for a given recipient and page number.
             * @param {int} recipientId - The ID of the recipient for whom messages are to be fetched.
             * @param {int} page - The page number of the message history to fetch.
             * @returns {Promise} A promise that resolves when messages are successfully fetched and processed.
             */
            function retrieveMessages(recipientId, page = 1) {
                if (recipientId) {
                    showPreloader();

                    if (isLoadingOldMessages) return;
                    isLoadingOldMessages = true;

                    fetch(chatRetrieveURL, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": xCSRFtoken,
                        },
                        body: JSON.stringify({
                            recipient_id: parseInt(recipientId),
                            page: parseInt(page),
                        })
                    })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(
                                "Failed to get messages with status code: " +
                                    response.status
                            );
                        }
                        return response.json();
                    })
                    .then((data) => {
                        console.log('Listen retrieveMessages:', data);
                        showPreloader(false);

                        if (!data || !data.messages || !data.messages.length) {
                            return;
                        }

                        data.messages.forEach((message) => {
                            populateChat(
                                message.id,
                                message.message,
                                message.sender_id,
                                message.sender_name,
                                message.sender_avatar,
                                message.timestamp,
                                message.is_read,
                                true
                            );
                        });

                        if (!page || page === 1) {
                            scrollToBottom();
                        }

                        if (data.current_page >= data.last_page) {
                            // Stop listener if there are no more pages to load
                            return;
                        }
                        isLoadingOldMessages = false;

                        return true;
                    })
                    .catch((error) => {
                        console.error("Error fetching old messages:", error);
                        isLoadingOldMessages = false;
                    });
                }
            }

            /**
             * Populate messages to the chat container
             * @param {int} messageId
             * @param {string} message
             * @param {int} senderId
             * @param {string} senderName
             * @param {string} timestamp
             * @param {boolean} isRead
             * @param {boolean} [prepend=false]
             */
            function populateChat(messageId, message, senderId, senderName, senderAvatar, timestamp, isRead, prepend = false) {
                //console.log('populateChat:', messageId, message, senderId, senderName, senderAvatar, timestamp, isRead, prepend);
                const formattedDate = formatDateTime(timestamp);

                var readIcon = isRead ? 'ri-check-double-fill text-success' : 'ri-check-line text-mutted';

                let conversationElements = '';

                var newMessage = document.createElement("li");
                newMessage.setAttribute("id", messageId);
                newMessage.classList.add('chat-list');
                newMessage.classList.add(
                    senderId == currentUserId ? "right" : "left"
                );

                // Add break lines to the message
                var messageBreaks = message.replace(/\n/g, '<br>');

                if (senderId == currentUserId) {
                    conversationElements = `<div class="conversation-list">
                        <div class="user-chat-content">
                            <div class="ctext-wrap">
                                <div class="ctext-wrap-content" id="${messageId}">
                                    <p class="mb-0 ctext-content">${messageBreaks}</p>
                                </div>
                                <!--
                                <div class="dropdown align-self-start message-box-drop">
                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item reply-message" href="#"><i class="ri-reply-line me-2 text-muted align-bottom"></i>Reply</a>
                                        <a class="dropdown-item" href="#"><i class="ri-share-line me-2 text-muted align-bottom"></i>Forward</a>
                                        <a class="dropdown-item copy-message" href="#"><i class="ri-file-copy-line me-2 text-muted align-bottom"></i>Copy</a>
                                        <a class="dropdown-item" href="#"><i class="ri-bookmark-line me-2 text-muted align-bottom"></i>Bookmark</a>
                                        <a class="dropdown-item delete-item" href="#"><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>
                                    </div>
                                </div>
                                -->
                            </div>
                            <div class="conversation-name">
                                <span class="d-none name">${senderName}</span>
                                <small class="text-muted time">${formattedDate}</small>
                                <span class="check-message-icon"><i data-message-id="${messageId}" class="status-indicator ${readIcon}"></i></span>
                            </div>
                        </div>
                    </div>`;
                } else {
                    conversationElements = `<div class="conversation-list">
                        <div class="chat-avatar"><img src="${senderAvatar}" alt="Avatar" width="28" height="28"></div>
                        <div class="user-chat-content">
                            <div class="ctext-wrap">
                                <div class="ctext-wrap-content" id="${messageId}">
                                    <p class="mb-0 ctext-content">${messageBreaks}</p>
                                </div>
                                <!--
                                <div class="dropdown align-self-start message-box-drop">
                                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item reply-message" href="#"><i class="ri-reply-line me-2 text-muted align-bottom"></i>Reply</a>
                                        <a class="dropdown-item" href="#"><i class="ri-share-line me-2 text-muted align-bottom"></i>Forward</a>
                                        <a class="dropdown-item copy-message" href="#"><i class="ri-file-copy-line me-2 text-muted align-bottom"></i>Copy</a>
                                        <a class="dropdown-item" href="#"><i class="ri-bookmark-line me-2 text-muted align-bottom"></i>Bookmark</a>
                                        <a class="dropdown-item delete-item" href="#"><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>
                                    </div>
                                </div>
                                -->
                            </div>
                            <div class="conversation-name">
                                <span class="d-none name">${senderName}</span>
                                <small class="text-muted time">${formattedDate}</small>
                                <span class="check-message-icon"><i data-message-id="${messageId}" class="status-indicator ${readIcon}"></i></span>
                            </div>
                        </div>
                    </div>`;
                }

                newMessage.innerHTML = conversationElements;

                if (conversationContainer) {
                    if (prepend) {
                        conversationContainer.insertBefore(newMessage, conversationContainer.firstChild);
                    } else {
                        conversationContainer.appendChild(newMessage);

                        scrollToBottom();
                    }

                    messageTextarea.focus();
                }
            }

            /**
             *Listen for changes on the select element to update the recipient ID
            * @param {int} recipientId
            * @returns
            */
            function updateRecipientId(recipientId) {
                conversationContainer.innerHTML = "";

                // Make the chat-container visible after a user is selected from the dropdown
                if (recipientId) {
                    // Make the chat container visible
                    conversationContainer.style.display = "block";

                    /**
                     * Listening for messages from Broadcast Name
                     * Subscribing to a Channel and Listening for Events
                     * DOC: https://laravel.com/docs/11.x/broadcasting#namespaces
                     *
                     * ChatMessages is the .Namespace\\Event\\Class :
                     *  app\Events\ChatMessages.php
                     * return event from broadcastWith()
                     */
                    window.Echo.private(channelName).listen(
                        ".chat.messages",
                        (event) => {
                            // Log received event and channel name for debugging.
                            console.log('Listen updateRecipientId: ', event);

                            const isRecipientUserOnline =
                                !!statusBetweenUsers[recipientId];
                            console.log("isRecipientUserOnline", isRecipientUserOnline);

                            let isRead = event.is_read;
                            if (isRecipientUserOnline) {
                                isRead = true;
                            }

                            populateChat(
                                event.message_id,
                                event.message,
                                event.sender_id,
                                event.sender_name,
                                event.sender_avatar,
                                event.timestamp,
                                isRead
                            );
                        }
                    );

                    // Fetch and display messages for the new recipient
                    retrieveMessages(recipientId);
                } else {
                    // Hide the chat container if the placeholder is selected again
                    conversationContainer.style.display = "none";

                    return;
                }
            }
            updateRecipientId(recipientId);

            /**
             * Manage the online status display based on the users currently connected to the channel.
             * @param {int} user
             */
            function updateMessageBadgeStatus(user) {
                if (user === recipientId) {
                    // Select all status indicators with the 'ri-check-line' class
                    const statusIndicators = document.querySelectorAll('.status-indicator.ri-check-line');

                    // Iterate through each status indicator and update its class
                    statusIndicators.forEach(indicator => {
                        indicator.classList.remove('ri-check-line', 'text-mutted');
                        indicator.classList.add('ri-check-double-fill', 'text-success');
                    });
                }
            }

            /**
             * Adds a user to the online list in the UI, indicating their presence in the chat.
             * @param {Object} user - The user to add.
             */
            function addUserToOnlineList(user) {
                // TODO Add user to the online list in UI
            }

            /**
             * Removes a user from the online list in the UI, indicating they have left the chat.
             * @param {Object} user - The user to remove.
             */
            function removeUserFromOnlineList(user) {
                // TODO Remove user from the online list in UI
            }

            /**
             * Generates a unique channel name based on user IDs to ensure privacy and correct message routing.
             * @param {int} userId1 - The first user ID.
             * @param {int} userId2 - The second user ID.
             * @returns {string} The generated channel name.
             */
            function generateChannelName(userId1, userId2) {
                return `chat-channel.${[userId1, userId2].sort((a, b) => a - b).join("_")}`;
            }


            /**
             * Scrolls the messages container smoothly to the bottom.
             * @param {number} time - The duration in milliseconds to delay the scroll, allowing for DOM updates.
             */
            function scrollToBottom(time = 100) {
                setTimeout(() => {
                    if (messageContainer) {
                        messageContainer.scrollTo({
                            top: messageContainer.scrollHeight,
                            behavior: "smooth"
                        });
                    }
                }, time);
            }


            /**
             * Helper Debounce function to limit the rate at which a function can fire.
             * @param {Function} func - Function to execute.
             * @param {number} wait - The time to delay in milliseconds.
             * @param {boolean} immediate - Trigger the function on the leading edge, instead of the trailing.
             * @returns {Function} A debounced version of the passed function.
             */
            function debounce(func, wait, immediate) {
                var timeout;
                return function () {
                    var context = this,
                        args = arguments;
                    var later = function () {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (immediate && !timeout) func.apply(context, args);
                };
            }

            /**
             * Event debounced listener to paginate messages when scrolling to the top of the container
             */
            messageContainer.addEventListener(
                "scroll",
                debounce(async function () {
                    // Check if the user is scrolling up and has reached the top of the container
                    if (this.scrollTop === 0 && !isLoadingOldMessages) {
                        currentPage++;
                        try {
                            await retrieveMessages(recipientId, currentPage);
                            this.scrollTo({
                                top: 10,
                                behavior: "smooth"
                            });
                        } catch (error) {
                            console.error("Failed to load messages:", error);
                        }
                    }
                }, 250)
            );
        }
    }

});

document.addEventListener("DOMContentLoaded", searchUser);
