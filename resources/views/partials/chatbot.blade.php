{{-- Floating AI assistant. The browser only calls Laravel's /api/chat; n8n + Gemini credentials stay on the server. --}}
<div class="chatbot" id="chatbot" data-endpoint="{{ url('/api/chat') }}">
    <section class="chatbot-panel" id="chatbotPanel" role="dialog" aria-modal="false" aria-labelledby="chatbotTitle" hidden>
        <header class="chatbot-head">
            <div class="chatbot-identity">
                <span class="chatbot-avatar">@include('partials.logo', ['class' => 'chatbot-avatar-icon'])</span>
                <span>
                    <strong id="chatbotTitle">Bloomie</strong>
                    <small><i class="chatbot-dot"></i> Blush &amp; Bloom virtual assistant</small>
                </span>
            </div>
            <button class="chatbot-close" id="chatbotClose" type="button" aria-label="Close assistant">×</button>
        </header>

        <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
            <div class="chat-msg chat-msg-bot">Hi! I'm Bloomie, the Blush &amp; Bloom Salon virtual assistant. How can I help you today?</div>
        </div>

        <div class="chatbot-suggestions" id="chatbotSuggestions" aria-label="Suggested questions">
            @foreach(['What services do you offer?', 'How much is a haircut?', 'How long does a facial treatment take?', 'What are your salon hours?', 'Can I book an appointment?', 'What appointment schedules are available?'] as $q)
                <button type="button" data-chat-suggestion>{{ $q }}</button>
            @endforeach
        </div>

        <form class="chatbot-form" id="chatbotForm">
            <label class="sr-only" for="chatbotInput">Type your message</label>
            <input id="chatbotInput" type="text" maxlength="1000" placeholder="Type your message…" autocomplete="off" required>
            <button type="submit" aria-label="Send message">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M3 11.5 21 3l-8.5 18-2-7.5-7.5-2Z" fill="currentColor"/></svg>
            </button>
        </form>
        <p class="chatbot-foot">AI answers may be imperfect. Bookings are confirmed by the salon.</p>
    </section>

    <button class="chatbot-launcher" id="chatbotLauncher" type="button" aria-expanded="false" aria-controls="chatbotPanel" aria-label="Chat with Bloomie">
        <svg class="icon-open" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H10l-4.5 4v-4h0A2.5 2.5 0 0 1 4 13.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.5 9.5h7M8.5 12.5h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        <svg class="icon-close" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
</div>
