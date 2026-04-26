/**
 * Chatbot JavaScript - Handles UI and API communication
 */

(function () {
    'use strict';

    // Configuration
    const API_URL = 'api/chat.php';
    const TYPING_DELAY = 50; // ms per character for typing effect

    // State
    let isOpen = false;
    let isLoading = false;

    /**
     * Initialize chatbot when DOM is ready
     */
    function initChatbot() {
        createChatbotHTML();
        attachEventListeners();
        showWelcomeMessage();
    }

    /**
     * Create chatbot HTML structure
     */
    function createChatbotHTML() {
        const chatbotHTML = `
            <!-- Floating Chat Button -->
            <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Open chat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                    <path d="M7 9h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/>
                </svg>
            </button>

            <!-- Chat Window -->
            <div id="chatbot-window" class="chatbot-window">
                <div class="chatbot-header">
                    <h3>🤖 Career Guide AI</h3>
                    <button class="chatbot-close" aria-label="Close chat">×</button>
                </div>
                <div id="chatbot-messages" class="chatbot-messages">
                    <!-- Messages will be inserted here -->
                </div>
                <div class="chatbot-input">
                    <input 
                        type="text" 
                        id="chatbot-input" 
                        placeholder="Ask about careers, skills, courses..."
                        autocomplete="off"
                    />
                    <button id="chatbot-send" class="chatbot-send" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Insert at end of body
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    /**
     * Attach event listeners
     */
    function attachEventListeners() {
        const toggleBtn = document.getElementById('chatbot-toggle');
        const closeBtn = document.querySelector('.chatbot-close');
        const sendBtn = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-input');

        toggleBtn.addEventListener('click', toggleChatWindow);
        closeBtn.addEventListener('click', closeChatWindow);
        sendBtn.addEventListener('click', sendMessage);

        // Send on Enter key
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !isLoading) {
                sendMessage();
            }
        });
    }

    /**
     * Toggle chat window open/close
     */
    function toggleChatWindow() {
        isOpen = !isOpen;
        const window = document.getElementById('chatbot-window');
        const toggleBtn = document.getElementById('chatbot-toggle');

        if (isOpen) {
            window.classList.add('active');
            toggleBtn.classList.add('hidden');
            document.getElementById('chatbot-input').focus();
        } else {
            window.classList.remove('active');
            toggleBtn.classList.remove('hidden');
        }
    }

    /**
     * Close chat window
     */
    function closeChatWindow() {
        isOpen = false;
        document.getElementById('chatbot-window').classList.remove('active');
        document.getElementById('chatbot-toggle').classList.remove('hidden');
    }

    /**
     * Show welcome message
     */
    function showWelcomeMessage() {
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.innerHTML = `
            <div class="welcome-message">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <h4>Welcome! How can I help?</h4>
                <p>Ask me about careers, skills, courses, or guidance!</p>
            </div>
        `;
    }

    /**
     * Send message to chatbot
     */
    async function sendMessage() {
        if (isLoading) return;

        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();

        if (!message) return;

        // Clear input
        input.value = '';

        // Add user message to chat
        addMessage(message, 'user');

        // Show typing indicator
        showTypingIndicator();
        isLoading = true;
        updateSendButton(true);

        try {
            // Call API
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();

            // Remove typing indicator
            removeTypingIndicator();

            if (data.success) {
                // Add AI response with typing effect
                await addMessageWithTyping(data.reply, 'ai');
            } else {
                showError(data.error || 'Failed to get response');
            }
        } catch (error) {
            removeTypingIndicator();
            showError('Network error. Please check your connection.');
        } finally {
            isLoading = false;
            updateSendButton(false);
            input.focus();
        }
    }

    /**
     * Add message to chat
     */
    function addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbot-messages');

        // Remove welcome message if present
        const welcome = messagesContainer.querySelector('.welcome-message');
        if (welcome) {
            welcome.remove();
        }

        const time = new Date().toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit'
        });

        const messageHTML = `
            <div class="chat-message ${sender}">
                <div class="message-content">${escapeHtml(text)}</div>
                <div class="message-time">${time}</div>
            </div>
        `;

        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        scrollToBottom();
    }

    /**
     * Add message with typing effect
     */
    async function addMessageWithTyping(text, sender) {
        const messagesContainer = document.getElementById('chatbot-messages');

        // Remove welcome message if present
        const welcome = messagesContainer.querySelector('.welcome-message');
        if (welcome) {
            welcome.remove();
        }

        const time = new Date().toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit'
        });

        // Create empty message
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        messageDiv.innerHTML = `
            <div class="message-content"></div>
            <div class="message-time">${time}</div>
        `;

        messagesContainer.appendChild(messageDiv);
        const contentDiv = messageDiv.querySelector('.message-content');

        // Type out the message
        let index = 0;
        const escapedText = escapeHtml(text);

        return new Promise((resolve) => {
            const typeInterval = setInterval(() => {
                if (index < escapedText.length) {
                    contentDiv.textContent += escapedText[index];
                    index++;
                    scrollToBottom();
                } else {
                    clearInterval(typeInterval);
                    resolve();
                }
            }, TYPING_DELAY);
        });
    }

    /**
     * Show typing indicator
     */
    function showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const typingHTML = `
            <div id="typing-indicator" class="chat-message ai">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
        scrollToBottom();
    }

    /**
     * Remove typing indicator
     */
    function removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const errorHTML = `
            <div class="error-message">
                ⚠️ ${escapeHtml(message)}
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', errorHTML);
        scrollToBottom();
    }

    /**
     * Update send button state
     */
    function updateSendButton(loading) {
        const sendBtn = document.getElementById('chatbot-send');
        sendBtn.disabled = loading;
    }

    /**
     * Scroll messages to bottom
     */
    function scrollToBottom() {
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatbot);
    } else {
        initChatbot();
    }

})();
