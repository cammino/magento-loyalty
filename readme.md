# Instalation
### adding submodule
```bash
cd ~/project-directory
git submodule add git@github.com:cammino/magento-loyalty.git app/code/community/Cammino/Loyalty
```

### Copying files
```bash
cp app/code/community/Cammino/Loyalty/files/adminhtml_loyalty.xml app/design/adminhtml/default/default/layout/loyalty.xml
cp app/code/community/Cammino/Loyalty/files/frontend_loyalty.xml app/design/frontend/cammino/NOME_DO_TEMA/layout/loyalty.xml
cp -r app/code/community/Cammino/Loyalty/files/loyalty/ app/design/frontend/cammino/NOME_DO_TEMA/template/loyalty
cp app/code/community/Cammino/Loyalty/files/Cammino_Loyalty.xml app/etc/modules/
cp app/code/community/Cammino/Loyalty/files/icon-loyalty.svg skin/frontend/cammino/NOME_DO_TEMA/images/
cp app/code/community/Cammino/Loyalty/files/_loyalty.sass skin/frontend/cammino/NOME_DO_TEMA/sass/module/
```

### Adding sass to main style file
Enter in main style.sass file and call the last file added above (**_loyalty.sass**)
Example: (skin/frontend/cammino/NOME_DO_TEMA/sass/styles.sass)
```sass
...
@import module/review
@import module/search
@import module/template-institutional
@import module/toolbar

# Add the reference for the file here:
@import module/loyalty
```

**Re-build style.sass**