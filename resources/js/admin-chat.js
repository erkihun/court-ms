import axios from 'axios';

const container = document.getElementById('admin-chat-messages');
const form = document.getElementById('admin-chat-form');
const input = form?.querySelector('textarea[name="message"]');
const contactCards = document.querySelectorAll('[data-chat-user-id]');
const headerNameEl = document.querySelector('[data-chat-header-name]');
const headerHandleEl = document.querySelector('[data-chat-header-handle]');
const headerInitialsEl = document.querySelector('[data-chat-header-initials]');
const recipientInput = form?.querySelector('input[name="recipient_user_id"]');
const conversationUrlTemplate = window.ADMIN_CHAT_CONVERSATION_URL_TEMPLATE ?? '';
let currentRecipientId = window.ADMIN_CHAT_INITIAL_RECIPIENT_ID ? String(window.ADMIN_CHAT_INITIAL_RECIPIENT_ID) : (recipientInput?.value ? String(recipientInput?.value) : null);

const scrollToBottom = () => {
    if (!container) return;
    container.scrollTop = container.scrollHeight;
};

const messageBelongsToConversation = (payload) => {
    if (!payload.recipient_id || !currentRecipientId) {
        return true;
    }

    const fromSelf = Number(payload.sender_id) === Number(window.ADMIN_CHAT_CURRENT_USER_ID);
    const counterpartId = fromSelf ? String(payload.recipient_id) : String(payload.sender_id);
    return counterpartId === String(currentRecipientId);
};

const renderMessage = (payload, options = {}) => {
    if (!options.force && !messageBelongsToConversation(payload)) {
        return;
    }

    const row = document.createElement('div');
    const fromSelf = Number(payload.sender_id) === Number(window.ADMIN_CHAT_CURRENT_USER_ID);
    row.className = `flex ${fromSelf ? 'justify-end' : 'justify-start'}`;

    const bubble = document.createElement('div');
    bubble.className = `max-w-[78%] rounded-2xl border px-4 py-3 shadow-sm transition ${fromSelf ? 'bg-blue-50 border-blue-200 text-right' : 'bg-white border-gray-200'}`;

    const meta = document.createElement('div');
    meta.className = 'flex items-center justify-between text-xs text-gray-500 mb-1';
    meta.innerHTML = `
        <span class="font-medium text-gray-900">${payload.sender_name || window.ADMIN_CHAT_SYSTEM_LABEL}</span>
        <span>${new Date(payload.created_at).toLocaleTimeString()}</span>
    `;

    const body = document.createElement('p');
    body.className = 'text-sm text-gray-800 whitespace-pre-wrap break-words';
    body.textContent = payload.message;

    bubble.appendChild(meta);
    bubble.appendChild(body);
    row.appendChild(bubble);

    if (options.prepend) {
        container.prepend(row);
    } else {
        container.appendChild(row);
        scrollToBottom();
    }
};

const showMessages = (messages = []) => {
    if (!container) return;
    container.innerHTML = '';
    messages.forEach((message) => renderMessage(message, { prepend: false, force: true }));
    scrollToBottom();
};

const fetchConversation = async (userId) => {
    if (!userId || !conversationUrlTemplate || !container) return;
    const url = conversationUrlTemplate.replace('__USER__', encodeURIComponent(userId));
    try {
        const response = await axios.get(url);
        const messages = response.data?.messages ?? [];
        currentRecipientId = String(userId);
        showMessages(messages);
    } catch (error) {
        console.error('Failed to load conversation', error);
    }
};

if (container && window.ADMIN_CHAT_MESSAGES) {
    showMessages(window.ADMIN_CHAT_MESSAGES);
}

if (form && input) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const value = input.value.trim();
        if (!value) {
            return;
        }

        const recipientId = recipientInput?.value;
        if (!recipientId) {
            input.focus();
            return;
        }

        input.disabled = true;

        try {
            const response = await axios.post(form.action, {
                message: value,
                recipient_user_id: recipientInput?.value,
            });
            renderMessage({
                ...response.data.message,
                created_at: response.data.message.created_at ?? new Date().toISOString(),
            });
            input.value = '';
        } catch (error) {
            console.error(error);
        } finally {
            input.disabled = false;
            input.focus();
        }
    });
}

if (window.Echo) {
    window.Echo.channel('admin-chat')
        .listen('.AdminChatMessageSent', (event) => {
            renderMessage(event, { prepend: false });
        });
}

const selectUserCard = (card, { skipFetch = false } = {}) => {
    if (!card) return;
    contactCards.forEach((c) => {
        c.classList.remove('bg-slate-100', 'border-slate-200', 'shadow-inner');
        c.classList.add('border-transparent');
        c.setAttribute('aria-pressed', 'false');
    });
    card.classList.remove('border-transparent');
    card.classList.add('bg-slate-100', 'border-slate-200', 'shadow-inner');
    card.setAttribute('aria-pressed', 'true');
    headerNameEl && (headerNameEl.textContent = card.dataset.chatUserName);
    headerHandleEl && (headerHandleEl.textContent = card.dataset.chatUserHandle);
    headerInitialsEl && (headerInitialsEl.textContent = card.dataset.chatUserInitials);
    if (recipientInput) {
        recipientInput.value = card.dataset.chatUserId;
    }
    if (!skipFetch) {
        fetchConversation(card.dataset.chatUserId);
    } else {
        currentRecipientId = card.dataset.chatUserId;
    }
};

contactCards.forEach((card) => {
    card.addEventListener('click', () => selectUserCard(card));
    card.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            selectUserCard(card);
        }
    });
});

if (contactCards.length) {
    selectUserCard(contactCards[0], { skipFetch: true });
}
