<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
const authStore = useAuthStore()
const loading = ref(true)
const loadingMissatges = ref(false)
const participacions = ref<any[]>([])
const error = ref('')
const missatges = ref<any[]>([])
const nouMissatge = ref('')
const enviantMissatge = ref(false)
const chatEl = ref<HTMLElement | null>(null)
const noLlegits = ref(0)
const xatObert = ref(false)
const pacientId = computed(() => authStore.user?.pacient?.id)
const fisioId = computed(() => participacions.value.find(p => p.fisioterapeuta_id)?.fisioterapeuta_id ?? null)
const fisioNom = computed(() => { const p = participacions.value.find(p => p.fisioterapeuta); return p?.fisioterapeuta ? `${p.fisioterapeuta.nom} ${p.fisioterapeuta.cognoms}` : 'El meu fisioterapeuta' })
const pendents = computed(() => participacions.value.filter(p => p.estat !== 'completada'))
const completades = computed(() => participacions.value.filter(p => p.estat === 'completada'))
onMounted(async () => {
  try {
    if (pacientId.value) {
      const r = await api.get(`/pacients/${pacientId.value}/historial`)
      participacions.value = r.data.data || r.data || []
      const nl = await api.get('/missatges/no-llegits')
      noLlegits.value = nl.data.count || 0
    }
  } catch(e) { error.value = "No s'han pogut carregar les dades." }
  finally { loading.value = false }
})
async function toggleXat() {
  xatObert.value = !xatObert.value
  if (xatObert.value && fisioId.value && missatges.value.length === 0) await carregarMissatges()
}
async function carregarMissatges() {
  if (!fisioId.value) return
  loadingMissatges.value = true
  try {
    const r = await api.get(`/missatges/${pacientId.value}/${fisioId.value}`)
    missatges.value = r.data
    noLlegits.value = 0
    scrollChat()
  } finally { loadingMissatges.value = false }
}
async function enviarMissatge() {
  if (!nouMissatge.value.trim() || enviantMissatge.value || !fisioId.value) return
  enviantMissatge.value = true
  try {
    const r = await api.post(`/missatges/${pacientId.value}/${fisioId.value}`,{ contingut: nouMissatge.value.trim() })
    missatges.value.push(r.data)
    nouMissatge.value = ''
    scrollChat()
  } finally { enviantMissatge.value = false }
}
function scrollChat() { setTimeout(() => { if (chatEl.value) chatEl.value.scrollTop = chatEl.value.scrollHeight },50) }
function formatDate(d: string) { if (!d) return '—'; return new Date(d).toLocaleDateString('ca-ES') }
function formatHora(d: string) { if (!d) return ''; return new Date(d).toLocaleTimeString('ca-ES',{hour:'2-digit',minute:'2-digit'}) }
function esPacient(m: any) { return m.emissor_rol === 'pacient' }
</script>
<template>
  <div class="p-6 space-y-6">
    <div class="flex items-start justify-between">
      <div><h1 class="text-2xl font-bold text-gray-900">El meu Portal</h1><p class="text-gray-500">Benvingut/da, {{ authStore.user?.name }}</p></div>
      <button v-if="fisioId" @click="toggleXat" class="relative flex items-center gap-2 bg-white border border-gray-200 hover:border-primary-400 hover:bg-primary-50 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-xl shadow-sm transition-all">
        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        Xat amb fisio
        <span v-if="noLlegits>0" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">{{ noLlegits }}</span>
      </button>
    </div>
    <div v-if="loading" class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div></div>
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">{{ error }}</div>
    <div v-else class="space-y-6">
      <transition name="slide-down">
        <div v-if="xatObert&&fisioId" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col" style="height:420px">
          <div class="px-5 py-3.5 border-b border-gray-100 bg-primary-50 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3"><div class="w-8 h-8 rounded-full bg-primary-200 text-primary-800 flex items-center justify-center text-xs font-bold">{{ fisioNom.split(' ').map((n:string)=>n[0]).slice(0,2).join('') }}</div><div><p class="font-semibold text-gray-900 text-sm">{{ fisioNom }}</p><p class="text-xs text-primary-600">Fisioterapeuta</p></div></div>
            <button @click="xatObert=false" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
          </div>
          <div ref="chatEl" class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
            <div v-if="loadingMissatges" class="flex justify-center py-8"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div></div>
            <div v-else-if="missatges.length===0" class="flex items-center justify-center h-full"><p class="text-sm text-gray-400">Inicia la conversa amb el teu fisioterapeuta</p></div>
            <template v-else>
              <div v-for="m in missatges" :key="m.id" class="flex" :class="esPacient(m)?'justify-end':'justify-start'">
                <div class="max-w-[75%]"><div class="rounded-2xl px-4 py-2.5 text-sm leading-relaxed" :class="esPacient(m)?'bg-primary-600 text-white rounded-br-sm':'bg-gray-100 text-gray-800 rounded-bl-sm'">{{ m.contingut }}</div><p class="text-xs text-gray-400 mt-1" :class="esPacient(m)?'text-right':'text-left'">{{ formatHora(m.created_at) }}</p></div>
              </div>
            </template>
          </div>
          <div class="px-4 py-3 border-t border-gray-100 flex-shrink-0">
            <div class="flex gap-2"><input v-model="nouMissatge" @keydown.enter.prevent="enviarMissatge" type="text" placeholder="Escriu un missatge..." class="flex-1 text-sm border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all" :disabled="enviantMissatge"/><button @click="enviarMissatge" :disabled="!nouMissatge.trim()||enviantMissatge" class="bg-primary-600 hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-4 py-2.5 rounded-xl transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></button></div>
          </div>
        </div>
      </transition>
      <div v-if="pendents.length>0">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Enquestes pendents</h2>
        <div class="space-y-3">
          <div v-for="p in pendents" :key="p.id" class="bg-white rounded-xl border-2 border-primary-400 shadow-sm p-5 flex items-center justify-between">
            <div><p class="font-semibold text-gray-900">{{ p.enquesta?.titol||'Enquesta' }}</p><p class="text-sm text-gray-400 mt-0.5">Rebuda el {{ formatDate(p.created_at) }}</p></div>
            <a :href="`/enquesta/${p.enquesta?.slug}?token=${p.token}`" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">Respondre →</a>
          </div>
        </div>
      </div>
      <div v-else class="bg-green-50 border border-green-200 rounded-xl p-5 text-center"><p class="text-green-700 font-medium">✓ No tens cap enquesta pendent</p></div>
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"><p class="text-sm text-gray-500">Completades</p><p class="text-3xl font-bold mt-1 text-green-600">{{ completades.length }}</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"><p class="text-sm text-gray-500">Total rebudes</p><p class="text-3xl font-bold mt-1 text-gray-800">{{ participacions.length }}</p></div>
      </div>
      <div v-if="completades.length>0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Historial</h2>
        <div class="divide-y divide-gray-100">
          <div v-for="p in completades" :key="p.id" class="py-3 flex items-center justify-between">
            <div><p class="font-medium text-gray-800">{{ p.enquesta?.titol||'Enquesta' }}</p><p class="text-sm text-gray-400">{{ formatDate(p.created_at) }}</p></div>
            <span class="text-xs px-2 py-1 rounded-full font-medium bg-green-100 text-green-700">Completada</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.slide-down-enter-active,.slide-down-leave-active{transition:all 0.25s ease}
.slide-down-enter-from,.slide-down-leave-to{opacity:0;transform:translateY(-8px)}
</style>
