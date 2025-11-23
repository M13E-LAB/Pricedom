#!/bin/bash

# Script d'auto-déploiement pour Zyma0.5
# Automatise git add, commit et push après modifications

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔄 Auto-déploiement Zyma0.5${NC}"
echo "=================================="

# Vérifier si on est dans un repo git
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Erreur: Pas dans un repository git${NC}"
    exit 1
fi

# Vérifier s'il y a des changements
if [ -z "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}ℹ️  Aucun changement détecté${NC}"
    exit 0
fi

# Afficher les changements
echo -e "${BLUE}📝 Changements détectés:${NC}"
git status --short

# Message de commit par défaut ou personnalisé
COMMIT_MSG="${1:-"Auto-deploy: modifications via assistant IA $(date '+%Y-%m-%d %H:%M:%S')"}"

echo -e "\n${BLUE}🚀 Déploiement en cours...${NC}"

# Git add
echo -e "${YELLOW}📦 Ajout des fichiers...${NC}"
git add .

# Git commit
echo -e "${YELLOW}💾 Commit avec message: ${COMMIT_MSG}${NC}"
git commit -m "$COMMIT_MSG"

# Git push
echo -e "${YELLOW}⬆️  Push vers GitHub...${NC}"
git push origin main

echo -e "\n${GREEN}✅ Déploiement terminé avec succès !${NC}"
echo -e "${GREEN}🌐 Railway va automatiquement redéployer votre app${NC}"
echo -e "${BLUE}🔗 Vérifiez le déploiement sur: https://railway.app${NC}" 