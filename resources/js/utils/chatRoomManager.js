/**
 * ChatRoomManager
 * Manages chat room auto-scrolling behavior with Alpine.js
 *
 * Features:
 * - Auto-scroll to bottom on load and new messages
 * - Smart scroll: only auto-scroll when user is near bottom
 * - Handles Livewire morph updates
 *
 * @class
 */
export class ChatRoomManager {
    /**
     * @param {HTMLElement} element - The Alpine.js element (x-data root)
     */
    constructor(element) {
        this.element = element;
        this.isNearBottom = true;
        this.scrollThreshold = 100; // pixels from bottom
        this.container = null;
    }

    /**
     * Initialize the chat room manager
     * Should be called in Alpine.js x-init
     */
    init() {
        // Find the scrollable container
        this.container = this.element.querySelector('.overflow-y-auto');

        if (!this.container) {
            console.warn('ChatRoomManager: Scrollable container not found');
            return;
        }

        // Initial scroll to bottom
        this.scrollToBottom();

        // Setup scroll listener
        this.container.addEventListener('scroll', () => this.checkScrollPosition());

        // Setup Livewire morph listener
        Livewire.hook('morph.updated', () => {
            if (this.isNearBottom) {
                // Use nextTick to ensure DOM is updated
                setTimeout(() => this.scrollToBottom(), 0);
            }
        });
    }

    /**
     * Scroll container to bottom
     */
    scrollToBottom() {
        if (this.container) {
            this.container.scrollTop = this.container.scrollHeight;
        }
    }

    /**
     * Check if user is near bottom of scroll
     * Updates isNearBottom flag
     */
    checkScrollPosition() {
        if (!this.container) {
            return;
        }

        const { scrollHeight, scrollTop, clientHeight } = this.container;
        this.isNearBottom = (scrollHeight - scrollTop - clientHeight) < this.scrollThreshold;
    }

    /**
     * Handle message sent event
     * Always scroll to bottom when user sends a message
     */
    onMessageSent() {
        setTimeout(() => this.scrollToBottom(), 0);
    }

    /**
     * Handle new message received event
     * Force scroll to bottom and update flag
     */
    onNewMessageReceived() {
        this.isNearBottom = true;
        setTimeout(() => this.scrollToBottom(), 0);
    }

    /**
     * Factory method for Alpine.js integration
     * Returns Alpine.js compatible data object
     *
     * @returns {Object} Alpine.js data object
     */
    static createAlpineData() {
        return {
            manager: null,
            isNearBottom: true,

            init() {
                this.manager = new ChatRoomManager(this.$el);
                this.manager.init();

                // Bind isNearBottom to manager
                this.$watch('manager.isNearBottom', value => {
                    this.isNearBottom = value;
                });
            },

            scrollToBottom() {
                if (this.manager) {
                    this.manager.scrollToBottom();
                }
            },

            handleMessageSent() {
                if (this.manager) {
                    this.manager.onMessageSent();
                }
            },

            handleNewMessageReceived() {
                if (this.manager) {
                    this.manager.onNewMessageReceived();
                }
            }
        };
    }
}

/**
 * Initialize ChatRoomManager on Alpine component
 * Use this as Alpine.js x-data attribute value
 *
 * @example
 * <div x-data="chatRoomData()"
 *      @message-sent.window="handleMessageSent()"
 *      @new-message-received.window="handleNewMessageReceived()">
 * </div>
 *
 * @returns {Object} Alpine.js data object
 */
export function chatRoomData() {
    return ChatRoomManager.createAlpineData();
}

// Make available globally for Blade templates
if (typeof window !== 'undefined') {
    window.chatRoomData = chatRoomData;
    window.ChatRoomManager = ChatRoomManager;
}
