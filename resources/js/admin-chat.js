import axios from 'axios';

const container = document.getElementById('admin-chat-messages');
const form = document.getElementById('admin-chat-form');
const input = form?.querySelector('textarea[name="message"]');
const contactCards = document.querySelectorAll('[data-chat-user-id]');
const searchInput = document.querySelector('[data-chat-search]');
const filterButtons = document.querySelectorAll('[data-chat-filter]');
const headerNameEl = document.querySelector('[data-chat-header-name]');
const headerHandleEl = document.querySelector('[data-chat-header-handle]');
const headerInitialsEl = document.querySelector('[data-chat-header-initials]');
const typingIndicatorEl = document.querySelector('[data-chat-typing]');
const phoneButton = document.querySelector('[data-chat-phone-button]');
const phoneDisplay = document.querySelector('[data-chat-phone-display]');
const phoneText = document.querySelector('[data-chat-phone-text]');
const recipientInput = form?.querySelector('input[name="recipient_user_id"]');
const conversationUrlTemplate = window.ADMIN_CHAT_CONVERSATION_URL_TEMPLATE ?? '';
const readUrl = window.ADMIN_CHAT_READ_URL ?? '';
let currentRecipientId = window.ADMIN_CHAT_INITIAL_RECIPIENT_ID ? String(window.ADMIN_CHAT_INITIAL_RECIPIENT_ID) : (recipientInput?.value ? String(recipientInput?.value) : null);
let activeFilter = 'all';
let typingTimeout = null;
let presenceMembers = new Set();

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
    row.dataset.chatMessageId = payload.id;
    row.dataset.chatSenderId = payload.sender_id;
    row.dataset.chatRecipientId = payload.recipient_id;

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

    if (fromSelf) {
        const status = document.createElement('div');
        status.className = 'mt-1 text-[11px] text-gray-400 flex items-center justify-end gap-1';
        status.dataset.chatReadStatus = '';
        const time = document.createElement('span');
        time.textContent = formatTime(payload.created_at);
        status.appendChild(time);
        if (payload.read_at) {
            const check = document.createElement('span');
            check.textContent = 'Read';
            status.appendChild(check);
        }
        bubble.appendChild(status);
    }

    row.appendChild(bubble);

    if (options.prepend) {
        container.prepend(row);
    } else {
        container.appendChild(row);
        scrollToBottom();
    }
};

const formatTime = (value) => {
    if (!value) return '--';
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return '--';
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const updateUnreadBadge = (card, count) => {
    if (!card) return;
    const badge = card.querySelector('[data-chat-unread-badge]');
    card.dataset.chatUnread = String(count);
    if (!badge) return;
    if (count > 0) {
        badge.classList.remove('hidden');
        badge.textContent = count > 99 ? '99+' : String(count);
    } else {
        badge.classList.add('hidden');
        badge.textContent = '';
    }
};

const updatePreview = (card, message, createdAt, isSender) => {
    if (!card) return;
    const previewEl = card.querySelector('[data-chat-last-preview]');
    const timeEl = card.querySelector('[data-chat-last-time]');
    if (previewEl) {
        const prefix = isSender ? 'You: ' : '';
        previewEl.textContent = message ? `${prefix}${message}` : previewEl.textContent;
    }
    if (timeEl) {
        timeEl.textContent = formatTime(createdAt);
    }
};

const applyFilters = () => {
    const query = (searchInput?.value || '').trim().toLowerCase();
    contactCards.forEach((card) => {
        const name = (card.dataset.chatUserName || '').toLowerCase();
        const handle = (card.dataset.chatUserHandle || '').toLowerCase();
        const matchesQuery = !query || name.includes(query) || handle.includes(query);
        const unreadCount = Number(card.dataset.chatUnread || 0);
        const isOnline = presenceMembers.size
            ? presenceMembers.has(String(card.dataset.chatUserId))
            : card.dataset.chatOnline !== 'false';

        let matchesFilter = true;
        if (activeFilter === 'unread') {
            matchesFilter = unreadCount > 0;
        } else if (activeFilter === 'online') {
            matchesFilter = isOnline;
        }

        card.classList.toggle('hidden', !(matchesQuery && matchesFilter));
    });
};

const updateOnlineIndicators = () => {
    if (!presenceMembers.size) return;
    contactCards.forEach((card) => {
        const isOnline = presenceMembers.has(String(card.dataset.chatUserId));
        const dot = card.querySelector('[data-chat-online-dot]');
        card.dataset.chatOnline = isOnline ? 'true' : 'false';
        if (dot) {
            dot.classList.toggle('hidden', !isOnline);
        }
    });
};

const showMessages = (messages = []) => {
    if (!container) return;
    container.innerHTML = '';
    messages.forEach((message) => renderMessage(message, { prepend: false, force: true }));
    scrollToBottom();
};

const updatePhoneDisplay = (card, { show = false } = {}) => {
    if (!phoneDisplay || !phoneText) return;
    const value = card?.dataset.chatUserPhone || '';
    const hasValue = value && value.trim().length > 0;
    phoneText.textContent = hasValue ? value : '--';
    phoneDisplay.classList.toggle('hidden', !(show && hasValue));
};

const fetchConversation = async (userId) => {
    if (!userId || !conversationUrlTemplate || !container) return;
    const url = conversationUrlTemplate.replace('__USER__', encodeURIComponent(userId));
    try {
        const response = await axios.get(url);
        const messages = response.data?.messages ?? [];
        currentRecipientId = String(userId);
        showMessages(messages);
        updateUnreadBadge(document.querySelector(`[data-chat-user-id="${userId}"]`), 0);
        if (readUrl) {
            axios.post(readUrl, { sender_user_id: userId }).catch(() => {});
        }
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
            const card = document.querySelector(`[data-chat-user-id="${recipientId}"]`);
            updatePreview(card, value, response.data.message.created_at ?? new Date().toISOString(), true);
            input.value = '';
        } catch (error) {
            console.error(error);
        } finally {
            input.disabled = false;
            input.focus();
        }
    });

    input.addEventListener('input', () => {
        if (!presenceChannel || !currentRecipientId) return;
        presenceChannel.whisper('typing', {
            sender_id: window.ADMIN_CHAT_CURRENT_USER_ID,
            recipient_id: currentRecipientId,
        });
    });
}

let presenceChannel = null;
if (window.Echo) {
    const privateChannel = window.Echo.private(`admin-chat.${window.ADMIN_CHAT_CURRENT_USER_ID}`);

    privateChannel.listen('.AdminChatMessageSent', (event) => {
        const isRecipient = String(event.recipient_id) === String(window.ADMIN_CHAT_CURRENT_USER_ID);
        const isSender = String(event.sender_id) === String(window.ADMIN_CHAT_CURRENT_USER_ID);
        const counterpartId = isSender ? String(event.recipient_id) : String(event.sender_id);
        const card = document.querySelector(`[data-chat-user-id="${counterpartId}"]`);
        updatePreview(card, event.message, event.created_at, isSender);

        if (isRecipient && String(counterpartId) !== String(currentRecipientId)) {
            const currentCount = Number(card?.dataset.chatUnread || 0) + 1;
            updateUnreadBadge(card, currentCount);
        }

        if (messageBelongsToConversation(event)) {
            renderMessage(event, { prepend: false });
            if (isRecipient && readUrl) {
                axios.post(readUrl, { sender_user_id: counterpartId }).catch(() => {});
            }
        }
    });

    privateChannel.listen('.AdminChatMessagesRead', (event) => {
        if (String(event.sender_id) !== String(window.ADMIN_CHAT_CURRENT_USER_ID)) {
            return;
        }
        const messageNodes = container?.querySelectorAll('[data-chat-message-id]') || [];
        messageNodes.forEach((node) => {
            const senderId = node.dataset.chatSenderId;
            const recipientId = node.dataset.chatRecipientId;
            if (String(senderId) === String(event.sender_id) && String(recipientId) === String(event.reader_id)) {
                const status = node.querySelector('[data-chat-read-status]') || node.querySelector('.text-[11px]');
                if (status && !status.querySelector('svg')) {
                    const icon = document.createElement('svg');
                    icon.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
                    icon.setAttribute('viewBox', '0 0 24 24');
                    icon.setAttribute('fill', 'none');
                    icon.setAttribute('stroke', 'currentColor');
                    icon.className = 'h-3 w-3 text-blue-500';
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';
                    status.appendChild(icon);
                }
            }
        });
    });

    presenceChannel = window.Echo.join('admin-chat-presence')
        .here((members) => {
            presenceMembers = new Set(members.map((m) => String(m.id)));
            updateOnlineIndicators();
            applyFilters();
        })
        .joining((member) => {
            presenceMembers.add(String(member.id));
            updateOnlineIndicators();
            applyFilters();
        })
        .leaving((member) => {
            presenceMembers.delete(String(member.id));
            updateOnlineIndicators();
            applyFilters();
        })
        .listenForWhisper('typing', (payload) => {
            if (!payload || String(payload.recipient_id) !== String(window.ADMIN_CHAT_CURRENT_USER_ID)) {
                return;
            }
            if (String(payload.sender_id) !== String(currentRecipientId)) {
                return;
            }
            if (typingIndicatorEl) {
                typingIndicatorEl.classList.remove('hidden');
                typingIndicatorEl.textContent = 'Typing...';
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    typingIndicatorEl.classList.add('hidden');
                }, 2000);
            }
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
    if (headerInitialsEl) {
        const avatar = card.dataset.chatUserAvatar;
        if (avatar) {
            headerInitialsEl.innerHTML = `<img src="${avatar}" alt="${card.dataset.chatUserName}" class="h-full w-full object-cover">`;
        } else {
            headerInitialsEl.textContent = card.dataset.chatUserInitials;
        }
    }
    if (recipientInput) {
        recipientInput.value = card.dataset.chatUserId;
    }
    if (!skipFetch) {
        fetchConversation(card.dataset.chatUserId);
    } else {
        currentRecipientId = card.dataset.chatUserId;
    }
    if (typingIndicatorEl) {
        typingIndicatorEl.classList.add('hidden');
    }
    updatePhoneDisplay(card, { show: false });
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

if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
}

filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
        activeFilter = button.dataset.chatFilter || 'all';
        filterButtons.forEach((btn) => {
            btn.classList.toggle('bg-blue-600', btn === button);
            btn.classList.toggle('text-white', btn === button);
            btn.classList.toggle('bg-gray-100', btn !== button);
            btn.classList.toggle('text-gray-700', btn !== button);
        });
        applyFilters();
    });
});

if (contactCards.length) {
    selectUserCard(contactCards[0], { skipFetch: true });
}

if (phoneButton) {
    phoneButton.addEventListener('click', () => {
        const activeCard = document.querySelector('[data-chat-user-id][aria-pressed="true"]');
        const isHidden = phoneDisplay?.classList.contains('hidden');
        updatePhoneDisplay(activeCard, { show: isHidden });
    });
}
