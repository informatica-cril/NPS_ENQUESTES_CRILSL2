## 🎯 DESPLIEGUE RÁPIDO EN cPANEL

**Si tienes dudas, lee: [DEPLOY_CPANEL.md](DEPLOY_CPANEL.md)**

### 📅 Resumen de pasos:

#### 1️⃣ EN TU PC (Una vez before deploying)
```bash
# En la carpeta VUE_NPM_new
npm install
npm run build
composer install --no-dev
git add .
git commit -m "Production build"
git push origin main
```

#### 2️⃣ EN cPANEL (Primera vez)

- Crea carpeta `/public_html/enquestes/`
- Clona desde GitHub o descarga ZIP
- Copia `.env.example` → `.env`
- Edita `.env` con datos de la BD
- Ejecuta los scripts: `deploy.php`, `key_generator.php`, `optimize.php`, `migrate.php`
- Elimina los scripts después de ejecutarlos

#### 3️⃣ PRÓXIMAS ACTUALIZACIONES

- Tu PC: `npm run build` + Git push
- cPanel: Ejecuta `deploy.php` para hacer git pull
- Done! ✅

### 🔗 URL Final
```
http://enquestes.crilsl.org/
```

### 📞 Contacto / Dudas
Lee el archivo [DEPLOY_CPANEL.md](DEPLOY_CPANEL.md) para instrucciones detalladas.
