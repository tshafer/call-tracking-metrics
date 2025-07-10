# CallTrackingMetrics WordPress Plugin

Integrate your WordPress site with [CallTrackingMetrics](https://calltrackingmetrics.com) for advanced call and form tracking, analytics, and marketing attribution.

## Requirements

- WordPress 6.5 or higher (tested up to 6.8.1)
- PHP 7.4+ (8.0+ recommended)
- CallTrackingMetrics account with API access enabled

## Installation

1. **Obtain a CallTrackingMetrics Account**
   - Sign up at [CallTrackingMetrics](https://calltrackingmetrics.com) and ensure your account has API access enabled.
2. **Download or Clone the Plugin**
   - Download the latest release or clone the repository into your WordPress plugins directory:
     ```sh
     git clone <repo-url> wp-content/plugins/call-tracking-metrics
     ```
3. **Install PHP Dependencies**
   - If you are developing or running from source, install dependencies with Composer:
     ```sh
     cd wp-content/plugins/call-tracking-metrics
     composer install
     ```
   - *Note: The plugin requires PHP 7.4 or higher (PHP 8.0+ recommended).*
4. **Activate the Plugin**
   - Go to the WordPress admin dashboard, navigate to **Plugins**, and activate **CallTrackingMetrics**.

## Configuration

- Go to **Settings > CallTrackingMetrics** in the WordPress admin.
- Enter your CTM API Access Key and Secret Key (get these from your CTM dashboard).
- Enable integrations as needed:
  - **Contact Form 7**: Toggle integration and map form fields. Ensure phone fields use `type="tel"` for best compatibility.
  - **Gravity Forms**: Toggle integration and map form fields.
- Optionally, enable the dashboard widget to view daily CTM activity directly in your WordPress admin dashboard.
- Configure additional options such as debug logging, tracking script injection, and more from the settings page.
- Save your settings.

## Usage

- The plugin will automatically inject the CTM tracking script on your site’s `<head>`, enabling call and form tracking on all public pages.
- Submissions from Contact Form 7 and Gravity Forms are automatically tracked and sent to CTM, provided the relevant integration is enabled and fields are mapped.
- A dashboard widget will display daily CTM activity if enabled.
- Debug logs and activity can be viewed or emailed from the admin.
- Use the settings page to map your form fields to CTM fields for accurate data capture.
- Many admin features (such as emailing logs) use AJAX for a smooth experience.
- The plugin is compatible with standard WordPress installations. For multisite, activate per site as needed.

## Feature Highlights

- Easy to configure and use
- Tracks calls from a full range of sources
- Identifies repeat callers
- Shows which marketing sources provide the best ROI
- Integrates with Contact Form 7 and Gravity Forms
- Dashboard widget for at-a-glance analytics
- Debug logging and troubleshooting tools

## Quick Start Example

1. Install and activate the plugin.
2. Enter your CTM API keys in **Settings > CallTrackingMetrics**.
3. Enable and configure integrations for your forms.
4. Check the dashboard widget for activity.
5. Review logs for troubleshooting if needed.

## Troubleshooting

- **API Connection Issues**: Double-check your API keys and ensure your CTM account has API access enabled.
- **No Calls/Leads Tracked**: Ensure the tracking script is present in your site’s `<head>`. Check for JavaScript errors in the browser console.
- **Form Integration Not Working**:
  - Make sure the relevant integration (CF7 or GF) is enabled in plugin settings.
  - For Contact Form 7, ensure phone fields use `type="tel"`.
  - For Gravity Forms, confirm field mapping is correct.
- **Debug Logs**: Use the admin dashboard widget or the “Email Daily Log” feature to review logs for errors or activity.
- **Plugin Not Loading**: Ensure PHP version is compatible (>=7.4, ideally 8.0+). Check for missing Composer dependencies (`vendor/` folder).

## Development & Maintenance

- **Dependencies**: Managed via Composer. Run `composer install` after pulling updates.
- **Testing**: (No test files present, but autoload-dev is configured for PHPUnit. Add tests in `tests/` and run with `vendor/bin/phpunit`.)
- **Updating**: After updating, always verify plugin activation and check the dashboard widget for errors.
- **Uninstall**: Plugin cleans up its options on uninstall.

## Source Control Handoff

- All plugin files are in `wp-content/plugins/call-tracking-metrics/`.
- Remove any development or temporary files before handoff (e.g., `.DS_Store`, local config overrides).
- Ensure `vendor/` is present for production, or provide instructions to run `composer install`.

### Optional: Push to Source Control

1. Initialize a git repository if not already present:
   ```sh
   git init
   git add .
   git commit -m "Initial commit of CallTrackingMetrics plugin"
   ```
2. Add remote and push:
   ```sh
   git remote add origin <repo-url>
   git push -u origin main
   ```

## License

GPL-2.0-or-later
