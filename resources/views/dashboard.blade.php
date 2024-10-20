<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div id="messages" style="overflow-y: scroll; max-height:70svh;">
        <!-- Example messages -->
        <div class="message sent">
            This is a sent message.
        </div>
        <div class="message received">
            This is a received message.
        </div>
    </div>
    <form id="messageForm" action="/send-message" method="POST">
        @csrf
        <input type="text" name="message" placeholder="Type your message" required>
        <button type="submit">Send</button>
    </form>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <script src="https://sdk.twilio.com/js/conversations/v1.0/twilio-conversations.min.js"></script>
    <script>
        document.getElementById("messageForm").addEventListener("submit", function(e) {
            e.preventDefault(); // Prevent the form from submitting normally

            const messageInput = this.querySelector('input[name="message"]');
            const message = messageInput.value;

            fetch("/chat-app/send-message", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}", // Use Blade to insert the CSRF token
                    },
                    body: JSON.stringify({
                        message,
                    }), // Send the message as JSON
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        // Clear the input after sending the message
                        messageInput.value = "";
                        // Load messages again after sending
                        loadMessages();
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        });

        let twilioConversation;

        function connectToTwilio() {
            fetch("/chat-app/generate-token")
                .then((response) => response.json())
                .then((data) => {
                    const accessToken = data.token; // Get the generated token
                    const conversationSid =
                    "{{ env('TWILIO_CHAT_SID') }}"; // Replace with your actual environment variable name

                    Twilio.Conversations.Client.create(accessToken)
                        .then((client) => {
                            // console.log('Twilio Client created:', client);
                            return client.getConversationBySid(conversationSid);
                        })
                        .then((conversation) => {
                            twilioConversation = conversation; // Store the conversation reference
                            // Load messages on initial load
                            const messagesDiv = document.getElementById("messages");
                            messagesDiv.innerHTML = "";
                            loadMessages();
                            conversation.on("messageAdded", (message) => {
                                displayMessage(message);
                            });
                        })
                        .catch((error) => {
                            console.error("Error connecting to Twilio:", error);
                        });
                })
                .catch((error) => {
                    console.error("Error fetching token:", error);
                });
        }

        function loadMessages() {
            if (!twilioConversation) return; // Ensure the conversation is defined

            twilioConversation
                .getMessages()
                .then((messages) => {
                    const messagesDiv = document.getElementById("messages");
                    messagesDiv.innerHTML = ""; // Clear existing messages

                    messages.items.forEach((message) => {
                        displayMessage(message);
                    });
                })
                .catch((error) => {
                    console.error("Error loading messages:", error);
                });
        }

        function displayMessage(message) {
            const messagesDiv = document.getElementById("messages");
            const messageElement = document.createElement("div");

            // Assuming you have a way to identify the logged-in user's ID
            const loggedInUserId = "{{ Auth::user()->email }}"; // Blade directive to get logged-in user ID

            // Set message class based on the author
            if (message.state.author === loggedInUserId) {
                messageElement.classList.add("message", "sent"); // Message sent by the user
            } else {
                messageElement.classList.add("message", "received"); // Message received from others
            }

            messageElement.innerHTML = `<h2>${message.state.author}</h2>
                                        ${message.state.body}`;
            messagesDiv.appendChild(messageElement);
        }

        window.onload = function() {
            connectToTwilio();
        };
    </script>
</x-app-layout>
