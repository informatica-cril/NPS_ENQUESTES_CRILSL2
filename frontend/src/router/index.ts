import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Portales
import PacientLoginView from '../views/portals/PacientLoginView.vue'
import FisioLoginView from '../views/portals/FisioLoginView.vue'
import AdminLoginView from '../views/portals/AdminLoginView.vue'

// 404
import NotFoundView from '../views/NotFoundView.vue'


const routes: RouteRecordRaw[] = [
  // Portal logins
  { path: '/admin/login', name: 'admin-login', component: () => import('@/views/portals/AdminLoginView.vue'), meta: { guest: true } },
  { path: '/fisio/login', name: 'fisio-login', component: () => import('@/views/portals/FisioLoginView.vue'), meta: { guest: true } },
  { path: '/pacient/login', name: 'pacient-login', component: () => import('@/views/portals/PacientLoginView.vue'), meta: { guest: true } },

  // Legacy login redirect
  { path: '/login', redirect: '/admin/login' },

  // Public survey routes
  { path: '/enquesta/:slug', name: 'public-survey', component: () => import('@/views/public/SurveyView.vue'), meta: { public: true } },
  { path: '/enquesta/:slug/completada', name: 'survey-completed', component: () => import('@/views/public/SurveyCompletedView.vue'), meta: { public: true } },

  // Admin routes
  {
    path: '/',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'dashboard', component: () => import('@/views/dashboard/DashboardView.vue') },
      { path: 'nps', name: 'nps', component: () => import('@/views/nps/NpsDashboardView.vue') },
      { path: 'nps/evolucio', name: 'nps-evolucio', component: () => import('@/views/nps/NpsEvolucioView.vue') },
      { path: 'nps/mapa', name: 'nps-mapa', component: () => import('@/views/nps/NpsMapaView.vue') },
      { path: 'nps/comentaris', name: 'nps-comentaris', component: () => import('@/views/nps/NpsComentarisView.vue') },
      { path: 'enquestes', name: 'enquestes', component: () => import('@/views/enquestes/EnquestesListView.vue') },
      { path: 'enquestes/create', name: 'enquestes-create', component: () => import('@/views/enquestes/EnquestaFormView.vue') },
      { path: 'enquestes/:id', name: 'enquestes-show', component: () => import('@/views/enquestes/EnquestaDetailView.vue') },
      { path: 'enquestes/:id/edit', name: 'enquestes-edit', component: () => import('@/views/enquestes/EnquestaFormView.vue') },
      { path: 'centres', name: 'centres', component: () => import('@/views/centres/CentresListView.vue') },
      { path: 'centres/:id', name: 'centres-show', component: () => import('@/views/centres/CentreDetailView.vue') },
      { path: 'fisioterapeutes', name: 'fisioterapeutes', component: () => import('@/views/fisioterapeutes/FisioterapeutesListView.vue') },
      { path: 'fisioterapeutes/:id', name: 'fisioterapeutes-show', component: () => import('@/views/fisioterapeutes/FisioterapeutaDetailView.vue') },
      { path: 'pacients', name: 'pacients', component: () => import('@/views/pacients/PacientsListView.vue') },
      { path: 'pacients/:id', name: 'pacients-show', component: () => import('@/views/pacients/PacientDetailView.vue') },
      { path: 'informes', name: 'informes', component: () => import('@/views/informes/InformesListView.vue') },
      { path: 'informes/create', name: 'informes-create', component: () => import('@/views/informes/InformeFormView.vue') },
      { path: 'informes/:id', name: 'informes-show', component: () => import('@/views/informes/InformeDetailView.vue') },
      { path: 'missatges', name: 'missatges', component: () => import('@/views/missatges/MissatgesAdminView.vue') },
      { path: 'profile', name: 'profile', component: () => import('@/views/auth/ProfileView.vue') },
    ],
  },

  // Fisioterapeuta routes
  {
    path: '/fisio',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true, roles: ['fisioterapeuta'] },
    children: [
      { path: '', redirect: '/fisio/dashboard' },
      { path: 'dashboard', name: 'fisio-dashboard', component: () => import('@/views/fisio/FisioDashboardView.vue') },
      { path: 'pacients', name: 'fisio-pacients', component: () => import('@/views/pacients/PacientsListView.vue') },
      { path: 'pacients/:id', name: 'fisio-pacient-detail', component: () => import('@/views/pacients/PacientDetailView.vue') },
      { path: 'nps', name: 'fisio-nps', component: () => import('@/views/nps/NpsDashboardView.vue') },
      { path: 'informes', name: 'fisio-informes', component: () => import('@/views/informes/InformesListView.vue') },
      { path: 'enquestes', name: 'fisio-enquestes', component: () => import('@/views/enquestes/EnquestesListView.vue') },
      { path: 'profile', name: 'fisio-profile', component: () => import('@/views/auth/ProfileView.vue') },
    ],
  },

  // Pacient routes
  {
    path: '/pacient',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true, roles: ['pacient'] },
    children: [
      { path: '', redirect: '/pacient/dashboard' },
      { path: 'dashboard', name: 'pacient-dashboard', component: () => import('@/views/pacient/PacientDashboardView.vue') },
      { path: 'enquestes', name: 'pacient-enquestes', component: () => import('@/views/enquestes/EnquestesListView.vue') },
      { path: 'nps', name: 'pacient-nps', component: () => import('@/views/nps/NpsDashboardView.vue') },
      { path: 'profile', name: 'pacient-profile', component: () => import('@/views/auth/ProfileView.vue') },
    ],
  },

  // 404
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/views/NotFoundView.vue') },
]

const router = createRouter({
  history: createWebHistory('/'),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.public) return next()

  if (to.meta.guest && authStore.isAuthenticated) {
    if (authStore.user?.role === 'fisioterapeuta') return next({ name: 'fisio-dashboard' })
    if (authStore.user?.role === 'pacient') return next({ name: 'pacient-dashboard' })
    return next({ name: 'dashboard' })
  }

  if (to.meta.requiresAuth) {
    if (!authStore.token) {
      if (to.path.startsWith('/fisio')) return next({ name: 'fisio-login' })
      if (to.path.startsWith('/pacient')) return next({ name: 'pacient-login' })
      return next({ name: 'admin-login' })
    }

    if (!authStore.user) {
      try {
        await authStore.fetchUser()
      } catch (e) {
        if (to.path.startsWith('/fisio')) return next({ name: 'fisio-login' })
        if (to.path.startsWith('/pacient')) return next({ name: 'pacient-login' })
        return next({ name: 'admin-login' })
      }
    }

    if (!authStore.isAuthenticated) {
      if (to.path.startsWith('/fisio')) return next({ name: 'fisio-login' })
      if (to.path.startsWith('/pacient')) return next({ name: 'pacient-login' })
      return next({ name: 'admin-login' })
    }

    const requiredRoles = to.meta.roles as string[] | undefined
    if (requiredRoles && !requiredRoles.includes(authStore.user?.role as string)) {
      if (authStore.user?.role === 'fisioterapeuta') return next({ name: 'fisio-dashboard' })
      if (authStore.user?.role === 'pacient') return next({ name: 'pacient-dashboard' })
      return next({ name: 'dashboard' })
    }
  }

  next()
})

export default router
