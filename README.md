# Kanban Looks Good

[![Version](https://img.shields.io/badge/Version-1.3.0-green.svg)](https://github.com/JuanCarlosAcostaPeraba/kanbanlooksgood/releases)
[![GLPI Marketplace](https://img.shields.io/badge/GLPI_Marketplace-Available-orange.svg)](https://plugins.glpi-project.org/#/plugin/kanbanlooksgood)
[![GLPI](https://img.shields.io/badge/GLPI-10.0.x_|_11.0.x-blue.svg)](https://glpi-project.org)
[![License: GPLv3+](https://img.shields.io/badge/License-GPLv3+-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.html)
[![Maintained](https://img.shields.io/badge/Maintained-yes-success.svg)]()

A lightweight and non-intrusive GLPI plugin that enhances the **Project Kanban** by displaying **Priority** and **Planned Duration** directly on each card — without modifying any GLPI core files.

## ✨ Features

- 🔹 Displays GLPI's native **priority badge** on Project and ProjectTask cards
- 🔹 Shows **planned duration** using GLPI's own formatting
- 🔹 Adds a clean metadata bar below each card header
- 🔹 Applies softened background color according to priority
- 🔹 Works for both Projects and ProjectTasks
- 🔹 Fully hook-based — **no core overrides**
- 🔹 **Configurable settings** via GLPI admin panel

## 📦 Requirements

- GLPI **10.0.x** or **11.0.x**
- PHP **8.1+** (recommended for GLPI 11)

## 🚀 Installation

### Option 1: From GLPI Marketplace (Recommended)

1. Go to **GLPI → Configuration → Plugins → Marketplace**
2. Search for **Kanban Looks Good**
3. Click **Install**, then **Enable**

### Option 2: Manual Installation

1. Download the latest release from [GitHub Releases](https://github.com/JuanCarlosAcostaPeraba/kanbanlooksgood/releases)
2. Extract and copy the folder `kanbanlooksgood` into:

    ```
    glpi/plugins/
    ```

3. Go to **GLPI → Configuration → Plugins**
4. Find **Kanban Looks Good**
5. Click **Install**, then **Enable**

## ⚙️ Configuration

Access the plugin settings via **GLPI → Configuration → Plugins → Kanban Looks Good**.

Available options:

- **Show Priority Badge**: Enable/disable priority badge display on cards
- **Show Planned Duration**: Enable/disable planned duration display
- **Work Hours per Day**: Configure hours per work day for duration calculations (1-24 hours, default: 7)

## 🧩 How it works

### Priority

- Uses GLPI's priority configuration (badge + color)
- Applies priority color to the card header
- Softened version of the same color is used as card background

### Planned Duration

- **Projects**: sum of all related ProjectTask planned durations
- **ProjectTasks**: uses their native `planned_duration` field
- Duration format uses configurable work hours per day (e.g., "2d 3h 30min")

## 🏗️ Plugin Structure

```
kanbanlooksgood/
├── setup.php                  # Plugin registration + hooks
├── plugin.xml                 # Plugin metadata for GLPI marketplace
├── inc/
│   ├── hook.class.php         # Injects metadata into Kanban cards
│   └── config.class.php       # Plugin configuration management
├── front/
│   └── config.form.php        # Configuration form handler
├── js/
│   ├── kanban.js              # Frontend enhancements (color + metadata bar)
│   └── config_inject.js       # Configuration injection for JavaScript
├── css/
│   └── kanban.css             # Styling for metadata section
├── locales/
│   └── es_ES.php              # Spanish translations
├── assets/
│   ├── logo.png               # Plugin logo
│   └── screenshots/           # Screenshots for marketplace
└── README.md
```

## 🔌 Hooks Used

- **`Hooks::KANBAN_ITEM_METADATA`**
  Injects priority, planned duration, and colors directly into card metadata so the frontend can render everything instantly.

## 🌐 Translations

- English (en_GB) - Default
- Spanish (es_ES)
- French (fr_FR)

Contributions for additional languages are welcome!

## ❓ FAQ

### Does this plugin modify GLPI core files?

No. This plugin uses GLPI's hook system exclusively. It doesn't modify any core files, making it safe and easy to maintain.

### Will this work with custom themes?

Yes. The plugin uses GLPI's native priority colors and styling, so it adapts automatically to custom themes, including dark mode.

### Can I disable priority or duration display separately?

Yes. Go to **Configuration → Plugins → Kanban Looks Good** to toggle each feature independently.

### Does this affect performance?

No significant performance impact. The plugin:
- Uses existing GLPI metadata hooks
- Minimizes database queries
- Loads only on Kanban pages

### Can I use this with GLPI 9.x?

No. This plugin requires GLPI 10.0.0 or higher. For older versions, you would need to modify the code significantly.

### What happens if I uninstall the plugin?

All plugin data (configuration) is removed from the database. Your Kanban will return to its original appearance without any side effects.

## 🔧 Troubleshooting

### Priority badges not showing

**Possible causes:**
- Priority display is disabled in plugin configuration
- Project has no priority set
- Browser cache needs to be cleared

**Solutions:**
1. Check plugin configuration: **Configuration → Plugins → Kanban Looks Good**
2. Verify project has priority set: **Projects → Edit → Priority**
3. Clear browser cache and reload the page
4. Check browser console for JavaScript errors (F12)

### Duration not displaying

**Possible causes:**
- Duration display is disabled in plugin configuration
- Project/Task has no planned duration
- Work hours configuration is invalid

**Solutions:**
1. Check plugin configuration is enabled
2. Verify tasks have planned duration set
3. Ensure "Work Hours per Day" is between 1-24
4. Check that ProjectTasks are properly linked to the Project

### JavaScript console errors

**Possible causes:**
- Conflicting JavaScript from other plugins
- Outdated browser version
- GLPI version incompatibility

**Solutions:**
1. Disable other plugins temporarily to identify conflicts
2. Update browser to latest version
3. Verify GLPI version compatibility (10.0.x or 11.0.x)
4. Check GLPI logs in `files/_log/`

### Styling looks broken

**Possible causes:**
- CSS caching issues
- Theme conflicts
- Custom CSS overrides

**Solutions:**
1. Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
2. Disable browser extensions temporarily
3. Check for custom CSS in your theme that might conflict
4. Try in incognito/private browsing mode

### Plugin doesn't appear in plugin list

**Possible causes:**
- Incorrect installation directory
- File permissions issues
- Missing required files

**Solutions:**
1. Verify folder is named `kanbanlooksgood` (lowercase, no spaces)
2. Check folder is in `glpi/plugins/` directory
3. Verify file permissions (readable by web server)
4. Check that `setup.php` exists and is valid PHP

### Getting "Access Denied" error

**Possible causes:**
- Insufficient user permissions
- Session expired

**Solutions:**
1. Verify you have "config" UPDATE permission
2. Log out and log back in
3. Contact your GLPI administrator for permissions

## 🐛 Reporting Issues

If you encounter a bug or have a feature request:

1. Check existing [issues](https://github.com/JuanCarlosAcostaPeraba/kanbanlooksgood/issues)
2. Use the appropriate issue template
3. Include:
   - GLPI version
   - Plugin version
   - PHP version
   - Browser and OS
   - Steps to reproduce
   - Error messages or screenshots

## 🤝 Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

Ways to contribute:
- Report bugs
- Suggest features
- Submit pull requests
- Improve documentation
- Add translations

## 📝 License

**GPLv3+**

Fully compatible with GLPI plugin licensing requirements.

## 👤 Author

Developed by **[Juan Carlos Acosta Perabá](https://github.com/JuanCarlosAcostaPeraba)** for **HUC – Hospital Universitario de Canarias**.

## 🌟 Support

If this plugin helps you, please consider:
- ⭐ Starring the repository
- 📢 Sharing it with the GLPI community
- 🐛 Reporting issues
- 💡 Contributing improvements

## 📊 Stats

![GitHub release (latest by date)](https://img.shields.io/github/v/release/JuanCarlosAcostaPeraba/kanbanlooksgood)
![GitHub](https://img.shields.io/github/license/JuanCarlosAcostaPeraba/kanbanlooksgood)
![GitHub issues](https://img.shields.io/github/issues/JuanCarlosAcostaPeraba/kanbanlooksgood)
![GitHub pull requests](https://img.shields.io/github/issues-pr/JuanCarlosAcostaPeraba/kanbanlooksgood)
