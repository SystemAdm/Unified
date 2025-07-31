# QR Code Testing Guide

This document provides a comprehensive guide to understanding and testing the QR code functionality in the Unified application.

## How the QR Code System Works

The QR code system in the Unified application is designed to encrypt user information (specifically the user ID and family name) into a base64 text string, which can then be used to register users for events.

### Encryption Methods

The system supports two encryption methods:

1. **Simple Base64 Encoding (Legacy Method)**
   - Simply encodes the user ID using base64
   - Example: `MQ==` (base64 encoding of user ID "1")
   - This is a simple method that only includes the user ID

2. **AES-256-GCM Encryption (Current Method)**
   - Creates a JSON object with user ID, family name, and timestamp
   - Encrypts this data using AES-256-GCM with the app's encryption key
   - Combines the IV (Initialization Vector), ciphertext, and authentication tag
   - Converts to base64 for the QR code
   - This is a more secure method that includes additional user information

### Validation Process

The validation process works as follows:

1. An admin goes to the "Validate Encrypted Text" page for an event
2. The admin scans a QR code or manually enters the encrypted text
3. The system attempts to decrypt the text using the AES-256-GCM method
4. If that fails, it falls back to the simple base64 decoding method
5. Once it has a user ID, it finds the user in the database
6. If the user is not already registered for the event, it adds them to the event's registered users
7. The system displays a success message with the user's name

## Testing the QR Code Functionality

To test the QR code functionality, follow these steps:

### Prerequisites

- Access to the Unified application
- Admin privileges to access the event management features
- A user account to test with

### Step 1: Generate an Encrypted QR Code

#### Option 1: Using the Frontend (if available)

If there's a QR code generation feature in the frontend:

1. Log in to the application
2. Navigate to your user profile or a page that displays your QR code
3. Save or screenshot the QR code for testing

#### Option 2: Using JavaScript Console

You can generate an encrypted text string using the browser's JavaScript console:

1. Log in to the application
2. Open the browser's developer tools (F12 or right-click > Inspect)
3. Go to the Console tab
4. Copy and paste the following code (replace USER_ID and FAMILY_NAME with actual values):

```javascript
// Import the encryption utility
import { encryptUserData } from '@/utils/encryption';

// Encrypt user data
const encryptedText = await encryptUserData(USER_ID, 'FAMILY_NAME');
console.log('Encrypted Text:', encryptedText);
```

5. If the import doesn't work directly in the console, you may need to find a page where the encryption utility is already imported and accessible.

### Step 2: Validate the Encrypted Text

1. Log in as an admin
2. Navigate to the Events section
3. Select an event
4. Click on "Validate Encrypted Text" (or similar option)
5. Enter the encrypted text from Step 1
6. Click "Validate Text"
7. Verify that the system successfully identifies the user and registers them for the event

### Testing Both Encryption Methods

#### Testing Simple Base64 Encoding

1. Generate a base64 encoded user ID:
   - For user ID 1: `MQ==`
   - For user ID 2: `Mg==`
   - For user ID 3: `Mw==`
   - (You can use online base64 encoders to generate these)
2. Use this encoded text in the validation process

#### Testing AES-256-GCM Encryption

This is more complex to generate manually, but you can:

1. Use the JavaScript method described in Option 2 above
2. Or use the test_qr_code_full.php script (which needs to be run in the Laravel context)

## Troubleshooting

If validation fails, check the following:

1. **Invalid Format**: Ensure the encrypted text is a valid base64 string
2. **User Not Found**: Verify that the user ID exists in the database
3. **Encryption Key Mismatch**: Ensure the app is using the correct encryption key
4. **Corrupted Data**: Try regenerating the encrypted text

## Conclusion

The QR code system in the Unified application provides a secure way to encrypt user information and validate it for event registration. By following this guide, you should be able to test both encryption methods and verify that the system works correctly.
