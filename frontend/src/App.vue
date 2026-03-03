<script setup lang="ts">
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { onMounted } from 'vue'

const authStore = useAuthStore()

onMounted(async () => {
  // Try to restore session on app load
  if (authStore.token) {
    await authStore.fetchUser()
  }
})
</script>

<template>
  <RouterView />
</template>
