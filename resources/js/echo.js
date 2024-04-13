
import Echo from '../libs/laravel-echo/echo.js';
import Pusher from '../libs/pusher-js/web/pusher.js';

window.Pusher = Pusher;

const xCSRFtoken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

/**
 * Setup Echo and global variables
 * Initializes Echo with Pusher as the broadcasting driver and sets CSRF token for security.
 * DOC: https://laravel.com/docs/11.x/broadcasting#client-reverb
 */
window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
    encrypted: true,
    auth: {
        headers: {
            "X-CSRF-TOKEN": xCSRFtoken,
        },
    },
});

const currentUserId = parseInt(
    document .querySelector('meta[name="current-user-id"]').getAttribute("content")
);
const userList = document.querySelector('#userList input[name="user_chat"]');

document.addEventListener("DOMContentLoaded", function () {

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

                updateUsersDropdown();
            })
            .joining((user) => {
                console.log(`${user.name} has joined.`);

                onlineUsers.push(user.id);
                console.log('Online users:', onlineUsers);

                updateUsersDropdown();
            })
            .leaving((user) => {
                console.log(`${user.name} has left.`);

                onlineUsers = onlineUsers.filter(id => id !== user.id);
                console.log('Online users:', onlineUsers);

                updateUsersDropdown();
            });

        /**
         * Updates the dropdown to reflect the online status of users.
         */
        function updateUsersDropdown() {
            const select = document.getElementById('select-user');
            const options = select.options;

            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                const userId = parseInt(option.value);

                // Check if the option has a valid user ID
                if (userId) {
                    if (onlineUsers.includes(userId)) {
                        option.style.color = 'green'; // Set online users to green
                    } else {
                        option.style.color = 'red'; // Set offline users to red
                    }
                }
            }
        }


        /**
         * Get the checked radio value
         */
        function getCheckedRadioValue() {
            let checkedRadio = document.querySelector('#userList input[type="radio"]:checked');
            return checkedRadio ? checkedRadio.value : null;
        }

        /**
         * Add event listeners to each radio button
         */
        userList.forEach(radio => {
            radio.addEventListener("change", function() {
                const recipientId = getCheckedRadioValue();
                console.log("Checked recipient ID:", recipientId);

                //window.location.reload();
            });
        });


        /**
         * This part initialize chat components.
         */
        initializeChat(userList);
    }
});

/**
 * Initializes the chat application by setting up channel listeners and message sending capabilities.c
 */
function initializeChat(userList) {
    // Initialize variables.
    let currentPage = 1;
    let isLoadingOldMessages = false;

    // Global state to track status between two users
    let statusBetweenUsers = {};

    const checkedRadio = userList.checked;
    const recipientId = checkedRadio ? checkedRadio.value : null;

    const channelName = generateChannelName(currentUserId, recipientId);

    const chatContainer = document.getElementById("chat-container");
    const messagesContainer = document.querySelector(
        "#chat-container ul#messages-container"
    );
    const sendMessageButton = document.getElementById("send-message");
    const messageTextarea = document.getElementById("message-textarea");
    const errorContainer = document.getElementById("error-container");

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

            users.forEach((user) => (statusBetweenUsers[user.id] = user));
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
    sendMessageButton.addEventListener("click", function () {
        var message = messageTextarea.value.trim();

        if (message && recipientId) {
            // Send the message to the server.
            storeMessage(message, recipientId);
        }
    });

    /**
     * Sends a new message to the server via AJAX.
     * @param {string} message - The message to send.
     * @param {int} recipientId - The ID of the recipient.
     */
    function storeMessage(message, recipientId) {
        // console.log('Sending message: ', message);

        if (message && recipientId) {
            fetch("/store-message", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": xCSRFtoken,
                },
                body: JSON.stringify({
                    message: message,
                    recipient_id: parseInt(recipientId),
                }),
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
                if (!data.success) {
                    // Flag and message in case of validation failure
                    if (data.error) {
                        console.error("Validation Error:", data.error);

                        // Display the error to the user, adjust selector as needed
                        errorContainer.textContent = data.error;
                    } else if (data.errors) {
                        // Handle multiple validation errors (e.g., Laravel validation errors)
                        // Join all error messages into a single string to display
                        const allErrors = Object.values(data.errors)
                            .map((errorArray) => errorArray.join(", "))
                            .join("; ");
                        console.error("Validation Errors:", allErrors);

                        errorContainer.textContent = allErrors;
                    }
                } else {
                    // Handle success case
                    // console.log('Success:', data.message);

                    messageTextarea.value = ""; // Reset textarea after successful sending

                    errorContainer.innerHTML = ""; // Reset error
                }
            })
            .catch((error) => {
                console.error("Fetch Error:", error.message);

                errorContainer.textContent =
                    "An error occurred while sending the message.";
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
            if (isLoadingOldMessages) return;
            isLoadingOldMessages = true;

            fetch(`/get-messages/?page=${page}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": xCSRFtoken,
                },
                body: JSON.stringify({
                    recipient_id: parseInt(recipientId),
                }),
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
                //console.log('Messages:', data);

                var Data = data.data;
                Data.forEach((message) => {
                    populateChat(
                        message.id,
                        message.message,
                        message.sender_id,
                        message.sender_name,
                        message.timestamp,
                        message.is_read,
                        true
                    );
                });

                if (!page || page === 1) {
                    scrollToBottom();
                }

                if (data.current_page < data.last_page) {
                    currentPage++;
                } else {
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
    function populateChat(messageId, message, senderId, senderName, timestamp, isRead, prepend = false) {
        var formattedDate = formatDateTime(timestamp);
        var newMessage = document.createElement("li");
        newMessage.classList.add(
            senderId == currentUserId ? "sender" : "recipient"
        );

        // Sender's name
        var strongElement = document.createElement("strong");
        strongElement.textContent = `${senderName}`;
        newMessage.appendChild(strongElement);

        // Message text, replacing newlines with spaces for inline display
        var messageDiv = document.createElement("div");
        messageDiv.classList.add("message-container");
        var messageParts = message.split("\n");
        messageParts.forEach((part, index) => {
            if (index > 0) {
                messageDiv.appendChild(document.createElement("br")); // Add line breaks between parts
            }
            messageDiv.appendChild(document.createTextNode(part));
        });
        newMessage.appendChild(messageDiv);

        // Date and time
        var dateTimeElement = document.createElement("span");
        dateTimeElement.classList.add("datetime-badge");
        dateTimeElement.textContent = ` ${formattedDate}`;
        newMessage.appendChild(dateTimeElement);

        // Status indicator
        if (currentUserId == senderId) {
            var statusElement = document.createElement("span");
            statusElement.setAttribute("data-message-id", messageId);
            statusElement.classList.add(
                "status-indicator",
                isRead ? "read" : "unread"
            );
            newMessage.appendChild(statusElement);
        }

        // Decide whether to append or prepend the message based on the 'prepend' flag
        if (prepend) {
            messagesContainer.insertBefore(
                newMessage,
                messagesContainer.firstChild
            );
        } else {
            messagesContainer.appendChild(newMessage);
            scrollToBottom();
        }

        messageTextarea.focus();
    }

    /**
     *Listen for changes on the select element to update the recipient ID
     * @param {int} recipientId
     * @returns
     */
    function updateRecipientId(recipientId) {
        messagesContainer.innerHTML = "";

        // Make the chat-container visible after a user is selected from the dropdown
        if (recipientId) {
            // Make the chat container visible
            chatContainer.style.display = "block";

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
                    //console.log('Listen: ', event);

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
                        event.timestamp,
                        isRead
                    );
                }
            );

            // Fetch and display messages for the new recipient
            retrieveMessages(recipientId);
        } else {
            // Hide the chat container if the placeholder is selected again
            chatContainer.style.display = "none";

            return;
        }
    }

    /**
     * Manage the online status display based on the users currently connected to the channel.
     * @param {int} user
     */
    function updateMessageBadgeStatus(user) {
        if (user === recipientId) {
            // Select all elements with the 'status-indicator unread' class within messages from the specified user
            const statusIndicators = document.querySelectorAll(
                `.status-indicator.unread`
            );

            // Iterate through each status indicator and update its class
            statusIndicators.forEach((indicator) => {
                indicator.classList.remove("unread");
                indicator.classList.add("read");
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
     * Helper to format dates
     * @param {string} dateTime
     * @returns
     */
    function formatDateTime(dateTime) {
        return new Date(dateTime)
            .toLocaleString("en-GB", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            })
            .replace(",", "");
    }

    /**
     * Scrolls the messages container smoothly to the bottom.
     * @param {number} time - The duration in milliseconds to delay the scroll, allowing for DOM updates.
     */
    function scrollToBottom(time = 100) {
        setTimeout(() => {
            const messagesContainer =
                document.getElementById("messages-container");
            if (messagesContainer) {
                messagesContainer.scrollTo({
                    top: messagesContainer.scrollHeight,
                    behavior: "smooth"
                });
            }
        }, time);
    }

    /**
     * Call updateRecipientId on page load to set the initial recipient ID
     * @param {int} recipientId
     */
    document.addEventListener(
        "DOMContentLoaded",
        updateRecipientId(recipientId)
    );

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
    document.querySelector("#messages-container").addEventListener(
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
