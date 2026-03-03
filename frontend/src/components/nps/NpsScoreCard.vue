<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  score: number | null
  total: number
  mitjana?: number | null
}>()

const scoreClass = computed(() => {
  if (props.score === null) return 'text-gray-400'
  if (props.score >= 50) return 'text-nps-promotor'
  if (props.score >= 0) return 'text-nps-passiu'
  return 'text-nps-detractor'
})

const scoreLabel = computed(() => {
  if (props.score === null) return 'Sense dades'
  if (props.score >= 50) return 'Excel·lent'
  if (props.score >= 0) return 'Bo'
  if (props.score >= -50) return 'Millorable'
  return 'Crític'
})
</script>

<template>
  <div class="card">
    <h3 class="text-sm font-medium text-gray-500">Puntuació NPS</h3>
    <p :class="['mt-2 nps-score', scoreClass]">
      {{ score !== null ? score : '-' }}
    </p>
    <p class="text-sm text-gray-500">{{ scoreLabel }}</p>
    <div class="mt-2 text-xs text-gray-400">
      <span>{{ total }} respostes</span>
      <span v-if="mitjana !== null && mitjana !== undefined" class="ml-2">
        · Mitjana: {{ mitjana?.toFixed(1) }}
      </span>
    </div>
  </div>
</template>
