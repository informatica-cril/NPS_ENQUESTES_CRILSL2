<script setup lang="ts">
import { HomeIcon } from '@heroicons/vue/24/outline'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
      <h1 class="text-6xl font-bold text-gray-300 mb-4">404</h1>
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Pàgina no trobada</h2>
      <p class="text-gray-600 mb-8">
        La pàgina que busques no existeix o ha estat moguda.
      </p>
      <RouterLink to="/" class="btn-primary">
        <HomeIcon class="h-5 w-5 mr-2" />
        Tornar a l'inici
      </RouterLink>
    </div>
  </div>
</template>
