// Test script for standardized date formats
import { formatDate } from './utils';

// Test date (August 3, 2025 at 9:32 AM)
const testDate = '2025-08-03T09:32:00Z';

// Test all standardized formats
console.log('Standard date format (d):', formatDate(testDate, 'd'));
// Expected: 03/08/2025

console.log('Standard banners recurring format (m):', formatDate(testDate, 'm'));
// Expected: 03. August

console.log('Standard events date and time format (f):', formatDate(testDate, 'f'));
// Expected: Sunday 03 August 2025 09:32

console.log('Standard time format (t):', formatDate(testDate, 't'));
// Expected: 09:32

// Run with: node resources/js/date-format-test.js
