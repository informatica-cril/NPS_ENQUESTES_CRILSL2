<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import mapboxgl from 'mapbox-gl'
import type { CentreNps } from '@/services/nps'

const props = defineProps<{
  centres: CentreNps[]
}>()

const mapContainer = ref<HTMLElement | null>(null)
let map: mapboxgl.Map | null = null

onMounted(() => {
  if (!mapContainer.value) return

  mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN || ''

  // Default to Catalonia center if no token
  const defaultCenter: [number, number] = [0.9, 41.6]

  map = new mapboxgl.Map({
    container: mapContainer.value,
    style: 'mapbox://styles/mapbox/light-v11',
    center: defaultCenter,
    zoom: 8,
  })

  map.addControl(new mapboxgl.NavigationControl())

  addMarkers()
})

watch(() => props.centres, () => {
  addMarkers()
})

function addMarkers() {
  if (!map) return

  // Remove existing markers
  document.querySelectorAll('.mapboxgl-marker').forEach(el => el.remove())

  props.centres.forEach(centre => {
    if (!centre.coordinates || !map) return

    const color = centre.nps_score === null
      ? '#9CA3AF'
      : centre.nps_score >= 50
        ? '#22c55e'
        : centre.nps_score >= 0
          ? '#eab308'
          : '#ef4444'

    const el = document.createElement('div')
    el.className = 'marker'
    el.style.cssText = `
      width: 40px;
      height: 40px;
      background-color: ${color};
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      font-size: 12px;
      border: 3px solid white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    `
    el.textContent = centre.nps_score !== null ? String(centre.nps_score) : '-'

    new mapboxgl.Marker(el)
      .setLngLat([centre.coordinates.lng, centre.coordinates.lat])
      .setPopup(
        new mapboxgl.Popup({ offset: 25 })
          .setHTML(`
            <div style="padding: 8px;">
              <strong>${centre.nom}</strong><br/>
              NPS: ${centre.nps_score ?? '-'}<br/>
              Respostes: ${centre.total_respostes}
            </div>
          `)
      )
      .addTo(map)
  })
}
</script>

<template>
  <div ref="mapContainer" class="w-full h-full min-h-[400px]"></div>
</template>
