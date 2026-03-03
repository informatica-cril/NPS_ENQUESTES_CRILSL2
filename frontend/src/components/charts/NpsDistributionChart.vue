<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps<{
  data: Record<number, number>
}>()

const chartData = computed(() => {
  const labels = Array.from({ length: 11 }, (_, i) => String(i))
  const values = labels.map(label => props.data[parseInt(label)] || 0)

  const colors = labels.map(label => {
    const score = parseInt(label)
    if (score >= 9) return '#22c55e' // promotor
    if (score >= 7) return '#eab308' // passiu
    return '#ef4444' // detractor
  })

  return {
    labels,
    datasets: [
      {
        label: 'Respostes',
        data: values,
        backgroundColor: colors,
        borderRadius: 4,
      },
    ],
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        stepSize: 1,
      },
    },
  },
}
</script>

<template>
  <div style="height: 250px;">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>
