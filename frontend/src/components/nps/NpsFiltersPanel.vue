<script setup lang="ts">
import { ref, watch } from 'vue'
import { useNpsStore } from '@/stores/nps'
import { format, subMonths, subYears } from 'date-fns'

const emit = defineEmits<{
  change: []
}>()

const npsStore = useNpsStore()

const presets = [
  { label: 'Últims 3 mesos', months: 3 },
  { label: 'Últims 6 mesos', months: 6 },
  { label: 'Últim any', months: 12 },
]

function applyPreset(months: number) {
  npsStore.updateFilters({
    data_inici: format(subMonths(new Date(), months), 'yyyy-MM-dd'),
    data_fi: format(new Date(), 'yyyy-MM-dd'),
  })
  emit('change')
}

function handleDateChange() {
  emit('change')
}
</script>

<template>
  <div class="flex flex-wrap items-center gap-4">
    <div class="flex gap-2">
      <button
        v-for="preset in presets"
        :key="preset.months"
        @click="applyPreset(preset.months)"
        class="px-3 py-1.5 text-sm rounded-md bg-gray-100 hover:bg-gray-200 transition-colors"
      >
        {{ preset.label }}
      </button>
    </div>

    <div class="flex items-center gap-2">
      <label class="text-sm text-gray-600">Des de:</label>
      <input
        type="date"
        :value="npsStore.filters.data_inici"
        @change="(e) => { npsStore.updateFilters({ data_inici: (e.target as HTMLInputElement).value }); handleDateChange() }"
        class="input w-auto"
      />
    </div>

    <div class="flex items-center gap-2">
      <label class="text-sm text-gray-600">Fins:</label>
      <input
        type="date"
        :value="npsStore.filters.data_fi"
        @change="(e) => { npsStore.updateFilters({ data_fi: (e.target as HTMLInputElement).value }); handleDateChange() }"
        class="input w-auto"
      />
    </div>
  </div>
</template>
