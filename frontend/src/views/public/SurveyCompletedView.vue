<script setup lang="ts">
import { CheckCircleIcon } from '@heroicons/vue/24/outline'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
      <div class="card py-12">
        <CheckCircleIcon class="h-16 w-16 text-green-500 mx-auto mb-6" />
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
          Gràcies per la teva participació!
        </h1>
        <p class="text-gray-600">
          Les teves respostes s'han enregistrat correctament.
          La teva opinió ens ajuda a millorar els nostres serveis.
        </p>
      </div>
    </div>
  </div>
</template>
