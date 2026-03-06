<script setup lang="ts">
import { ref } from 'vue'
import { RouterView, RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Bars3Icon,
  XMarkIcon,
  HomeIcon,
  ChartBarIcon,
  ClipboardDocumentListIcon,
  BuildingOffice2Icon,
  UserGroupIcon,
  UsersIcon,
  DocumentTextIcon,
  ArrowRightOnRectangleIcon,
  UserCircleIcon,
  MapIcon,
  ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()
const sidebarOpen = ref(false)

const adminNav = [
  { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
  { name: 'NPS Dashboard', href: '/nps', icon: ChartBarIcon },
  { name: 'Mapa NPS', href: '/nps/mapa', icon: MapIcon },
  { name: 'Comentaris', href: '/nps/comentaris', icon: ChatBubbleLeftRightIcon },
  { name: 'Enquestes', href: '/enquestes', icon: ClipboardDocumentListIcon },
  { name: 'Centres', href: '/centres', icon: BuildingOffice2Icon },
  { name: 'Fisioterapeutes', href: '/fisioterapeutes', icon: UserGroupIcon },
  { name: 'Pacients', href: '/pacients', icon: UsersIcon },
  { name: 'Informes', href: '/informes', icon: DocumentTextIcon },
]
const fisioNav = [
  { name: 'Dashboard', href: '/fisio/dashboard', icon: HomeIcon },
]
const pacientNav = [
  { name: 'Les meves Enquestes', href: '/pacient/enquestes', icon: ClipboardDocumentListIcon },
]
import { computed } from 'vue'
const navigation = computed(() => {
  if (authStore.user?.role === 'fisioterapeuta') return fisioNav
  if (authStore.user?.role === 'pacient') return pacientNav
  return adminNav
})

async function handleLogout() {
  const role = authStore.user?.role
  await authStore.logout()
  if (role === 'pacient') window.location.href = '/pacient/login'
  else if (role === 'fisioterapeuta') window.location.href = '/fisio/login'
  else window.location.href = '/admin/login'
}

function isActive(href: string): boolean {
  return route.path === href || route.path.startsWith(href + '/')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile sidebar -->
    <Teleport to="body">
      <div v-if="sidebarOpen" class="relative z-50 lg:hidden">
        <div class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false" />
        <div class="fixed inset-0 flex">
          <div class="relative mr-16 flex w-full max-w-xs flex-1">
            <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
              <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                <XMarkIcon class="h-6 w-6 text-white" />
              </button>
            </div>
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-primary-700 px-6 pb-4">
              <div class="flex h-16 shrink-0 items-center">
                <span class="text-xl font-bold text-white">NPS Enquestes</span>
              </div>
              <nav class="flex flex-1 flex-col">
                <ul class="flex flex-1 flex-col gap-y-7">
                  <li>
                    <ul class="-mx-2 space-y-1">
                      <li v-for="item in navigation" :key="item.name">
                        <RouterLink
                          :to="item.href"
                          :class="[
                            isActive(item.href)
                              ? 'bg-primary-800 text-white'
                              : 'text-primary-200 hover:text-white hover:bg-primary-800',
                            'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold',
                          ]"
                          @click="sidebarOpen = false"
                        >
                          <component :is="item.icon" class="h-6 w-6 shrink-0" />
                          {{ item.name }}
                        </RouterLink>
                      </li>
                    </ul>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Desktop sidebar -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
      <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-primary-700 px-6 pb-4">
        <div class="flex h-16 shrink-0 items-center">
          <span class="text-xl font-bold text-white">NPS Enquestes</span>
        </div>
        <nav class="flex flex-1 flex-col">
          <ul class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul class="-mx-2 space-y-1">
                <li v-for="item in navigation" :key="item.name">
                  <RouterLink
                    :to="item.href"
                    :class="[
                      isActive(item.href)
                        ? 'bg-primary-800 text-white'
                        : 'text-primary-200 hover:text-white hover:bg-primary-800',
                      'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold',
                    ]"
                  >
                    <component :is="item.icon" class="h-6 w-6 shrink-0" />
                    {{ item.name }}
                  </RouterLink>
                </li>
              </ul>
            </li>
            <li class="mt-auto">
              <RouterLink
                to="/profile"
                class="group -mx-2 flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-primary-200 hover:bg-primary-800 hover:text-white"
              >
                <UserCircleIcon class="h-6 w-6 shrink-0" />
                {{ authStore.user?.name }}
              </RouterLink>
              <button
                @click="handleLogout"
                class="group -mx-2 flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-primary-200 hover:bg-primary-800 hover:text-white"
              >
                <ArrowRightOnRectangleIcon class="h-6 w-6 shrink-0" />
                Tancar sessió
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Top bar -->
      <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
        <button
          type="button"
          class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
          @click="sidebarOpen = true"
        >
          <Bars3Icon class="h-6 w-6" />
        </button>
        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
          <div class="flex flex-1"></div>
          <div class="flex items-center gap-x-4 lg:gap-x-6">
            <span class="hidden lg:flex lg:items-center">
              <span class="text-sm font-semibold text-gray-900">
                {{ authStore.user?.name }}
              </span>
            </span>
          </div>
        </div>
      </div>

      <!-- Page content -->
      <main class="py-6">
        <div class="px-4 sm:px-6 lg:px-8">
          <RouterView />
        </div>
      </main>
    </div>
  </div>
</template>
