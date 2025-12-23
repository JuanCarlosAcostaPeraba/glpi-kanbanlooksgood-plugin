#!/bin/bash
# =========================================================================
# Script para crear el paquete de distribución de Kanban Looks Good
# Para Linux/Mac/Git Bash
# =========================================================================

VERSION="2.1.0"
PLUGIN_NAME="kanbanlooksgood"
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}==========================================${NC}"
echo -e "${CYAN}  Kanban Looks Good - Build Release${NC}"
echo -e "${CYAN}  Version: ${VERSION} (GLPI 11 Only)${NC}"
echo -e "${CYAN}==========================================${NC}"
echo ""

# Crear directorio temporal
TEMP_DIR="./build/${PLUGIN_NAME}"
if [ -d "./build" ]; then
    rm -rf ./build
fi
mkdir -p "${TEMP_DIR}"

echo -e "${YELLOW}[1/4] Copiando archivos...${NC}"

# Copiar estructura de directorios y archivos
copy_files() {
    # Archivos raíz
    cp setup.php "${TEMP_DIR}/" && echo -e "${GREEN}  ✓ Copiado: setup.php${NC}"
    cp plugin.xml "${TEMP_DIR}/" && echo -e "${GREEN}  ✓ Copiado: plugin.xml${NC}"
    cp LICENSE "${TEMP_DIR}/" && echo -e "${GREEN}  ✓ Copiado: LICENSE${NC}"
    cp README.md "${TEMP_DIR}/" && echo -e "${GREEN}  ✓ Copiado: README.md${NC}"
    cp CHANGELOG.md "${TEMP_DIR}/" && echo -e "${GREEN}  ✓ Copiado: CHANGELOG.md${NC}"
    cp hook.php "${TEMP_DIR}/" 2>/dev/null && echo -e "${GREEN}  ✓ Copiado: hook.php${NC}" || echo -e "${YELLOW}  ⚠ hook.php no encontrado (opcional)${NC}"
    
    # Carpeta inc/
    if [ -d "inc" ]; then
        mkdir -p "${TEMP_DIR}/inc"
        if ls inc/*.php >/dev/null 2>&1; then
            cp inc/*.php "${TEMP_DIR}/inc/" && echo -e "${GREEN}  ✓ Copiado: inc/*.php${NC}"
        fi
    fi
    
    # Carpeta front/
    if [ -d "front" ]; then
        mkdir -p "${TEMP_DIR}/front"
        if ls front/*.php >/dev/null 2>&1; then
            cp front/*.php "${TEMP_DIR}/front/" && echo -e "${GREEN}  ✓ Copiado: front/*.php${NC}"
        fi
    fi
    
    # Carpeta public/css/ (GLPI 11 structure)
    if [ -d "public/css" ]; then
        mkdir -p "${TEMP_DIR}/public/css"
        if ls public/css/*.css >/dev/null 2>&1; then
            cp public/css/*.css "${TEMP_DIR}/public/css/" && echo -e "${GREEN}  ✓ Copiado: public/css/*.css${NC}"
        fi
    fi
    
    # Carpeta locales/
    if [ -d "locales" ]; then
        mkdir -p "${TEMP_DIR}/locales"
        if ls locales/*.php >/dev/null 2>&1; then
            cp locales/*.php "${TEMP_DIR}/locales/" && echo -e "${GREEN}  ✓ Copiado: locales/*.php${NC}"
        fi
    fi
    
    # Carpeta assets/
    if [ -d "assets" ]; then
        mkdir -p "${TEMP_DIR}/assets/screenshots"
        if [ -f "assets/logo.png" ]; then
            cp assets/logo.png "${TEMP_DIR}/assets/" && echo -e "${GREEN}  ✓ Copiado: assets/logo.png${NC}"
        fi
        if [ -d "assets/screenshots" ] && ls assets/screenshots/* >/dev/null 2>&1; then
            cp assets/screenshots/* "${TEMP_DIR}/assets/screenshots/" && echo -e "${GREEN}  ✓ Copiado: assets/screenshots/*${NC}"
        fi
    fi
}

copy_files

echo ""
echo -e "${YELLOW}[2/4] Verificando estructura...${NC}"

# Verificar archivos críticos
ALL_OK=true
CRITICAL_FILES=("setup.php" "plugin.xml" "LICENSE" "README.md" "CHANGELOG.md" "hook.php" "inc/config.class.php" "inc/hook.class.php" "front/config.form.php" "public/css/kanban.css")

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "${TEMP_DIR}/${file}" ]; then
        echo -e "${GREEN}  ✓ ${file}${NC}"
    else
        echo -e "${RED}  ✗ ${file} - FALTA!${NC}"
        ALL_OK=false
    fi
done

if [ "$ALL_OK" = false ]; then
    echo ""
    echo -e "${RED}ERROR: Faltan archivos críticos. Abortando.${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}[3/4] Creando archivo ZIP...${NC}"

# Crear ZIP
cd build
if [ -f "../${ZIP_NAME}" ]; then
    rm "../${ZIP_NAME}"
fi

# En Git Bash para Windows, usar PowerShell para crear el ZIP
if command -v powershell.exe &> /dev/null; then
    # Usar PowerShell en Windows
    powershell.exe -NoProfile -Command "Compress-Archive -Path '${PLUGIN_NAME}' -DestinationPath '../${ZIP_NAME}' -CompressionLevel Optimal" > /dev/null 2>&1
elif command -v zip &> /dev/null; then
    # Usar zip en Linux/Mac
    zip -r "../${ZIP_NAME}" "${PLUGIN_NAME}" > /dev/null 2>&1
else
    echo -e "${RED}ERROR: No se encontró ni zip ni PowerShell${NC}"
    cd ..
    exit 1
fi
cd ..

ZIP_SIZE=$(du -h "${ZIP_NAME}" 2>/dev/null | cut -f1)
if [ -z "$ZIP_SIZE" ]; then
    ZIP_SIZE="unknown"
fi
echo -e "${GREEN}  ✓ Creado: ${ZIP_NAME} (${ZIP_SIZE})${NC}"

echo ""
echo -e "${YELLOW}[4/4] Limpiando archivos temporales...${NC}"
rm -rf ./build
echo -e "${GREEN}  ✓ Limpieza completada${NC}"

echo ""
echo -e "${CYAN}==========================================${NC}"
echo -e "${GREEN}  ✓ Release creada exitosamente!${NC}"
echo -e "${CYAN}==========================================${NC}"
echo ""
echo -e "${NC}Archivo: ${ZIP_NAME}${NC}"
echo ""
echo -e "${YELLOW}Siguiente paso:${NC}"
echo -e "${NC}  1. Ve a: https://github.com/JuanCarlosAcostaPeraba/glpi-kanbanlooksgood-plugin/releases/new${NC}"
echo -e "${NC}  2. Tag: v${VERSION}${NC}"
echo -e "${NC}  3. Título: v${VERSION} - GLPI 11 Native${NC}"
echo -e "${NC}  4. Sube el archivo: ${ZIP_NAME}${NC}"
echo -e "${NC}  5. Descripción: Ver notas en CHANGELOG.md${NC}"
echo -e "${YELLOW}     ⚠️ IMPORTANTE: Esta versión solo funciona con GLPI 11.0.x${NC}"
echo ""
