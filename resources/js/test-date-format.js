// Test script for formatDate function
import { formatDate } from './utils';

// Test date
const testDate = '2023-05-15T14:30:00Z';

// Test standard formats
console.log('Standard date format (d):', formatDate(testDate, 'd'));
console.log('Standard banners recurring format (m):', formatDate(testDate, 'm'));
console.log('Standard events date and time format (f):', formatDate(testDate, 'f'));
console.log('Standard time format (t):', formatDate(testDate, 't'));

// Test legacy formats
console.log('Default format:', formatDate(testDate));
console.log('Admin events index format:', formatDate(testDate, 'dddd D/M/YYYY HH:mm'));
console.log('Events show format:', formatDate(testDate, 'MMM D, YYYY h:mm A'));

// Run with: node resources/js/test-date-format.js
