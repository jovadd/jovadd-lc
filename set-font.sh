#!/bin/bash
# set-font.sh — Pre-scarica un font in fonts/ per il tema Jovadd LC
#
# Uso:
#   ./set-font.sh "Poppins"
#   ./set-font.sh "Raleway"
#
# Il font attivo si imposta dal customizer WordPress: Aspetto → Personalizza → Tipografia.
# Questo script serve solo per pre-scaricare font da terminale (es. setup iniziale progetto).
#
# Font disponibili nel catalogo:
#   Inter, DM Sans, Outfit, Nunito, Raleway, Montserrat, Open Sans,
#   Poppins, Lato, Roboto, Playfair Display, Merriweather, Lora, Source Serif 4

set -e

FONT_NAME="${1}"

if [ -z "$FONT_NAME" ]; then
    echo "Uso: ./set-font.sh \"NomeFont\""
    echo "Esempio: ./set-font.sh \"Poppins\""
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FONTS_DIR="$SCRIPT_DIR/fonts"
FONT_SLUG=$(echo "$FONT_NAME" | tr '[:upper:]' '[:lower:]' | tr ' ' '-')

BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo ""
echo -e "${BOLD}Jovadd LC — Scarico font: ${FONT_NAME}${NC}"
echo "────────────────────────────────────"

mkdir -p "$FONTS_DIR"
TMP_FILE="$FONTS_DIR/_tmp_download.woff2"

# Prova font variabile
VF_URL="https://cdn.jsdelivr.net/fontsource/fonts/${FONT_SLUG}:vf@latest/latin-wght-normal.woff2"
VF_FILE="${FONT_SLUG}-latin-wght-normal.woff2"
HTTP_STATUS=$(curl -s -o "$TMP_FILE" -w "%{http_code}" "$VF_URL")

if [ "$HTTP_STATUS" = "200" ] && [ -s "$TMP_FILE" ]; then
    FONT_FILE="$VF_FILE"
    IS_VARIABLE=true
else
    rm -f "$TMP_FILE"
    STATIC_URL="https://cdn.jsdelivr.net/fontsource/fonts/${FONT_SLUG}@latest/latin-400-normal.woff2"
    STATIC_FILE="${FONT_SLUG}-latin-400-normal.woff2"
    HTTP_STATUS=$(curl -s -o "$TMP_FILE" -w "%{http_code}" "$STATIC_URL")

    if [ "$HTTP_STATUS" != "200" ] || [ ! -s "$TMP_FILE" ]; then
        rm -f "$TMP_FILE"
        echo -e "${RED}✗ Font non trovato: \"${FONT_NAME}\"${NC}"
        echo "  Verifica il nome esatto nel catalogo (vedi --help)"
        exit 1
    fi

    FONT_FILE="$STATIC_FILE"
    IS_VARIABLE=false
fi

mv "$TMP_FILE" "$FONTS_DIR/$FONT_FILE"

if [ "$IS_VARIABLE" = true ]; then
    echo -e "${GREEN}✓ Font variabile scaricato${NC} — fonts/${FONT_FILE}"
else
    echo -e "${YELLOW}⚠ Font variabile non disponibile, usato peso 400${NC} — fonts/${FONT_FILE}"
fi

echo ""
echo -e "Font disponibile. Attivalo da:"
echo -e "  ${BOLD}WordPress → Aspetto → Personalizza → Tipografia${NC}"
echo ""
