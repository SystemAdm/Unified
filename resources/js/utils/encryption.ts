/**
 * Encryption utility for user data
 * Uses Web Crypto API for secure encryption in the browser
 */

/**
 * Get fallback APP_KEY (for development only)
 */
const getFallbackAppKey = (): string => {
    console.warn('Using fallback APP_KEY - this should not happen in production');
    return 'base64:3/iCjEPbldLKJtZere0H4pUeijoXXIUsWPZ91nDR7JY=';
};

/**
 * Convert base64 key to ArrayBuffer
 */
const base64ToArrayBuffer = (base64: string): ArrayBuffer => {
    // Remove 'base64:' prefix if present
    const cleanBase64 = base64.replace(/^base64:/, '');
    const binaryString = atob(cleanBase64);
    const bytes = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
};

/**
 * Derive encryption key from APP_KEY
 */
const deriveKey = async (appKey: string): Promise<CryptoKey> => {
    const keyBuffer = base64ToArrayBuffer(appKey);

    // Import the raw key material
    const importedKey = await crypto.subtle.importKey(
        'raw',
        keyBuffer.slice(0, 32), // Use first 32 bytes for AES-256
        { name: 'AES-GCM' },
        false,
        ['encrypt', 'decrypt']
    );

    return importedKey;
};

/**
 * Encrypt user data (id and family_name)
 */
export const encryptUserData = async (userId: number, familyName: string, appKey?: string): Promise<string> => {
    try {
        const keyToUse = appKey || getFallbackAppKey();
        const key = await deriveKey(keyToUse);

        // Create data object to encrypt
        const userData = {
            id: userId,
            family_name: familyName,
            timestamp: Date.now() // Add timestamp for additional security
        };

        // Convert to JSON string and then to ArrayBuffer
        const dataString = JSON.stringify(userData);
        const encoder = new TextEncoder();
        const dataBuffer = encoder.encode(dataString);

        // Generate random IV
        const iv = crypto.getRandomValues(new Uint8Array(12)); // 96-bit IV for GCM

        // Encrypt the data
        const encryptedBuffer = await crypto.subtle.encrypt(
            {
                name: 'AES-GCM',
                iv: iv
            },
            key,
            dataBuffer
        );

        // Combine IV and encrypted data
        const combined = new Uint8Array(iv.length + encryptedBuffer.byteLength);
        combined.set(iv);
        combined.set(new Uint8Array(encryptedBuffer), iv.length);

        // Convert to base64 for QR code
        const binaryString = String.fromCharCode(...combined);
        return btoa(binaryString);

    } catch (error) {
        console.error('Encryption failed:', error);
        throw new Error('Failed to encrypt user data');
    }
};

/**
 * Decrypt user data (for verification purposes)
 */
export const decryptUserData = async (encryptedData: string, appKey?: string): Promise<{ id: number; family_name: string; timestamp: number }> => {
    try {
        const keyToUse = appKey || getFallbackAppKey();
        const key = await deriveKey(keyToUse);

        // Convert from base64
        const binaryString = atob(encryptedData);
        const combined = new Uint8Array(binaryString.length);
        for (let i = 0; i < binaryString.length; i++) {
            combined[i] = binaryString.charCodeAt(i);
        }

        // Extract IV and encrypted data
        const iv = combined.slice(0, 12);
        const encryptedBuffer = combined.slice(12);

        // Decrypt the data
        const decryptedBuffer = await crypto.subtle.decrypt(
            {
                name: 'AES-GCM',
                iv: iv
            },
            key,
            encryptedBuffer
        );

        // Convert back to string and parse JSON
        const decoder = new TextDecoder();
        const dataString = decoder.decode(decryptedBuffer);
        return JSON.parse(dataString);

    } catch (error) {
        console.error('Decryption failed:', error);
        throw new Error('Failed to decrypt user data');
    }
};
