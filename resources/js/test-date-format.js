// Test script for formatDate function
import { formatDate } from './utils';

// Test date
const testDate = '2023-05-15T14:30:00Z';

// Test formats
console.log('Default format:', formatDate(testDate));
console.log('Admin events index format:', formatDate(testDate, 'dddd D/M/YYYY HH:mm'));
console.log('Events show format:', formatDate(testDate, 'MMM D, YYYY h:mm A'));

// Run with: node resources/js/test-date-format.js
