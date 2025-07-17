import { DateTime } from 'luxon';

/**
 * Format a date string using a specified format
 * @param dateString - The date string to format
 * @param formatString - The format string to use (e.g., 'MMM D, YYYY h:mm A', 'dddd D/M/YYYY HH:mm')
 * @returns The formatted date string
 */
export function formatDate(dateString: string, formatString: string = 'MMMM D, YYYY'): string {
  try {
    // Create a mapping of our format tokens to Luxon format tokens
    const formatMap: Record<string, string> = {
      // Day of week
      'dddd': 'cccc', // Full day name
      'ddd': 'ccc',   // Short day name
      // Year
      'YYYY': 'yyyy', // 4-digit year
      'YY': 'yy',     // 2-digit year
      // Month
      'MMMM': 'LLLL', // Full month name
      'MMM': 'LLL',   // Short month name
      'MM': 'LL',     // 2-digit month
      'M': 'L',       // 1-digit month
      // Day
      'DD': 'dd',     // 2-digit day
      'D': 'd',       // 1-digit day
      // Hour
      'hh': 'hh',     // 2-digit 12-hour
      'h': 'h',       // 1-digit 12-hour
      'HH': 'HH',     // 2-digit 24-hour
      'H': 'H',       // 1-digit 24-hour
      // Minute
      'mm': 'mm',     // 2-digit minute
      'm': 'm',       // 1-digit minute
      // AM/PM
      'A': 'a',       // AM/PM
      'a': 'a'        // am/pm
    };

    // Sort tokens by length (longest first) to ensure proper replacement
    const sortedTokens = Object.keys(formatMap).sort((a, b) => b.length - a.length);

    // Convert the input format string to Luxon format
    let luxonFormat = formatString;
    for (const token of sortedTokens) {
      // Escape special regex characters in the token
      const escapedToken = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      // Use a regular expression to replace all occurrences of the token
      const regex = new RegExp(escapedToken, 'g');
      luxonFormat = luxonFormat.replace(regex, formatMap[token]);
    }

    // Parse the date string and format it using Luxon
    const dateTime = DateTime.fromISO(dateString);

    if (!dateTime.isValid) {
      return dateString;
    }

    return dateTime.toFormat(luxonFormat);
  } catch (error) {
    console.error('Error formatting date:', error);
    return dateString; // Return the original string if formatting fails
  }
}
