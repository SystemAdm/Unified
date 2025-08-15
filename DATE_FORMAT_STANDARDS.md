# Date Format Standards

This document outlines the standardized date formats used throughout the application.

## Standard Format Codes

The application uses the following standardized format codes for consistent date and time formatting:

| Format Code | Description | Example | Use Case |
|-------------|-------------|---------|----------|
| `d` | Date format | 15/06/2009 | Regular dates, created/updated timestamps |
| `m` | Month format | 15. juni | Banners recurring dates |
| `f` | Full date and time | Mandag 15 juni 2009 13:45 | Events with date and time |
| `t` | Time only | 13:45 | Time-only display |

## Implementation

These formats are implemented in the `formatDate` function in `resources/js/utils.ts`. To use these standardized formats:

```javascript
import { formatDate } from '@/utils';

// Examples
formatDate(dateString, 'd'); // Date format: 15/06/2009
formatDate(dateString, 'm'); // Month format: 15. juni
formatDate(dateString, 'f'); // Full date and time: Mandag 15 juni 2009 13:45
formatDate(dateString, 't'); // Time only: 13:45
```

## Legacy Format Strings

The `formatDate` function still supports legacy format strings for backward compatibility:

```javascript
formatDate(dateString, 'MMMM D, YYYY'); // Custom format
formatDate(dateString, 'dddd D/M/YYYY HH:mm'); // Custom format
```

However, it is recommended to use the standardized format codes for consistency across the application.

## Testing

A test script is available at `resources/js/date-format-test.js` to verify the standardized date formats.

Run the test with:
```
node resources/js/date-format-test.js
```
