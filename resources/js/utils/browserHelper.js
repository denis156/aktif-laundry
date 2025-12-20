// ==========================================
// BROWSER HELPER
// ==========================================
// Helper untuk detect in-app browser dan redirect ke external browser

/**
 * Detect apakah user menggunakan WebView
 * @returns {boolean}
 */
export function detectWebView() {
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;

    // Detect WebView patterns
    // React Native: includes "ReactNative"
    // Flutter: includes "Flutter"
    // Android WebView: includes "wv" or "Version/X.X Chrome/XX (Linux; Android"
    // iOS WKWebView: no Safari in UA but has AppleWebKit
    const isReactNative = /ReactNative/i.test(userAgent);
    const isFlutter = /Flutter/i.test(userAgent);
    const isAndroidWebView = /wv|WebView/i.test(userAgent) || (/Android/i.test(userAgent) && /Version\/[\d.]+/i.test(userAgent) && !/Chrome/i.test(userAgent));
    const isIOSWebView = /iPhone|iPad|iPod/i.test(userAgent) && /AppleWebKit/i.test(userAgent) && !/Safari/i.test(userAgent);

    return isReactNative || isFlutter || isAndroidWebView || isIOSWebView;
}

/**
 * Detect apakah user menggunakan in-app browser
 * @returns {Object} { isInApp: boolean, isWebView: boolean, platform: string, browser: string }
 */
export function detectInAppBrowser() {
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;

    // Check if WebView first
    const isWebView = detectWebView();

    // List of in-app browsers to detect
    const inAppBrowsers = {
        instagram: /Instagram/i.test(userAgent),
        facebook: /FBAN|FBAV/i.test(userAgent),
        twitter: /Twitter/i.test(userAgent),
        tiktok: /TikTok|musical_ly/i.test(userAgent),
        line: /Line/i.test(userAgent),
        linkedin: /LinkedInApp/i.test(userAgent),
        whatsapp: /WhatsApp/i.test(userAgent),
        telegram: /Telegram/i.test(userAgent),
        snapchat: /Snapchat/i.test(userAgent),
        pinterest: /Pinterest/i.test(userAgent),
        reddit: /RedditApp/i.test(userAgent),
    };

    // Check if any in-app browser is detected
    const detectedBrowser = Object.keys(inAppBrowsers).find(key => inAppBrowsers[key]);
    const isInApp = !!detectedBrowser;

    // Detect platform
    const isAndroid = /Android/i.test(userAgent);
    const isIOS = /iPhone|iPad|iPod/i.test(userAgent);

    let platform = 'unknown';
    if (isAndroid) platform = 'android';
    if (isIOS) platform = 'ios';

    return {
        isInApp,
        isWebView,
        platform,
        browser: detectedBrowser || (isWebView ? 'webview' : 'unknown'),
        userAgent
    };
}

/**
 * Get app URL from meta tag atau fallback ke window.location.origin
 * @returns {string}
 */
export function getAppUrl() {
    const metaTag = document.querySelector('meta[name="app-url"]');
    return metaTag ? metaTag.content : window.location.origin;
}

/**
 * Generate Chrome Intent URL untuk Android
 * @param {string} url - URL yang ingin dibuka
 * @returns {string} Intent URL
 */
export function generateChromeIntentUrl(url) {
    // Parse URL untuk extract scheme, host, dan path
    const urlObj = new URL(url);
    const scheme = urlObj.protocol.replace(':', ''); // https
    const host = urlObj.host; // aktif-laundry.test
    const path = urlObj.pathname + urlObj.search + urlObj.hash; // /path?query#hash

    // Format Intent URL untuk Chrome
    return `intent://${host}${path}#Intent;scheme=${scheme};package=com.android.chrome;end`;
}

/**
 * Generate Brave Intent URL untuk Android (alternative)
 * @param {string} url - URL yang ingin dibuka
 * @returns {string} Intent URL
 */
export function generateBraveIntentUrl(url) {
    const urlObj = new URL(url);
    const scheme = urlObj.protocol.replace(':', '');
    const host = urlObj.host;
    const path = urlObj.pathname + urlObj.search + urlObj.hash;

    return `intent://${host}${path}#Intent;scheme=${scheme};package=com.brave.browser;end`;
}

/**
 * Redirect ke external browser
 * Android: Gunakan Intent URL untuk buka di Chrome
 * iOS: Return false (harus manual, tidak bisa redirect otomatis)
 *
 * @param {string} url - URL yang ingin dibuka (optional, default: current URL)
 * @returns {boolean} true jika redirect berhasil, false jika gagal atau iOS
 */
export function redirectToExternalBrowser(url = null) {
    const detection = detectInAppBrowser();
    const targetUrl = url || window.location.href;

    // Jika bukan in-app browser, tidak perlu redirect
    if (!detection.isInApp) {
        console.log('[BrowserHelper] Not in-app browser, no redirect needed');
        return false;
    }

    // Android: Redirect menggunakan Intent URL
    if (detection.platform === 'android') {
        try {
            const chromeIntentUrl = generateChromeIntentUrl(targetUrl);

            // Log untuk debugging
            console.log('[BrowserHelper] Redirecting to Chrome via Intent URL');
            console.log('[BrowserHelper] Intent URL:', chromeIntentUrl);

            // Redirect
            window.location.href = chromeIntentUrl;

            return true;
        } catch (error) {
            console.error('[BrowserHelper] Failed to redirect to Chrome:', error);
            return false;
        }
    }

    // iOS: Tidak bisa redirect otomatis
    if (detection.platform === 'ios') {
        console.log('[BrowserHelper] iOS detected - manual redirect required');
        return false;
    }

    return false;
}

/**
 * Copy current URL to clipboard
 * Berguna untuk iOS yang harus manual paste ke Safari/Chrome
 * @returns {Promise<boolean>}
 */
export async function copyCurrentUrlToClipboard() {
    try {
        await navigator.clipboard.writeText(window.location.href);
        console.log('[BrowserHelper] URL copied to clipboard');
        return true;
    } catch (error) {
        console.error('[BrowserHelper] Failed to copy URL:', error);

        // Fallback: gunakan document.execCommand (deprecated tapi masih work di beberapa browser)
        try {
            const textArea = document.createElement('textarea');
            textArea.value = window.location.href;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);

            if (successful) {
                console.log('[BrowserHelper] URL copied using fallback method');
                return true;
            }

            return false;
        } catch (fallbackError) {
            console.error('[BrowserHelper] Fallback copy also failed:', fallbackError);
            return false;
        }
    }
}

/**
 * Check apakah perlu tampilkan warning untuk in-app browser
 * @returns {boolean}
 */
export function shouldShowInAppBrowserWarning() {
    const detection = detectInAppBrowser();
    return detection.isInApp;
}

/**
 * Get user-friendly browser name
 * @param {string} browserKey - Browser key dari detection
 * @returns {string}
 */
export function getBrowserDisplayName(browserKey) {
    const names = {
        instagram: 'Instagram',
        facebook: 'Facebook',
        twitter: 'Twitter (X)',
        tiktok: 'TikTok',
        line: 'LINE',
        linkedin: 'LinkedIn',
        whatsapp: 'WhatsApp',
        telegram: 'Telegram',
        snapchat: 'Snapchat',
        pinterest: 'Pinterest',
        reddit: 'Reddit',
        unknown: 'In-App Browser'
    };

    return names[browserKey] || names.unknown;
}

// Expose functions ke window object untuk digunakan dari Livewire/Alpine
window.browserHelper = {
    detect: detectInAppBrowser,
    detectWebView: detectWebView,
    redirectToExternal: redirectToExternalBrowser,
    copyUrl: copyCurrentUrlToClipboard,
    shouldShowWarning: shouldShowInAppBrowserWarning,
    getAppUrl: getAppUrl,
    generateChromeIntent: generateChromeIntentUrl,
    generateBraveIntent: generateBraveIntentUrl,
    getBrowserName: getBrowserDisplayName,
};

// Log initialization
console.log('[BrowserHelper] Initialized');

// Auto detect on load and log if in-app browser
const initialDetection = detectInAppBrowser();
if (initialDetection.isInApp) {
    console.warn('[BrowserHelper] In-app browser detected:', {
        browser: getBrowserDisplayName(initialDetection.browser),
        platform: initialDetection.platform,
        userAgent: initialDetection.userAgent
    });
}
