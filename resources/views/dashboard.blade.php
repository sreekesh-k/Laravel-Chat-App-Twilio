<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <form id="messageForm" action="/send-message" method="POST">
        @csrf
        <input type="text" name="message" placeholder="Type your message" required>
        <button type="submit">Send</button>
    </form>

    <div id="messages">
        <!-- Messages will be displayed here -->
    </div>

    <script>
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the form from submitting normally

            const messageInput = this.querySelector('input[name="message"]');
            const message = messageInput.value;

            fetch('/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', // Use Blade to insert the CSRF token
                    },
                    body: JSON.stringify({
                        message
                    }), // Send the message as JSON
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear the input after sending the message
                        messageInput.value = '';
                        // Optionally, refresh the messages displayed
                        loadMessages(); // You will implement this function to refresh messages
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });

        function loadMessages() {
            fetch('/messages')
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Check the structure in the console
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = ''; // Clear existing messages

                    data.forEach(msg => {
                        const messageElement = document.createElement('div');
                        messageElement.textContent =
                        `${msg.author}: ${msg.body}`; // Make sure these keys match your data structure
                        messagesDiv.appendChild(messageElement);
                    });
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                });
        }

        setInterval(loadMessages, 5000);
    </script>

</x-app-layout>
