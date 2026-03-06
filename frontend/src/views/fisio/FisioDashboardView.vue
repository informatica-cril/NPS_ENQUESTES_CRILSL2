<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNpsStore } from '@/stores/nps'
import api from '@/services/api'
const authStore = useAuthStore()
const npsStore = useNpsStore()
const loading = ref(true)
const loadingMsg = ref(false)
const loadingPacients = ref(false)
const pacients = ref<any[]>([])
const missatges = ref<any[]>([])
const nouMissatge = ref('')
const enviant = ref(false)
const chatEl = ref<HTMLElement | null>(null)
const noLlegits = ref(0)
const xatObert = ref(false)
const fisioId = computed(() => authStore.user?.fisioterapeuta?.id)
onMounted(async () => {
  try {
    await npsStore.fetchDashboard()
    await carregarPacients()
    const nl = await api.get('/missatges/no-llegits')
    noLlegits.value = nl.data.count || 0
  } catch(e) {}
  finally { loading.value = false }
})
async function carregarPacients() {
  if (!fisioId.value) return
  loadingPacients.value = true
  try {
    const r = await api.get('/missatges/fisio/pacients')
    pacients.value = r.data
  } catch(e) {} finally { loadingPacients.value = false }
}
async function toggleXat() {
  xatObert.value = !xatObert.value
  if (xatObert.value && missatges.value.length === 0) await carregarChat()
}
async function carregarChat() {
  if (!fisioId.value) return
  loadingMsg.value = true
  try {
    const r = await api.get(`/missatges/${fisioId.value}`)
    missatges.value = r.data
    noLlegits.value = 0
    scrollChat()
  } finally { loadingMsg.value = false }
}
async function enviarMissatge() {
  if (!nouMissatge.value.trim() || enviant.value || !fisioId.value) return
  enviant.value = true
  try {
    const r = await api.post(`/missatges/${fisioId.value}`, { contingut: nouMissatge.value.trim() })
    missatges.value.push(r.data)
    nouMissatge.value = ''
    scrollChat()
  } finally { enviant.value = false }
}
function scrollChat() { setTimeout(() => { if (chatEl.value) chatEl.value.scrollTop = chatEl.value.scrollHeight }, 50) }
function formatHora(d: string) { if (!d) return ''; return new Date(d).toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' }) }
function formatData(d: string) { if (!d) return '—'; return new Date(d).toLocaleDateString('ca-ES', { day: '2-digit', month: 'short' }) }
function esFisio(m: any) { return m.emissor_rol === 'fisioterapeuta' }
</script>
<template>
  <div class="p-6 space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">El meu Dashboard</h1><p class="text-gray-500">Evolució dels resultats dels teus pacients</p></div>
    <div v-if="loading" class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div></div>
    <div v-else class="space-y-6">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"><p class="text-sm text-gray-500">NPS Global</p><p class="text-3xl font-bold mt-1" :class="(npsStore.dashboard?.nps_score??0)>=0?'text-yellow-500':'text-red-500'">{{ npsStore.dashboard?.nps_score??0 }}</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"><p class="text-sm text-gray-500">Pacients</p><p class="text-3xl font-bold mt-1 text-gray-800">{{ pacients.length }}</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"><p class="text-sm text-gray-500">Respostes</p><p class="text-3xl font-bold mt-1 text-gray-800">{{ npsStore.dashboard?.total_respostes??0 }}</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"><p class="text-sm text-gray-500">NPS Promotors</p><p class="text-3xl font-bold mt-1 text-green-600">{{ npsStore.dashboard?.promotors??0 }}</p></div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Indicadors Detallats</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div v-for="ind in [{label:'Qualitat',value:npsStore.dashboard?.mitja_qualitat},{label:'Puntualitat',value:npsStore.dashboard?.mitja_puntualitat},{label:'Tracte',value:npsStore.dashboard?.mitja_tracte},{label:'Millora Percebuda',value:npsStore.dashboard?.mitja_millora},{label:'Comunicació',value:npsStore.dashboard?.mitja_comunicacio},{label:'Experiència Global',value:npsStore.dashboard?.mitja_experiencia}]" :key="ind.label" class="p-3 bg-gray-50 rounded-lg"><p class="text-xs text-gray-500">{{ ind.label }}</p><p class="text-xl font-bold text-gray-800 mt-1">{{ ind.value!=null?Number(ind.value).toFixed(1):'—' }}<span class="text-sm text-gray-400">/5</span></p></div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-800">Els meus Pacients</h2>
          <span class="text-xs text-gray-400">{{ pacients.length }} pacients</span>
        </div>
        <div v-if="loadingPacients" class="flex justify-center py-8"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div></div>
        <div v-else-if="pacients.length===0" class="p-6 text-center text-gray-400 text-sm">Sense pacients assignats</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead><tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pacient</th>
              <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completades</th>
              <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pendents</th>
              <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estat</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="p in pacients" :key="p.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs flex-shrink-0">{{ p.nom_complet?.split(' ').map((n:string)=>n[0]).slice(0,2).join('') }}</div>
                    <div><p class="font-medium text-gray-900 text-sm">{{ p.nom_complet }}</p><p v-if="p.email" class="text-xs text-gray-400">{{ p.email }}</p></div>
                  </div>
                </td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-bold" :class="p.completades>0?'bg-green-100 text-green-700':'bg-gray-100 text-gray-400'">{{ p.completades }}</span></td>
                <td class="px-5 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-bold" :class="p.pendents>0?'bg-amber-100 text-amber-700':'bg-gray-100 text-gray-400'">{{ p.pendents }}</span></td>
                <td class="px-5 py-3 text-center text-sm text-gray-500">{{ p.total_enquestes }}</td>
                <td class="px-5 py-3 text-center">
                  <span v-if="p.total_enquestes===0" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">Sense enquestes</span>
                  <span v-else-if="p.pendents>0" class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700">⏳ Pendent</span>
                  <span v-else class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">✓ Tot completat</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <h2 class="text-lg font-semibold text-gray-800">Missatges amb Administració</h2>
          </div>
          <div class="flex items-center gap-3">
            <span v-if="noLlegits>0" class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ noLlegits }} nous</span>
            <button @click="toggleXat" class="text-sm text-primary-600 hover:text-primary-800 font-medium transition-colors">{{ xatObert ? 'Tancar ▲' : 'Obrir ▼' }}</button>
          </div>
        </div>
        <transition name="slide">
          <div v-if="xatObert" class="flex flex-col" style="height:380px">
            <div ref="chatEl" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50">
              <div v-if="loadingMsg" class="flex justify-center py-8"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div></div>
              <div v-else-if="missatges.length===0" class="flex items-center justify-center h-full"><p class="text-sm text-gray-400">Inicia la conversa amb l'administració</p></div>
              <template v-else>
                <div v-for="m in missatges" :key="m.id" class="flex" :class="esFisio(m)?'justify-end':'justify-start'">
                  <div class="max-w-[75%]">
                    <div class="rounded-2xl px-4 py-2.5 text-sm" :class="esFisio(m)?'bg-primary-600 text-white rounded-br-sm':'bg-white text-gray-800 shadow-sm border border-gray-100 rounded-bl-sm'">{{ m.contingut }}</div>
                    <p class="text-xs text-gray-400 mt-1" :class="esFisio(m)?'text-right':'text-left'">{{ formatHora(m.created_at) }}<span v-if="!esFisio(m)" class="ml-1 font-medium text-gray-500">Admin</span></p>
                  </div>
                </div>
              </template>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 bg-white">
              <div class="flex gap-2">
                <input v-model="nouMissatge" @keydown.enter.prevent="enviarMissatge" type="text" placeholder="Escriu un missatge a l'administració..." class="flex-1 text-sm border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all" :disabled="enviant"/>
                <button @click="enviarMissatge" :disabled="!nouMissatge.trim()||enviant" class="bg-primary-600 hover:bg-primary-700 disabled:opacity-40 text-white px-4 py-2.5 rounded-xl transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>
<style scoped>
.slide-enter-active,.slide-leave-active{transition:all 0.2s ease}
.slide-enter-from,.slide-leave-to{opacity:0;transform:translateY(-6px)}
</style>
