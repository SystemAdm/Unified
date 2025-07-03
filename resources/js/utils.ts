/**
 * Format a date string using a specified format
 * @param dateString - The date string to format
 * @param formatString - The format string to use (e.g., 'MMM D, YYYY h:mm A')
 * @returns The formatted date string
 */
export function formatDate(dateString: string, formatString: string = 'MMMM D, YYYY'): string {
  try {
    const date = new Date(dateString);

    if (isNaN(date.getTime())) {
      return dateString;
    }

    // Define month names
    const months = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    const shortMonths = months.map(month => month.substring(0, 3));

    // Get date components
    const year = date.getFullYear();
    const month = date.getMonth();
    const day = date.getDate();
    const hours = date.getHours();
    const minutes = date.getMinutes();

    // Format the date according to the format string
    return formatString
      .replace('YYYY', year.toString())
      .replace('YY', (year % 100).toString().padStart(2, '0'))
      .replace('MMMM', months[month])
      .replace('MMM', shortMonths[month])
      .replace('MM', (month + 1).toString().padStart(2, '0'))
      .replace('M', (month + 1).toString())
      .replace('DD', day.toString().padStart(2, '0'))
      .replace('D', day.toString())
      .replace('hh', (hours % 12 || 12).toString().padStart(2, '0'))
      .replace('h', (hours % 12 || 12).toString())
      .replace('HH', hours.toString().padStart(2, '0'))
      .replace('H', hours.toString())
      .replace('mm', minutes.toString().padStart(2, '0'))
      .replace('m', minutes.toString())
      .replace('A', hours >= 12 ? 'PM' : 'AM')
      .replace('a', hours >= 12 ? 'pm' : 'am');
  } catch (error) {
    console.error('Error formatting date:', error);
    return dateString; // Return the original string if formatting fails
  }
}
