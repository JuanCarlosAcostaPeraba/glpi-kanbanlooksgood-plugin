# Changelog - Kanban Looks Good

## Version 2.1.0 - GLPI 11 Native (2025-01-XX)

### 🔧 Changes
- Version bump for marketplace release
- Improved release packaging script
- Updated documentation

---

## Version 2.0.0 - GLPI 11 Native (2025-12-12)

### ⚠️ BREAKING CHANGES
- **Dropped GLPI 10.x support**: This version only works with GLPI 11.0.x
- **For GLPI 10.x users**: Please continue using version 1.3.4
- **Architectural change**: Switched from client-side (jQuery) to server-side (PHP) rendering

### 🚀 Major Changes
- **Complete rewrite for GLPI 11's Vue.js-based Kanban**:
  - Changed from `KANBAN_ITEM_METADATA` hook to `PRE_KANBAN_CONTENT` hook
  - Metadata (priority, duration) now injected as HTML during server-side rendering
  - Removed all client-side JavaScript (jQuery) - no longer needed
  - Priority colors applied via inline CSS for immediate rendering
- **Assets in `public/` directory**: CSS now served from `public/css/` (GLPI 11 standard)
- **Namespace Hooks**: Uses `Glpi\Plugin\Hooks` namespace for cleaner code
- **Modern database methods**: Uses `query()` and `insert()` instead of deprecated methods
- **Simplified codebase**: Removed all GLPI 10 compatibility layers

### ✨ New Features
- Native GLPI 11 integration with Vue.js Kanban
- Server-side color lightening for card backgrounds
- Inline CSS injection for priority-based card styling
- Better performance with GLPI 11 architecture

### 🗑️ Removed
- All JavaScript files (`kanban.js`, `config_inject.js`) - no longer needed
- Client-side DOM manipulation code
- jQuery dependencies for Kanban modifications

### 📝 Technical Details

**Why the Architectural Change?**

GLPI 11 uses Vue.js for Kanban rendering, which doesn't allow client-side DOM manipulation after Vue mounts. The plugin now generates all HTML and styles server-side during the Kanban data preparation phase, ensuring compatibility with Vue's reactive rendering system.

**File Structure Changes:**
- Created `public/` directory for assets
- Moved `css/` → `public/css/`
- Removed `js/` directory (no longer needed)

**Code Changes:**
- Added `use Glpi\Plugin\Hooks;`
- Changed hook from `kanban_item_metadata` to `PRE_KANBAN_CONTENT`
- Changed `$PLUGIN_HOOKS['add_css']` → `$PLUGIN_HOOKS[Hooks::ADD_CSS]`
- Removed `$PLUGIN_HOOKS['add_javascript']` (not needed)
- Updated minimum GLPI version: 11.0.0
- Updated maximum GLPI version: 11.0.99
- Removed all GLPI 10 fallback code
- Simplified database operations using GLPI 11 methods

**Benefits:**
- 🚀 Faster and more reliable
- ✅ Follows GLPI 11 best practices
- 🧹 Cleaner codebase (removed ~200 lines of JS)
- 🔮 Easier to maintain and update
- ⚡ No client-side rendering delays

### 🧪 Tested On
- ✅ GLPI 11.0.0 - 11.0.4
- ✅ PHP 8.1+
- ✅ MySQL 8.0+
- ✅ MariaDB 10.5+

---

## Version 1.3.4 - Last GLPI 10 Compatible Version (2024-11-15)

### 🔧 Changes
- Last version compatible with GLPI 10.x
- Supports GLPI 10.0.0 - 10.1.99
- Uses jQuery for client-side rendering
- Uses `kanban_item_metadata` hook

### 📌 Note
If you are using GLPI 10.x, this is the version you should use. Do not upgrade to 2.0.0 or higher.

---

## Version 1.3.3 (2024-10-20)

### 🐛 Bug Fixes
- Fixed priority color retrieval for GLPI 10.0.15+
- Improved compatibility with different GLPI 10 minor versions

---

## Version 1.3.2 (2024-09-10)

### ✨ Improvements
- Better error handling in configuration
- Improved translation support

---

## Version 1.3.1 (2024-08-05)

### 🐛 Bug Fixes
- Fixed duration calculation for projects with multiple tasks
- Corrected CSS for dark theme

---

## Version 1.3.0 (2024-07-01)

### ✨ New Features
- Added configuration page
- Configurable work hours per day
- Option to show/hide priority
- Option to show/hide duration

---

## Version 1.2.0 (2024-05-15)

### ✨ New Features
- Added planned duration display
- Duration shown in days, hours, and minutes

---

## Version 1.1.0 (2024-04-01)

### ✨ New Features
- Added priority display on Kanban cards
- Color-coded priority badges

---

## Version 1.0.0 (2024-03-01)

### 🎉 Initial Release
- Basic Kanban enhancements for GLPI Projects
- Priority and duration display
- Compatible with GLPI 10.0.0+
