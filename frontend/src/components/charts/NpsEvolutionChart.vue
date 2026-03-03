<script setup lang="ts">
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js'
import type { NpsEvolucio } from '@/types'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = withDefaults(defineProps<{
  data: NpsEvolucio[]
  height?: number
}>(), {
  height: 250,
})

const chartData = computed(() => {
  return {
    labels: props.data.map(d => d.periode),
    datasets: [
      {
        label: 'NPS Score',
        data: props.data.map(d => d.nps_score),
        borderColor: '#0ea5e9',
        backgroundColor: 'rgba(14, 165, 233, 0.1)',
        fill: true,
        tension: 0.4,
      },
      {
        label: 'Mitjana',
        data: props.data.map(d => d.mitjana * 10), // Scale to compare with NPS
        borderColor: '#8b5cf6',
        borderDash: [5, 5],
        fill: false,
        tension: 0.4,
      },
    ],
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
    },
  },
  scales: {
    y: {
      min: -100,
      max: 100,
      ticks: {
        stepSize: 25,
      },
    },
  },
}
</script>

<template>
  <div :style="{ height: height + 'px' }">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>
