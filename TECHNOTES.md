# CallTrackingMetrics WordPress Plugin – Tech Notes

## Overview

This document provides technical notes, troubleshooting tips, and architectural insights for future maintainers of the CallTrackingMetrics WordPress plugin. It is intended to help you understand the plugin’s structure, integration points, and common issues, as well as provide guidance for debugging and extending the plugin.

---

## Plugin Architecture

- **Entry Points:**
  - `call-tracking-metrics.php`: Lightweight loader and admin hooks (toast notifications, log email AJAX, script enqueuing).
  - `ctm.php`: Main plugin logic, class definitions, service orchestration, and WordPress hook registration.
- **Autoloading:**
  - Uses Composer for PSR-4 autoloading (`src/` for main code, `tests/` for tests).
  - Run `composer install` after pulling or updating code.
- **Core Classes:**
  - `CTM\Service\ApiService`: Handles API communication with CallTrackingMetrics.
  - `CTM\Service\CF7Service`: Integrates with Contact Form 7.
  - `CTM\Service\GFService`: Integrates with Gravity Forms.
  - `CTM\Admin\Options`: Manages plugin settings and admin UI.
  - `CTM\Admin\LoggingSystem`: Handles debug logging, log storage, and log emailing.

---

## Hook Usage & Integration Logic

- **Tracking Script Injection:**
  - Injected into `<head>` via `wp_head` action for all public pages (see `printTrackingScript`).
- **Form Integrations:**
  - **Contact Form 7:**
    - Hooks into `wpcf7_before_send_mail` for submission processing.
    - Adds JavaScript for confirmation tracking via `wpcf7mailsent` event.
    - Field mapping and enable/disable logic controlled by plugin settings.
  - **Gravity Forms:**
    - Hooks into `gform_after_submission` for submission processing.
    - Confirmation logic is a placeholder for future enhancements.
- **Dashboard Widget:**
  - Registered via `wp_dashboard_setup` if enabled in settings.
- **Lifecycle Hooks:**
  - Uses `register_activation_hook` and `register_deactivation_hook` for setup/cleanup (see LoggingSystem for log management on lifecycle events).
- **AJAX:**
  - Admin AJAX endpoints for emailing logs and other admin-side features.

---

## Logging System & Debugging

- **Logging:**
  - Debug logging is managed by `CTM\Admin\LoggingSystem`.
  - Logs are stored as daily WordPress options (e.g., `ctm_daily_log_{date}`) and indexed for retrieval.
  - Log entries include timestamp, type (info, error, debug, api, etc.), message, context, user, IP, and memory usage.
  - Only logs when debug mode is enabled (`ctm_debug_enabled` option).
- **Retention & Cleanup:**
  - Keeps up to 1000 entries per day.
  - Auto-cleans logs older than the retention period (default 7 days, configurable via `ctm_log_retention_days`).
  - Cleanup runs on plugin deactivation and can be triggered manually.
- **Log Access:**
  - Logs can be viewed in the admin dashboard widget or emailed for review.
  - Log statistics and available dates are accessible via LoggingSystem methods.
- **Debugging Tips:**
  - Enable debug mode in settings to capture detailed logs.
  - Use logs to trace API calls, form submissions, and errors.
  - For AJAX/admin issues, check both browser console and PHP error logs.

---

## Settings Management & Admin UI

- **Settings Registration:**
  - All plugin options are registered with the WordPress Settings API in `Options::registerSettings()`.
  - Includes API keys, feature toggles, logging options, and more.
- **Admin Page:**
  - Settings page is added under the WordPress Settings menu.
  - Uses a tabbed interface for organization (see `Options::renderSettingsPage`).
  - Notices and API connection status are dynamically generated.
- **Field Mapping:**
  - Managed by `FieldMapping` class, with assets enqueued for mapping UI.
  - Field mapping is essential for correct data transfer to CTM.
- **AJAX Handlers:**
  - All AJAX endpoints are registered and managed by `AjaxHandlers`.

---

## Security Considerations

- **API Keys:**
  - Stored as WordPress options; ensure database and admin access are secure.
- **Input Validation:**
  - All user input (including AJAX and form data) is sanitized before use.
- **Nonce & Capability Checks:**
  - AJAX endpoints and admin actions use nonces and capability checks to prevent unauthorized access.
- **Log Data:**
  - Logs may contain sensitive information (form data, user info); handle with care and restrict access to admins.
- **Dependencies:**
  - Keep Composer dependencies up to date to avoid vulnerabilities.

---

## Updating Dependencies & Plugin Lifecycle

- **Composer:**
  - Run `composer install` after pulling code or switching branches.
  - Update dependencies with `composer update` as needed, but test thoroughly.
- **Activation/Deactivation:**
  - On activation, logging system is initialized and log cleanup is scheduled.
  - On deactivation, logs are cleaned up and scheduled tasks are removed.
- **Uninstall:**
  - Plugin cleans up its options and logs on uninstall.

---

## Testing Integrations & Debugging Form Submissions

- **Contact Form 7:**
  - Test with forms containing `type="tel"` fields for phone numbers.
  - Check logs for each submission to verify data is sent to CTM.
  - Use browser console to confirm tracking script and JS events are firing.
- **Gravity Forms:**
  - Test with mapped fields and verify entries in logs and CTM dashboard.
  - Confirm confirmation logic if customizing.
- **API Testing:**
  - Use debug logs to inspect API request/response payloads.
  - Check for errors or failed submissions in logs.
- **AJAX/Admin:**
  - Test log emailing and other admin features with debug mode enabled.
  - Check for nonce/capability errors if features fail.

---

## Safely Extending or Refactoring

- **Adding Integrations:**
  - Follow the structure of `CF7Service` and `GFService` in `src/Service/`.
  - Register new hooks in the main plugin class (`ctm.php`).
- **Customizing Admin UI:**
  - Extend or modify `Options` and `SettingsRenderer` in `src/Admin/`.
- **Debugging:**
  - Use the `LoggingSystem` for consistent debug output.
- **Refactoring:**
  - Use dependency injection and composition patterns as in `Options` for maintainability.
  - Write or update tests in `tests/` (autoload-dev is configured for PHPUnit).
- **Backward Compatibility:**
  - Test all integrations and settings after changes.
  - Document any breaking changes in the changelog and README.

---

## Resources

- [CallTrackingMetrics API Docs](https://calltrackingmetrics.com/developers/)
- [WordPress Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [Composer](https://getcomposer.org/)

---

*Maintained by the CallTrackingMetrics Team. For support, contact support@calltrackingmetrics.com or refer to the main README for user-facing documentation.* 

---

## Advanced Maintainer Notes

### Plugin Lifecycle & Scheduled Tasks
- On activation, the plugin initializes logging and may schedule cleanup tasks for log retention.
- On deactivation, scheduled tasks are unscheduled and logs are cleaned up.
- If you add new scheduled tasks (via `wp_schedule_event`), ensure you unschedule them on deactivation to avoid orphaned cron jobs.

### Example: Adding a New Form Integration
1. Create a new service class in `src/Service/` (e.g., `MyFormService.php`).
2. Implement methods for processing submissions and mapping fields.
3. Register hooks for your form plugin in the main plugin class (`ctm.php`), similar to how CF7 and GF are handled.
4. Add settings and field mapping UI as needed in the admin options.
5. Log all submissions and API calls for debugging.

### Example: Debugging a Failed API Submission
- Enable debug mode in plugin settings.
- Submit a test form and check the debug log for the API request and response.
- Look for HTTP status codes, error messages, or payload issues.
- Use the `LoggingSystem::logDebug()` method to add custom debug output if needed.
- If the issue is with authentication, verify API keys and account permissions.

### Performance Considerations
- Log retention is capped (default 7 days, 1000 entries/day) to avoid database bloat.
- Avoid excessive logging in production unless troubleshooting.
- AJAX and admin features are designed to be lightweight, but test on large sites for scalability.
- If you add new features that store data, consider their impact on the WordPress options table.

### WordPress Multisite Compatibility
- The plugin is designed for standard WordPress but should work on multisite if activated per site.
- Options and logs are stored per site, not network-wide.
- Test all integrations and admin features on multisite before deploying widely.

### Other Advanced Tips
- When refactoring, use dependency injection and keep classes single-responsibility.
- Always sanitize and validate all user input, especially for new AJAX endpoints.
- Document any new hooks, filters, or actions you add for future developers.
- Keep the README and TECHNOTES up to date with any architectural or integration changes.

--- 

---

## Additional Maintainer Notes & Scenarios

### Example Troubleshooting Scenarios
- **Scenario: API keys are correct but no data is sent to CTM**
  - Double-check that the tracking script is present in the site’s `<head>`.
  - Ensure the correct integration (CF7/GF) is enabled and mapped.
  - Check debug logs for API errors or payload issues.
  - Test with a minimal theme and no other plugins to rule out conflicts.

- **Scenario: Log emails are not received**
  - Check the WordPress mail configuration and spam folder.
  - Verify the recipient email in the plugin settings.
  - Review PHP error logs for mail-related errors.

- **Scenario: Plugin settings page is blank or broken**
  - Check for JavaScript errors in the browser console.
  - Ensure all Composer dependencies are installed.
  - Disable other admin plugins to rule out conflicts.

### Safely Updating the Plugin in Production
- Always back up your site and database before updating.
- Test updates on a staging environment first.
- After updating, run `composer install` to ensure dependencies are up to date.
- Check the plugin settings and dashboard widget for errors post-update.
- Review the changelog and README for breaking changes.

### Handling Plugin Conflicts
- If you suspect a conflict, disable other plugins and switch to a default theme.
- Gradually re-enable plugins/themes to isolate the conflict.
- Common conflicts: other plugins that modify forms, AJAX, or admin UI.
- Use debug logs to capture errors or unexpected behavior.
- Report reproducible conflicts to the plugin maintainers with detailed steps.

### Adding Custom Logging or Monitoring
- Use `CTM\Admin\LoggingSystem::logDebug($message)` to add custom log entries.
- For advanced monitoring, consider integrating with external logging services (e.g., Sentry, Loggly) by hooking into the logging system.
- Always sanitize sensitive data before logging.

### Contributing Code or Requesting Features
- Fork the repository and create a feature branch for your changes.
- Follow PSR-12 coding standards and document all new classes/methods.
- Add or update tests in the `tests/` directory if possible.
- Submit a pull request with a clear description of your changes.
- For feature requests or bug reports, open an issue with detailed reproduction steps.

### Automated Tests
- The plugin is set up for PHPUnit tests (autoload-dev in composer.json).
- Add new tests in the `tests/` directory, following the namespace `CTM\Tests`.
- Run tests with `vendor/bin/phpunit` from the plugin directory.
- Ensure all tests pass before submitting code changes.

### GDPR & Data Privacy
- The plugin may log or transmit personal data (e.g., phone numbers, form fields).
- Ensure you have user consent for tracking and data transmission.
- Use the plugin’s log retention settings to minimize data storage.
- Document your data practices in your site’s privacy policy.
- If you add new features that process personal data, review GDPR and local privacy laws.

### Long-Term Maintenance Advice
- Regularly review and update Composer dependencies to patch security issues.
- Monitor the WordPress and CTM changelogs for breaking changes or new features.
- Keep documentation (README, TECHNOTES) up to date with all changes.
- Encourage users and contributors to report issues and suggest improvements.

--- 

---

## Developer-Focused Information

### Code Style and Best Practices
- Follow PSR-12 coding standards for PHP.
- Use namespaces for all new classes (see `CTM\Service`, `CTM\Admin`).
- Keep classes single-responsibility and use composition over inheritance where possible.
- Document all public methods and classes with PHPDoc blocks.
- Use type hints and return types for all functions and methods.
- Prefer dependency injection for class dependencies (see `Options` class for example).

### Using and Extending Dependency Injection
- The plugin uses dependency injection for admin and service classes (see `Options::__construct`).
- When adding new features, inject dependencies via the constructor rather than instantiating them directly.
- This makes the codebase more testable and maintainable.

### Adding New Admin Pages or Settings
- Use `add_options_page` or `add_menu_page` in your admin class to add new pages.
- Register new settings with the WordPress Settings API in your options class.
- Render settings forms using a dedicated renderer class for separation of concerns.
- Use nonces and capability checks for all admin actions.

### Adding or Modifying AJAX Endpoints
- Register AJAX actions using `add_action('wp_ajax_{action}', ...)` for authenticated users and `add_action('wp_ajax_nopriv_{action}', ...)` for public endpoints.
- Always check nonces and user capabilities in your AJAX handlers.
- Return JSON responses using `wp_send_json_success` and `wp_send_json_error`.
- See `AjaxHandlers` class for examples.

### Internationalization (i18n) and Localization (l10n)
- Use `__()` and `_e()` functions for all user-facing strings.
- Set the `Text Domain` header in the main plugin file (`call-tracking-metrics`).
- Place translation files in the `/languages` directory.
- Run `wp i18n make-pot` to generate a `.pot` file for translators.

### Handling Plugin Upgrades and Migrations
- For new options or database schema changes, use `register_activation_hook` or version checks in your main plugin class to run migrations.
- Store the plugin version in an option and check it on each load to trigger upgrades if needed.
- Always test migrations on a staging environment before deploying.

### Documentation in the Codebase
- Most classes and methods are documented with PHPDoc blocks.
- See the `README.md` and this `TECHNOTES.md` for high-level documentation.
- Inline comments are used for complex logic or important decisions.
- For new features, update both the code and documentation.

### Using Plugin Hooks and Filters
- The plugin provides hooks and filters for extensibility (e.g., `ctm_cf7_formreactor_data`, `ctm_gf_formreactor_data`).
- Use `do_action` and `apply_filters` to add new extensibility points.
- Document all new hooks in the code and in the documentation.
- Encourage third-party developers to use these hooks for custom integrations.

### Onboarding New Developers
- New developers should start by reading the `README.md` and `TECHNOTES.md`.
- Review the main plugin files (`ctm.php`, `call-tracking-metrics.php`) to understand the flow.
- Explore the `src/` directory for service and admin classes.
- Set up a local development environment with Composer and PHPUnit.
- Run and review automated tests in the `tests/` directory.
- Ask questions and document answers for future contributors.

--- 