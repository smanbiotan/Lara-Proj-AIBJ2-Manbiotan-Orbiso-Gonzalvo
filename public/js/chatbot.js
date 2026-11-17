/* =========================================================================
   Blush & Bloom Salon — floating AI assistant
   The browser sends messages to Laravel (/api/chat). Laravel forwards them to
   n8n + Google Gemini using server-side secrets. No API keys exist in this file.
   ========================================================================= */
(function () {
    'use strict';

    const widget = document.getElementById('chatbot');
    if (!widget) return;

    const endpoint = widget.dataset.endpoint;
    const panel = document.getElementById('chatbotPanel');
    const launcher = document.getElementById('chatbotLauncher');
    const closeBtn = document.getElementById('chatbotClose');
    const form = document.getElementById('chatbotForm');
    const input = document.getElementById('chatbotInput');
    const messages = document.getElementById('chatbotMessages');
    const sendBtn = form.querySelector('button[type="submit"]');
    let busy = false;

    // A random conversation ID per browser tab, so n8n can keep short-term memory.
    const sessionKey = 'bb-chat-session';
    let sessionId = null;
    try { sessionId = sessionStorage.getItem(sessionKey); } catch (e) { /* ignore */ }
    if (!sessionId) {
        sessionId = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : 'bb-' + Date.now().toString(36) + Math.random().toString(36).slice(2, 12);
        try { sessionStorage.setItem(sessionKey, sessionId); } catch (e) { /* ignore */ }
    }

    const setOpen = (open) => {
        panel.hidden = !open;
        widget.classList.toggle('is-open', open);
        document.body.classList.toggle('chat-open', open);
        launcher.setAttribute('aria-expanded', String(open));
        launcher.setAttribute('aria-label', open ? 'Close chat with Bloomie' : 'Chat with Bloomie');
        if (open) setTimeout(() => input.focus(), 60);
    };

    const addMessage = (text, type, note) => {
        const el = document.createElement('div');
        el.className = 'chat-msg chat-msg-' + type;
        el.textContent = text; // textContent prevents HTML/script injection
        if (note) {
            const small = document.createElement('small');
            small.className = 'chat-msg-note';
            small.textContent = note;
            el.appendChild(small);
        }
        messages.appendChild(el);
        messages.scrollTop = messages.scrollHeight;
        return el;
    };

    const addTyping = () => {
        const el = document.createElement('div');
        el.className = 'chat-msg chat-msg-bot chat-typing';
        el.setAttribute('aria-label', 'Assistant is typing');
        el.innerHTML = '<span></span><span></span><span></span>';
        messages.appendChild(el);
        messages.scrollTop = messages.scrollHeight;
        return el;
    };

    const send = async (text) => {
        const message = text.trim();
        if (!message || busy) return;
        busy = true;
        sendBtn.disabled = true;
        addMessage(message, 'user');
        input.value = '';
        const typing = addTyping();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ message: message, session_id: sessionId })
            });
            const data = await response.json().catch(() => ({}));
            typing.remove();

            if (response.status === 429) {
                addMessage('You are sending messages quickly. Please wait a moment and try again.', 'error');
            } else if (!response.ok) {
                addMessage(data.message || 'Sorry, I could not process that. Please try again.', 'error');
            } else {
                if (data.session_id) sessionId = data.session_id;
                addMessage(data.reply, 'bot', data.mode === 'fallback' ? 'Quick answer from our salon information (Bloomie is offline)' : null);
            }
        } catch (error) {
            typing.remove();
            addMessage('The assistant is unavailable right now. You can still book online or use our Contact page.', 'error');
        } finally {
            busy = false;
            sendBtn.disabled = false;
            input.focus();
        }
    };

    launcher.addEventListener('click', () => setOpen(panel.hidden));
    closeBtn.addEventListener('click', () => { setOpen(false); launcher.focus(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) { setOpen(false); launcher.focus(); }
    });
    form.addEventListener('submit', (e) => { e.preventDefault(); send(input.value); });
    widget.querySelectorAll('[data-chat-suggestion]').forEach((btn) => btn.addEventListener('click', () => send(btn.textContent)));

    // Any element with data-open-chat (e.g. on the Home page) opens the assistant.
    document.querySelectorAll('[data-open-chat]').forEach((btn) => btn.addEventListener('click', () => setOpen(true)));
})();
