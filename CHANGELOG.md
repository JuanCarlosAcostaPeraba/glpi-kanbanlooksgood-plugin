# Changelog - Kanban Looks Good

## Version 1.3.2 - GLPI 10.x / 11.x Compatible (2025-12-10)

### 🔧 Fixed
- **Compatibilidad completa con GLPI 11.x**: El plugin ahora funciona correctamente en GLPI 11 sin romper la aplicación
- **Hooks actualizados**: Reemplazado el uso de `Glpi\Plugin\Hooks` namespace por constantes de string directas para compatibilidad
- **Métodos de base de datos**: Actualizados métodos deprecados (`query` → `queryOrDie`, `insert` → `insertOrDie`)
- **Manejo de sesiones mejorado**: Verificación robusta de variables de sesión para prioridades
- **CommonITILObject**: Añadido fallback para obtener nombres de prioridades si la clase no existe en GLPI 11

### ⚡ Changed
- Versión incrementada de 1.3.1 a 1.3.2
- Rango de compatibilidad extendido: GLPI 10.0.0 - 11.1.99
- Mejora en el manejo de errores con bloques try-catch
- Sanitización HTML mejorada para evitar vulnerabilidades XSS
- Header de configuración adaptado a GLPI 11 (parámetros actualizados)
- Verificación CSRF añadida en el formulario de configuración

### 🛡️ Security
- Añadida validación CSRF en `front/config.form.php`
- Escapado HTML mejorado en todos los outputs

### 📝 Technical Details

**Cambios principales por archivo:**

#### `setup.php`
- ❌ Eliminado: `use Glpi\Plugin\Hooks;`
- ✅ Hook registrado como string: `'kanban_item_metadata'`
- ✅ Métodos DB actualizados: `queryOrDie()`, `insertOrDie()`
- ✅ Manejo de errores con try-catch

#### `inc/hook.class.php`
- ✅ Verificación de existencia de `$_SESSION['glpipriority_X']`
- ✅ Fallback para `CommonITILObject::getPriorityName()`
- ✅ Sanitización HTML en todos los outputs
- ✅ Compatible con ambas versiones de GLPI

#### `inc/config.class.php`
- ✅ Try-catch en `saveConfig()`
- ✅ Logging de errores si está disponible

#### `front/config.form.php`
- ✅ Verificación CSRF añadida
- ✅ Header compatible con GLPI 11 (parámetros ajustados)
- ✅ Detección de versión para llamar a Html::header() correctamente

#### `plugin.xml`
- ✅ Añadida versión 1.3.2 con compatibilidad para 10.x y 11.x

### 🚀 Instalación en GLPI 11

1. Copia el plugin en `plugins/kanbanlooksgood/`
2. Ve a **Configuración → Plugins**
3. Instala y activa "Kanban Looks Good"
4. Configura las opciones en **Configuración → Kanban Looks Good**

### ⚠️ Notas de Migración

Si actualizas desde la versión 1.3.1:
- No es necesario desinstalar el plugin
- La configuración existente se preservará
- La tabla de base de datos se verificará y actualizará automáticamente

### 🧪 Tested On
- ✅ GLPI 10.0.x
- ✅ GLPI 11.0.x

---

## Version 1.3.1 (Anterior)

Ver historial anterior en releases previos.

