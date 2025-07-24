# QR Code Implementation Summary

## Overview
Implemented clickable UserInfo component that displays a QR code containing encrypted user data (User::id and User::family_name) using the APP_KEY for encryption.

## Changes Made

### 1. Dependencies Added
- **qrcode**: JavaScript library for QR code generation
- **@types/qrcode**: TypeScript types for qrcode library

### 2. Backend Changes
- **HandleInertiaRequests.php**: Added `appKey` to shared Inertia data to make APP_KEY available to frontend

### 3. Frontend Changes

#### TypeScript Types
- **types/index.d.ts**: Added `family_name?: string` to User interface

#### Encryption Utility
- **utils/encryption.ts**: Created encryption utility with:
  - `encryptUserData()`: Encrypts user ID and family_name using AES-GCM
  - `decryptUserData()`: Decrypts encrypted data (for verification)
  - Uses Web Crypto API for secure browser-based encryption
  - Accepts APP_KEY as parameter from page props

#### UserInfo Component
- **components/UserInfo.vue**: Modified to:
  - Make component clickable with hover effects
  - Add QR code icon to indicate functionality
  - Generate QR code with encrypted user data on click
  - Display QR code in modal popup
  - Handle loading states and error cases

## Security Features
- Uses AES-256-GCM encryption with random IV
- Includes timestamp in encrypted data for additional security
- APP_KEY is securely passed from Laravel backend
- Encrypted data is base64 encoded for QR code compatibility

## User Experience
- Clickable UserInfo component with visual feedback
- QR code icon indicates functionality
- Modal popup displays generated QR code
- Loading state during QR generation
- Click outside modal to close

## Data Structure
The QR code contains encrypted JSON data:
```json
{
  "id": 123,
  "family_name": "Smith",
  "timestamp": 1642857600000
}
```

## Usage
1. User clicks on UserInfo component
2. System encrypts user ID and family_name using APP_KEY
3. QR code is generated containing encrypted data
4. Modal displays QR code for scanning
5. QR code can be decrypted using the same APP_KEY

## Files Modified
- `package.json` (dependencies)
- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/types/index.d.ts`
- `resources/js/utils/encryption.ts` (new file)
- `resources/js/components/UserInfo.vue`

## Testing
The implementation is ready for testing in the browser. The QR code should contain encrypted user data that can be decrypted using the APP_KEY.
