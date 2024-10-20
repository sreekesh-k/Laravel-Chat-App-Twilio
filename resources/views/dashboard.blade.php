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
                body: JSON.stringify({ message }), // Send the message as JSON
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

        // Function to load messages (to be implemented)
        function loadMessages() {
            // You can fetch and display messages from the server
        }
    </script>

</x-app-layout>
