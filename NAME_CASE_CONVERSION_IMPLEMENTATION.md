# Name/Title Case Conversion Implementation

## Overview
This document describes the implementation of proper case conversion for name and title attributes across all models in the application. The implementation uses `mb_convert_case($name, MB_CASE_TITLE, 'UTF-8')` to ensure consistent capitalization of names and titles with proper UTF-8 support.

## Implementation Details

### Models Updated
The following models have been updated to implement proper case conversion:

1. **User Model**
   - Already had case conversion for name-related attributes:
     - `getNameAttribute`
     - `setNameAttribute`
     - `getGivenNameAttribute`
     - `getFamilyNameAttribute`
     - `getAdditionalNameAttribute`

2. **Wishlist Model**
   - Already had case conversion for the name attribute:
     - `getNameAttribute`

3. **Organization Model**
   - Added case conversion for the name attribute:
     - `getNameAttribute`

4. **Event Model**
   - Added case conversion for the title attribute:
     - `getTitleAttribute`

5. **Location Model**
   - Added case conversion for the name attribute:
     - `getNameAttribute`

6. **News Model**
   - Added case conversion for the title attribute:
     - `getTitleAttribute`

7. **Announcement Model**
   - Added case conversion for the title attribute:
     - `getTitleAttribute`

8. **Game Model**
   - Added case conversion for the name attribute:
     - `getNameAttribute`

9. **Banner Model**
   - Added case conversion for the title attribute:
     - `getTitleAttribute`

10. **Console Model**
    - Added case conversion for the name attribute:
      - `getNameAttribute`

11. **SelfHostedApp Model**
    - Added case conversion for the name attribute:
      - `getNameAttribute`

### Implementation Pattern
For each model, the following pattern was implemented:

```php
/**
 * Get the name/title attribute with first letter of each word capitalized
 */
public function getNameAttribute($value): string
{
    return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
}
```

This ensures that whenever a name or title attribute is accessed, it will be properly formatted with the first letter of each word capitalized, regardless of how it was stored in the database.

### Testing
A test script (`test_name_case_conversion.php`) was created to verify the implementation. The script loads each model and displays both the original and formatted values of the name/title attributes to confirm proper case conversion.

## Benefits
- Consistent capitalization of names and titles across the application
- Proper handling of UTF-8 characters in names and titles
- Improved user experience with properly formatted text
- Centralized formatting logic in the models rather than in views or controllers
