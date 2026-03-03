# Guia de Migració - Next.js a Laravel + Vue.js

Aquesta guia documenta els punts clau per migrar l'aplicació original (Next.js) al nou stack (Laravel + Vue.js).

## 🔄 Correspondències de Components

### Autenticació

| Original (Next.js) | Nou (Laravel + Vue.js) |
|--------------------|------------------------|
| NextAuth | Laravel Sanctum |
| next-auth/prisma-adapter | Eloquent models |
| JWT sessions | Token-based auth |
| Middleware auth | Vue Router guards + API middleware |

### Base de Dades

| Original | Nou |
|----------|-----|
| Prisma ORM | Eloquent ORM |
| PostgreSQL | MySQL |
| Prisma migrations | Laravel migrations |
| prisma/schema.prisma | database/migrations/*.php |

### Gestió d'Estat

| Original (React) | Nou (Vue.js) |
|------------------|---------------|
| TanStack Query | Pinia + services |
| Zustand | Pinia |
| Jotai | Pinia / provide-inject |
| React Context | Pinia / composables |

### Formularis

| Original | Nou |
|----------|-----|
| React Hook Form | VeeValidate |
| Zod/Yup schemas | Zod schemas |
| Formik | VeeValidate |

### UI Components

| Original | Nou |
|----------|-----|
| Radix UI | Headless UI |
| Lucide icons | Heroicons |
| next/image | img nativa / lazy loading |
| next/link | RouterLink |

### Gràfics

| Original | Nou |
|----------|-----|
| Chart.js + react-chartjs-2 | Chart.js + vue-chartjs |
| Recharts | Chart.js (simplificat) |
| Plotly.js | Chart.js (o mantenir Plotly) |

### Mapes

| Original | Nou |
|----------|-----|
| mapbox-gl (React) | mapbox-gl (Vue) |

### API Routes

| Original (Next.js) | Nou (Laravel) |
|--------------------|---------------|
| /api/* (App Router) | routes/api.php |
| Server Actions | Controller methods |
| getServerSideProps | API calls from Vue |

## 📊 Migració del Model de Dades

### Prisma → Eloquent

**Prisma Schema:**
```prisma
model User {
  id        String   @id @default(cuid())
  email     String   @unique
  name      String?
  role      Role     @default(VIEWER)
  createdAt DateTime @default(now())
  surveys   Survey[]
}
```

**Laravel Migration:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('name')->nullable();
    $table->enum('role', ['admin', 'viewer']);
    $table->timestamps();
});
```

**Eloquent Model:**
```php
class User extends Authenticatable {
    protected $fillable = ['email', 'name', 'role'];
    
    public function surveys() {
        return $this->hasMany(Survey::class);
    }
}
```

## 🔐 Migració d'Autenticació

### NextAuth → Laravel Sanctum

**Original (NextAuth):**
```typescript
// pages/api/auth/[...nextauth].ts
export default NextAuth({
  adapter: PrismaAdapter(prisma),
  providers: [CredentialsProvider(...)],
  callbacks: { session, jwt }
})
```

**Nou (Laravel Sanctum):**
```php
// AuthController.php
public function login(Request $request) {
    if (!Auth::attempt($request->only('email', 'password'))) {
        throw ValidationException::withMessages([...]);
    }
    return response()->json([
        'user' => Auth::user(),
        'token' => Auth::user()->createToken('auth')->plainTextToken,
    ]);
}
```

**Vue Auth Store:**
```typescript
// stores/auth.ts
export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token'))
  
  async function login(credentials) {
    const response = await authService.login(credentials)
    token.value = response.token
    localStorage.setItem('auth_token', response.token)
  }
})
```

## 📡 Migració de Crides API

### React Query → Pinia + Services

**Original (TanStack Query):**
```typescript
const { data, isLoading } = useQuery({
  queryKey: ['surveys'],
  queryFn: () => fetch('/api/surveys').then(r => r.json())
})
```

**Nou (Pinia):**
```typescript
// services/enquestes.ts
export const enquestaService = {
  async list(filters) {
    const { data } = await api.get('/enquestes', { params: filters })
    return data
  }
}

// stores/enquestes.ts
export const useEnquestesStore = defineStore('enquestes', () => {
  const enquestes = ref([])
  const loading = ref(false)
  
  async function fetchEnquestes(filters) {
    loading.value = true
    enquestes.value = await enquestaService.list(filters)
    loading.value = false
  }
  
  return { enquestes, loading, fetchEnquestes }
})

// Component.vue
const store = useEnquestesStore()
onMounted(() => store.fetchEnquestes())
```

## 🎨 Migració de Components UI

### React → Vue

**Original (React):**
```tsx
const Button = ({ onClick, children, variant = 'primary' }) => (
  <button 
    className={cn('btn', `btn-${variant}`)}
    onClick={onClick}
  >
    {children}
  </button>
)
```

**Nou (Vue):**
```vue
<script setup>
defineProps({
  variant: { type: String, default: 'primary' }
})
const emit = defineEmits(['click'])
</script>

<template>
  <button 
    :class="['btn', `btn-${variant}`]"
    @click="emit('click')"
  >
    <slot />
  </button>
</template>
```

## 📧 Migració d'Email

### Nodemailer → Laravel Mail

**Original:**
```typescript
import nodemailer from 'nodemailer'

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: 587,
  auth: { user: '...', pass: '...' }
})

await transporter.sendMail({ to, subject, html })
```

**Nou (Laravel):**
```php
// config/mail.php configuració
// app/Mail/SurveyInvitation.php
class SurveyInvitation extends Mailable {
    public function content() {
        return new Content(view: 'emails.survey-invitation');
    }
}

// Enviar
Mail::to($user)->send(new SurveyInvitation($survey));
```

## 📁 Migració d'Emmagatzematge de Fitxers

### AWS S3

**Original:**
```typescript
import { S3Client, PutObjectCommand } from '@aws-sdk/client-s3'
```

**Nou (Laravel):**
```php
// config/filesystems.php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
]

// Ús
Storage::disk('s3')->put('path/file.pdf', $contents);
$url = Storage::disk('s3')->url('path/file.pdf');
```

## ✅ Checklist de Migració

### Backend
- [ ] Configurar projecte Laravel
- [ ] Crear migracions equivalents a Prisma schema
- [ ] Implementar models Eloquent amb relacions
- [ ] Crear controladors API
- [ ] Configurar Sanctum per autenticació
- [ ] Implementar serveis de negoci (NpsService, etc.)
- [ ] Configurar CORS per al frontend
- [ ] Configurar emmagatzematge de fitxers
- [ ] Configurar enviament d'emails
- [ ] Crear seeders amb dades d'exemple
- [ ] Escriure tests

### Frontend
- [ ] Configurar projecte Vue.js amb Vite
- [ ] Configurar Tailwind CSS
- [ ] Crear tipus TypeScript
- [ ] Implementar serveis API (Axios)
- [ ] Crear Pinia stores
- [ ] Configurar Vue Router amb guards
- [ ] Migrar components UI
- [ ] Migrar vistes/pàgines
- [ ] Integrar Chart.js
- [ ] Integrar Mapbox GL
- [ ] Configurar VeeValidate
- [ ] Testejar responsivitat

### Integració
- [ ] Testejar autenticació end-to-end
- [ ] Testejar flux d'enquestes
- [ ] Testejar dashboard NPS
- [ ] Testejar exportació de dades
- [ ] Testejar enviament d'emails
- [ ] Preparar entorns de staging/producció
