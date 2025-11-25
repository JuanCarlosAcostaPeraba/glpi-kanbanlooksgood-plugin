# Kanban Looks Good

[![Version](https://img.shields.io/badge/Version-1.2.1-green.svg)](https://github.com/JuanCarlosAcostaPeraba/kanbanlooksgood/releases)
[![GLPI Marketplace](https://img.shields.io/badge/GLPI_Marketplace-Available-orange.svg)](https://plugins.glpi-project.org/#/plugin/kanbanlooksgood)
[![GLPI](https://img.shields.io/badge/GLPI-10.0.x-blue.svg)](https://glpi-project.org)
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

- GLPI **10.0.0 - 10.0.99**
- PHP **7.4+**

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

## 📝 License

**GPLv3+**

Fully compatible with GLPI plugin licensing requirements.

## 👤 Author

Developed by **[Juan Carlos Acosta Perabá](https://github.com/JuanCarlosAcostaPeraba)** for **HUC – Hospital Universitario de Canarias**.
