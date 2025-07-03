# Admin Menu Tests

This directory contains tests for the admin menu functionality.

## Test Files

- `AdminMenuTest.php`: Tests for the admin menu functionality, including access control for different user roles.
- `AdminSidebarTest.php`: Tests for the admin sidebar component, including visibility of menu items.
- `AdminTestCase.php`: Base test case for admin tests, which seeds the roles and permissions.

## Running the Tests

To run the admin menu tests, use the following command:

```bash
php artisan test tests/Feature/Admin
```

## Test Coverage

The tests cover the following aspects of the admin menu:

1. **Access Control**:
   - Admin users can access all admin pages (organizations, users, roles, permissions)
   - Non-admin users cannot access admin pages
   - Guests are redirected to login when trying to access admin pages

2. **Menu Visibility**:
   - Admin menu items are visible to admin users
   - Admin menu routes are accessible to admin users
   - Admin menu routes are not accessible to non-admin users
   - Admin menu routes redirect guests to login

## Implementation Details

The tests use the Pest PHP testing framework and Laravel's testing helpers. The `AdminTestCase` class extends Laravel's `TestCase` and seeds the roles and permissions before each test.

The tests create users with different roles (admin, member) and verify that they can or cannot access the admin pages based on their roles and permissions.
