# NPS Enquestes - Frontend (Vue.js 3)

Aplicació SPA desenvolupada amb Vue.js 3 i Composition API per al sistema de gestió d'enquestes NPS.

## 📋 Requisits

- Node.js 18+
- npm 9+ o yarn 1.22+

## 🚀 Instal·lació

### 1. Instal·lar Dependències

```bash
npm install
# o
yarn install
```

### 2. Configurar Entorn

```bash
cp .env.example .env
```

Editar `.env`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME="NPS Enquestes"
VITE_MAPBOX_TOKEN=your_mapbox_token
```

### 3. Iniciar Servidor de Desenvolupament

```bash
npm run dev
```

L'aplicació estarà disponible a `http://localhost:5173`

## 📁 Estructura del Projecte

```
src/
├── assets/
│   └── css/
│       └── main.css          # Estils globals + Tailwind
├── components/
│   ├── charts/
│   │   ├── NpsDistributionChart.vue
│   │   └── NpsEvolutionChart.vue
│   ├── forms/
│   ├── layout/
│   │   └── AppLayout.vue     # Layout principal
│   ├── nps/
│   │   ├── NpsScoreCard.vue
│   │   ├── NpsFiltersPanel.vue
│   │   └── NpsMap.vue
│   └── ui/
├── composables/              # Lògica reutilitzable
├── router/
│   └── index.ts              # Configuració de rutes
├── services/
│   ├── api.ts                # Client Axios
│   ├── auth.ts
│   ├── enquestes.ts
│   ├── nps.ts
│   ├── centres.ts
│   └── public.ts
├── stores/
│   ├── auth.ts               # Pinia store autenticació
│   ├── enquestes.ts          # Pinia store enquestes
│   └── nps.ts                # Pinia store NPS
├── types/
│   └── index.ts              # TypeScript types/interfaces
├── views/
│   ├── auth/
│   │   ├── LoginView.vue
│   │   ├── RegisterView.vue
│   │   ├── ForgotPasswordView.vue
│   │   └── ProfileView.vue
│   ├── dashboard/
│   │   └── DashboardView.vue
│   ├── enquestes/
│   │   ├── EnquestesListView.vue
│   │   ├── EnquestaDetailView.vue
│   │   └── EnquestaFormView.vue
│   ├── nps/
│   │   ├── NpsDashboardView.vue
│   │   ├── NpsEvolucioView.vue
│   │   ├── NpsMapaView.vue
│   │   └── NpsComentarisView.vue
│   ├── centres/
│   ├── fisioterapeutes/
│   ├── pacients/
│   ├── informes/
│   ├── public/
│   │   ├── SurveyView.vue
│   │   └── SurveyCompletedView.vue
│   └── NotFoundView.vue
├── App.vue
└── main.ts
```

## 🛠️ Stack Tecnològic

### Core
- **Vue.js 3** - Composition API
- **TypeScript** - Tipatge estàtic
- **Vite** - Build tool

### Gestió d'Estat
- **Pinia** - State management

### Routing
- **Vue Router 4** - Navegació SPA

### UI/UX
- **Tailwind CSS** - Estils utilitaris
- **Headless UI** - Components accessibles
- **Heroicons** - Icones

### Gràfics i Mapes
- **Chart.js** + **vue-chartjs** - Gràfics
- **Mapbox GL** - Mapes interactius

### Formularis
- **VeeValidate** - Validació de formularis
- **Zod** - Schema validation

### HTTP
- **Axios** - Client HTTP

### Utilitats
- **date-fns** - Manipulació de dates
- **lodash-es** - Utilitats JS
- **@vueuse/core** - Composables Vue

## 📱 Pàgines i Rutes

### Públiques
| Ruta | Component | Descripció |
|------|-----------|------------|
| `/login` | LoginView | Inici de sessió |
| `/register` | RegisterView | Registre |
| `/forgot-password` | ForgotPasswordView | Recuperar contrasenya |
| `/enquesta/:slug` | SurveyView | Enquesta pública |
| `/enquesta/:slug/completada` | SurveyCompletedView | Confirmació |

### Protegides (requereixen autenticació)
| Ruta | Component | Descripció |
|------|-----------|------------|
| `/dashboard` | DashboardView | Dashboard principal |
| `/nps` | NpsDashboardView | Dashboard NPS |
| `/nps/evolucio` | NpsEvolucioView | Evolució temporal |
| `/nps/mapa` | NpsMapaView | Mapa per centres |
| `/nps/comentaris` | NpsComentarisView | Comentaris NPS |
| `/enquestes` | EnquestesListView | Llistat enquestes |
| `/enquestes/create` | EnquestaFormView | Crear enquesta |
| `/enquestes/:id` | EnquestaDetailView | Detall enquesta |
| `/enquestes/:id/edit` | EnquestaFormView | Editar enquesta |
| `/centres` | CentresListView | Llistat centres |
| `/fisioterapeutes` | FisioterapeutesListView | Llistat fisios |
| `/pacients` | PacientsListView | Llistat pacients |
| `/informes` | InformesListView | Llistat informes |
| `/profile` | ProfileView | Perfil d'usuari |

## 🎨 Components Principals

### Charts
- `NpsDistributionChart` - Gràfic de barres amb distribució 0-10
- `NpsEvolutionChart` - Gràfic de línia amb evolució temporal

### NPS
- `NpsScoreCard` - Targeta amb puntuació NPS
- `NpsFiltersPanel` - Filtres de dates i períodes
- `NpsMap` - Mapa amb marcadors per centre

### Layout
- `AppLayout` - Layout amb sidebar i navegació

## 📦 Pinia Stores

### `auth`
- `user` - Usuari actual
- `token` - Token d'autenticació
- `isAuthenticated` - Computed
- `login()`, `logout()`, `fetchUser()`

### `enquestes`
- `enquestes` - Llistat d'enquestes
- `currentEnquesta` - Enquesta seleccionada
- `pagination` - Informació de paginació
- `fetchEnquestes()`, `createEnquesta()`, etc.

### `nps`
- `dashboard` - Estadístiques generals
- `evolucio` - Dades d'evolució
- `perCentre` - NPS per centre
- `filters` - Filtres actius
- `fetchDashboard()`, `fetchEvolucio()`, etc.

## 🧪 Scripts Disponibles

```bash
# Desenvolupament
npm run dev

# Build producció
npm run build

# Preview build
npm run preview

# Lint
npm run lint
```

## 🚀 Desplegament

### Build

```bash
npm run build
```

Generarà la carpeta `dist/` amb els fitxers estàtics.

### Nginx (exemple)

```nginx
server {
    listen 80;
    server_name app.example.com;
    root /var/www/frontend/dist;

    index index.html;

    # SPA fallback
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Proxy API
    location /api {
        proxy_pass http://localhost:8000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

## 🎨 Personalització de Tema

### Colors (tailwind.config.js)

```javascript
colors: {
  primary: {
    50: '#f0f9ff',
    // ...
    600: '#0284c7',
    // ...
  },
  nps: {
    promotor: '#22c55e',  // Verd
    passiu: '#eab308',     // Groc
    detractor: '#ef4444',  // Vermell
  },
}
```

### Classes CSS Globals (main.css)

```css
.btn-primary { @apply bg-primary-600 text-white ...; }
.btn-secondary { @apply bg-white text-gray-700 ...; }
.card { @apply bg-white rounded-lg shadow p-6; }
.input { @apply block w-full rounded-md border-gray-300 ...; }
```

## 🔐 Autenticació

L'autenticació utilitza tokens Bearer (Laravel Sanctum):

1. Login envia credencials a `/api/auth/login`
2. El backend retorna un token
3. El token es guarda a `localStorage`
4. Axios interceptor afegeix el token a cada petició
5. Si el token expira (401), es redirigeix a login

## 📝 TypeScript Types

Totes les interfícies estan definides a `src/types/index.ts`:

- `User`, `Centre`, `Fisioterapeuta`, `Pacient`
- `Enquesta`, `Pregunta`, `Participacio`, `Resposta`
- `NpsResultat`, `NpsEstadistiques`, `NpsEvolucio`
- `Informe`, `PaginatedResponse`, `ApiError`
