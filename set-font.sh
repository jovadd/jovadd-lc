#!/bin/bash
# set-font.sh — Cambia il font self-hosted del tema Jovadd LC
#
# Uso:
#   ./set-font.sh "Poppins"
#   ./set-font.sh "Raleway"
#   ./set-font.sh "Inter"      ← ripristina il default
#
# Cerca il nome esatto del font su https://fontsource.org
# Prova prima il font variabile (.woff2-variations), poi fallback statico 400.

set -e

FONT_NAME="${1}"

if [ -z "$FONT_NAME" ]; then
    echo "Uso: ./set-font.sh \"NomeFont\""
    echo "Esempio: ./set-font.sh \"Poppins\""
    echo ""
    echo "Cerca il nome su https://fontsource.org"
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FONTS_DIR="$SCRIPT_DIR/fonts"
FUNCTIONS_PHP="$SCRIPT_DIR/functions.php"
THEME_VARS="$SCRIPT_DIR/sass/_theme_variables.scss"

# fontsource slug: lowercase, spazi → trattini
FONT_SLUG=$(echo "$FONT_NAME" | tr '[:upper:]' '[:lower:]' | tr ' ' '-')

BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo ""
echo -e "${BOLD}Jovadd LC — Set Font: ${FONT_NAME}${NC}"
echo "────────────────────────────────────"

# ── 1. Scarica il font ───────────────────────────────────────────────────────

mkdir -p "$FONTS_DIR"

# Prova font variabile
VF_URL="https://cdn.jsdelivr.net/fontsource/fonts/${FONT_SLUG}:vf@latest/latin-wght-normal.woff2"
VF_FILE="${FONT_SLUG}-latin-wght-normal.woff2"
TMP_FILE="$FONTS_DIR/_tmp_download.woff2"

HTTP_STATUS=$(curl -s -o "$TMP_FILE" -w "%{http_code}" "$VF_URL")

if [ "$HTTP_STATUS" = "200" ] && [ -s "$TMP_FILE" ]; then
    FONT_FILE="$VF_FILE"
    FORMAT="woff2-variations"
    WEIGHT_RANGE="100 900"
    IS_VARIABLE=true
else
    # Fallback: font statico peso 400
    rm -f "$TMP_FILE"
    STATIC_URL="https://cdn.jsdelivr.net/fontsource/fonts/${FONT_SLUG}@latest/latin-400-normal.woff2"
    STATIC_FILE="${FONT_SLUG}-latin-400-normal.woff2"
    HTTP_STATUS=$(curl -s -o "$TMP_FILE" -w "%{http_code}" "$STATIC_URL")

    if [ "$HTTP_STATUS" != "200" ] || [ ! -s "$TMP_FILE" ]; then
        rm -f "$TMP_FILE"
        echo -e "${RED}✗ Font non trovato: \"${FONT_NAME}\"${NC}"
        echo "  Verifica il nome esatto su https://fontsource.org"
        exit 1
    fi

    FONT_FILE="$STATIC_FILE"
    FORMAT="woff2"
    WEIGHT_RANGE="400"
    IS_VARIABLE=false
fi

# Salva il nuovo font, poi rimuove i vecchi
mv "$TMP_FILE" "$FONTS_DIR/$FONT_FILE"
find "$FONTS_DIR" -name "*.woff2" ! -name "$FONT_FILE" -delete

if [ "$IS_VARIABLE" = true ]; then
    echo -e "${GREEN}✓ Font variabile scaricato${NC} — ${FONT_FILE}"
else
    echo -e "${YELLOW}⚠ Font variabile non disponibile, usato peso 400${NC} — ${FONT_FILE}"
fi

# ── 2. Aggiorna functions.php ────────────────────────────────────────────────

python3 - "$FUNCTIONS_PHP" "$FONT_NAME" "$FONT_FILE" "$FORMAT" "$WEIGHT_RANGE" <<'PYEOF'
import sys, re

php_file, font_name, font_file, fmt, weight = sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4], sys.argv[5]

new_block = (
    "// FONT SELF-HOSTED (GDPR) — @jovadd-font-start\n"
    "add_action( 'wp_head', function() {\n"
    "    $font_url = get_template_directory_uri() . '/fonts/" + font_file + "';\n"
    "    echo '<style>\n"
    "@font-face {\n"
    "    font-family: \"" + font_name + "\";\n"
    "    font-style: normal;\n"
    "    font-display: swap;\n"
    "    font-weight: " + weight + ";\n"
    "    src: url(' . esc_url( $font_url ) . ') format(\"" + fmt + "\");\n"
    "    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;\n"
    "}\n"
    "</style>' . \"\\n\";\n"
    "}, 1 );\n"
    "// @jovadd-font-end"
)

content = open(php_file).read()
updated = re.sub(
    r'// FONT SELF-HOSTED \(GDPR\).*?// @jovadd-font-end',
    new_block,
    content,
    flags=re.DOTALL
)

if updated == content:
    print("WARN: markers non trovati in functions.php — blocco font non aggiornato")
    sys.exit(1)

open(php_file, 'w').write(updated)
PYEOF

echo -e "${GREEN}✓ functions.php aggiornato${NC}"

# ── 3. Aggiorna _theme_variables.scss ───────────────────────────────────────

sed -i '' "s/^\\\$font-family-base:.*!default;/\$font-family-base: \"${FONT_NAME}\", sans-serif !default;/" "$THEME_VARS"
sed -i '' "s/^\\\$headings-font-family:.*!default;/\$headings-font-family: \"${FONT_NAME}\", sans-serif !default;/" "$THEME_VARS"

echo -e "${GREEN}✓ _theme_variables.scss aggiornato${NC}"

# ── 4. Riepilogo ─────────────────────────────────────────────────────────────

echo ""
echo -e "${BOLD}Font impostato:${NC} ${FONT_NAME}"
echo -e "${BOLD}File:${NC}          fonts/${FONT_FILE}"
if [ "$IS_VARIABLE" = true ]; then
    echo -e "${BOLD}Tipo:${NC}          Variabile (pesi 100–900)"
else
    echo -e "${BOLD}Tipo:${NC}          Statico (peso 400)"
fi
echo ""
echo "Prossimo step: ricompila il CSS dal customizer WordPress."
echo ""
