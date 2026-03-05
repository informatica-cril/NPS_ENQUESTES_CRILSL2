import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  // Public routes
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { guest: true },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { guest: true },
  },

  // Public survey routes
  {
    path: '/enquesta/:slug',
    name: 'public-survey',
    component: () => import('@/views/public/SurveyView.vue'),
    meta: { public: true },
  },
  {
    path: '/enquesta/:slug/completada',
    name: 'survey-completed',
    component: () => import('@/views/public/SurveyCompletedView.vue'),
    meta: { public: true },
  },

  // Protected routes (with layout)
  {
    path: '/',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/views/dashboard/DashboardView.vue'),
      },

      // NPS
      {
        path: 'nps',
        name: 'nps',
        component: () => import('@/views/nps/NpsDashboardView.vue'),
      },
      {
        path: 'nps/evolucio',
        name: 'nps-evolucio',
        component: () => import('@/views/nps/NpsEvolucioView.vue'),
      },
      {
        path: 'nps/mapa',
        name: 'nps-mapa',
        component: () => import('@/views/nps/NpsMapaView.vue'),
      },
      {
        path: 'nps/comentaris',
        name: 'nps-comentaris',
        component: () => import('@/views/nps/NpsComentarisView.vue'),
      },

      // Enquestes
      {
        path: 'enquestes',
        name: 'enquestes',
        component: () => import('@/views/enquestes/EnquestesListView.vue'),
      },
      {
        path: 'enquestes/create',
        name: 'enquestes-create',
        component: () => import('@/views/enquestes/EnquestaFormView.vue'),
      },
      {
        path: 'enquestes/:id',
        name: 'enquestes-show',
        component: () => import('@/views/enquestes/EnquestaDetailView.vue'),
      },
      {
        path: 'enquestes/:id/edit',
        name: 'enquestes-edit',
        component: () => import('@/views/enquestes/EnquestaFormView.vue'),
      },

      // Centres
      {
        path: 'centres',
        name: 'centres',
        component: () => import('@/views/centres/CentresListView.vue'),
      },
      {
        path: 'centres/:id',
        name: 'centres-show',
        component: () => import('@/views/centres/CentreDetailView.vue'),
      },

      // Fisioterapeutes
      {
        path: 'fisioterapeutes',
        name: 'fisioterapeutes',
        component: () => import('@/views/fisioterapeutes/FisioterapeutesListView.vue'),
      },
      {
        path: 'fisioterapeutes/:id',
        name: 'fisioterapeutes-show',
        component: () => import('@/views/fisioterapeutes/FisioterapeutaDetailView.vue'),
      },

      // Pacients
      {
        path: 'pacients',
        name: 'pacients',
        component: () => import('@/views/pacients/PacientsListView.vue'),
      },
      {
        path: 'pacients/:id',
        name: 'pacients-show',
        component: () => import('@/views/pacients/PacientDetailView.vue'),
      },

      // Informes
      {
        path: 'informes',
        name: 'informes',
        component: () => import('@/views/informes/InformesListView.vue'),
      },
      {
        path: 'informes/create',
        name: 'informes-create',
        component: () => import('@/views/informes/InformeFormView.vue'),
      },
      {
        path: 'informes/:id',
        name: 'informes-show',
        component: () => import('@/views/informes/InformeDetailView.vue'),
      },

      // Profile
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/views/auth/ProfileView.vue'),
      },
    ],
  },

  // 404
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory('/'),
  routes,
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Skip for public routes
  if (to.meta.public) {
    return next()
  }

  // Redirect authenticated users away from guest pages
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  // Check auth for protected routes
  if (to.meta.requiresAuth) {
    if (!authStore.token) {
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }

    // Fetch user if not loaded
    if (!authStore.user) {
      try {
        await authStore.fetchUser()
      } catch (e) {
        return next({ name: 'login', query: { redirect: to.fullPath } })
      }
    }

    if (!authStore.isAuthenticated) {
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }
  }

  next()
})

export default router
