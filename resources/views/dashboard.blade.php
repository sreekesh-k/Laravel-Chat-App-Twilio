<x-app-layout>
    <div id="messages" style="overflow-y: scroll; max-height:80svh; position: relative;;">
        <!-- Example messages -->
        <div class="message sent">
            This is a sent message.
        </div>
        <div class="message received">
            This is a received message.
        </div>
    </div>
    <form id="messageForm" action="/chat-app/send-message" method="POST">
        @csrf
        <input type="text" name="message" placeholder="Type your message" required>
        <button type="submit">Send</button>
    </form>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <script src="https://sdk.twilio.com/js/conversations/v1.0/twilio-conversations.min.js"></script>
    <script>
        document.getElementById("messageForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const messageInput = this.querySelector('input[name="message"]');
            const message = messageInput.value;

            fetch("/chat-app/send-message", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({
                        message
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = "";
                        loadMessages();
                    }
                })
                .catch(error => console.error("Error:", error));
        });

        let twilioConversation;

        function connectToTwilio() {
            fetch("/chat-app/generate-token")
                .then(response => response.json())
                .then(data => {
                    const accessToken = data.token;
                    const conversationSid = "{{ env('TWILIO_CHAT_SID') }}";

                    Twilio.Conversations.Client.create(accessToken)
                        .then(client => client.getConversationBySid(conversationSid))
                        .then(conversation => {
                            twilioConversation = conversation;
                            document.getElementById("messages").innerHTML = "";
                            loadMessages();
                            conversation.on("messageAdded", message => {
                                displayMessage(message);
                                scrollToEnd();
                            });
                        })
                        .catch(error => console.error("Error connecting to Twilio:", error));
                })
                .catch(error => console.error("Error fetching token:", error));
        }

        function loadMessages() {
            if (!twilioConversation) return;

            twilioConversation.getMessages()
                .then(messages => {
                    const messagesDiv = document.getElementById("messages");
                    messagesDiv.innerHTML = "";
                    messages.items.forEach(message => displayMessage(message));
                    scrollToEnd(); // Scroll to the end after loading messages
                })
                .catch(error => console.error("Error loading messages:", error));
        }

        function displayMessage(message) {
            const messagesDiv = document.getElementById("messages");
            const messageElement = document.createElement("div");
            const loggedInUserId = "{{ Auth::user()->name }}";

            if (message.state.author === loggedInUserId) {
                messageElement.classList.add("message", "sent");
            } else {
                messageElement.classList.add("message", "received");
            }

            const apiDate = message.state.dateUpdated;
            const day = String(apiDate.getDate()).padStart(2, '0');
            const month = String(apiDate.getMonth() + 1).padStart(2, '0');
            const year = String(apiDate.getFullYear()).slice(-2);
            const hours = String(apiDate.getHours()).padStart(2, '0');
            const minutes = String(apiDate.getMinutes()).padStart(2, '0');

            const formattedDate = `${day}-${month}-${year}`;
            const time = `${hours}:${minutes}`;
            messageElement.innerHTML = `<h2>${message.state.author}</h2>
                                        ${message.state.body}<br>
                                           <Small>${time}</Small>`;
            messagesDiv.appendChild(messageElement);
        }

        function scrollToEnd() {
            const messagesDiv = document.getElementById("messages");
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        window.onload = function() {
            connectToTwilio();
        };
    </script>
</x-app-layout>
