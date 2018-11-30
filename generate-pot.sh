#!/bin/sh

xgettext --from-code=UTF-8 -k_e -k_x -k__ --default-domain=static-maker-deploy-extra -o languages/default.pot $(find . -name "*.php"  -not -path "./node_modules/*" -not -path "./vendor/*")

