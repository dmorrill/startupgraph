<div x-data="{ open: false, sent: false, error: false, sending: false }" class="fixed bottom-6 right-6 z-50">
    {{-- Trigger button --}}
    <button @click="open = !open; sent = false; error = false"
            x-show="!open"
            class="bg-gray-800 text-white px-4 py-2 rounded-full shadow-lg hover:bg-gray-700 text-sm font-medium transition">
        💬 Feedback
    </button>

    {{-- Feedback form --}}
    <div x-show="open" x-cloak
         x-transition
         class="bg-white border shadow-xl rounded-lg w-80 p-4">

        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-gray-900 text-sm">Share feedback</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <template x-if="!sent">
            <form @submit.prevent="
                sending = true; error = false;
                fetch('{{ route('feedback.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: $refs.msg.value,
                        page_url: window.location.href
                    })
                }).then(r => {
                    sending = false;
                    if (r.ok) { sent = true } else { error = true }
                }).catch(() => { sending = false; error = true })
            ">
                <textarea x-ref="msg" name="message" rows="3" required maxlength="2000"
                          placeholder="What would make StartupGraph more useful for you?"
                          class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-gray-500 focus:border-gray-500"></textarea>
                <p x-show="error" class="text-red-500 text-xs mt-1">Something went wrong. Please try again.</p>
                <button type="submit" :disabled="sending"
                        class="mt-2 w-full bg-gray-800 text-white text-sm font-medium py-2 rounded-md hover:bg-gray-700 disabled:opacity-50">
                    <span x-text="sending ? 'Sending...' : 'Send'"></span>
                </button>
            </form>
        </template>

        <template x-if="sent">
            <div class="text-center py-4">
                <p class="text-green-600 font-medium text-sm">Thanks! Your feedback helps us improve. 🙏</p>
                <button @click="open = false" class="mt-2 text-gray-500 text-xs underline">Close</button>
            </div>
        </template>
    </div>
</div>
