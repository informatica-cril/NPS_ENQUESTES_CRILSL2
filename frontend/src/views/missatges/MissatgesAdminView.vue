<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
const authStore = useAuthStore()
const loading = ref(true)
const loadingMsg = ref(false)
const fisios = ref<any[]>([])
const fisioSeleccionat = ref<any>(null)
const missatges = ref<any[]>([])
const nouMissatge = ref('')
const enviant = ref(false)
const chatEl = ref<HTMLElement | null>(null)
onMounted(async () => {
  try {
    const r = await api.get('/missatges/admin/fisios')
    fisios.value = r.data
  } finally { loading.value = false }
})
async function obrirChat(fisio: any) {
  fisioSeleccionat.value = fisio
  missatges.value = []
  loadingMsg.value = true
  try {
    const r = await api.get(`/missatges/${fisio.id}`)
    missatges.value = r.data
    const f = fisios.value.find(f => f.id === fisio.id)
    if (f) f.no_llegits = 0
    scrollChat()
  } finally { loadingMsg.value = false }
}
async function enviarMissatge() {
  if (!nouMissatge.value.trim() || enviant.value) return
  enviant.value = true
  try {
    const r = await api.post(`/missatges/${fisioSeleccionat.value.id}`, { contingut: nouMissatge.value.trim() })
    missatges.value.push(r.data)
    const f = fisios.value.find(f => f.id === fisioSeleccionat.value.id)
    if (f) f.ultim_missatge = { contingut: r.data.contingut, created_at: r.data.created_at, emissor_rol: 'admin' }
    nouMissatge.value = ''
    scrollChat()
  } finally { enviant.value = false }
}
function scrollChat() { setTimeout(() => { if (chatEl.value) chatEl.value.scrollTop = chatEl.value.scrollHeight }, 50) }
function formatDate(d: string) { if (!d) return '—'; return new Date(d).toLocaleDateString('ca-ES', { day: '2-digit', month: 'short' }) }
function formatHora(d: string) { if (!d) return ''; return new Date(d).toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' }) }
function esAdmin(m: any) { return m.emissor_rol === 'admin' }
const totalNoLlegits = () => fisios.value.reduce((s, f) => s + (f.no_llegits || 0), 0)
</script>
<template>
  <div>
    <h1 class="page-title mb-6">Missatges amb Fisioterapeutes</h1>
    <div v-if="loading" class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div></div>
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6" style="height:calc(100vh - 180px)">
      <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex-shrink-0">
          <h2 class="font-semibold text-gray-800">Fisioterapeutes</h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ fisios.length }} fisioterapeutes</p>
        </div>
        <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
          <div v-if="fisios.length===0" class="p-6 text-center text-gray-400 text-sm">No hi ha fisioterapeutes</div>
          <div v-for="f in fisios" :key="f.id" @click="obrirChat(f)" class="flex items-center gap-3 px-5 py-4 cursor-pointer hover:bg-gray-50 transition-colors" :class="fisioSeleccionat?.id===f.id?'bg-primary-50 border-l-4 border-primary-500':''">
            <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm flex-shrink-0">{{ f.nom_complet?.split(' ').map((n:string)=>n[0]).slice(0,2).join('') }}</div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between"><p class="font-medium text-gray-900 text-sm truncate">{{ f.nom_complet }}</p><span v-if="f.ultim_missatge" class="text-xs text-gray-400 flex-shrink-0">{{ formatDate(f.ultim_missatge.created_at) }}</span></div>
              <div class="flex items-center justify-between mt-0.5">
                <p class="text-xs text-gray-400 truncate"><template v-if="f.ultim_missatge"><span v-if="f.ultim_missatge.emissor_rol==='admin'" class="text-primary-500">Tu: </span>{{ f.ultim_missatge.contingut }}</template><template v-else>Sense missatges</template></p>
                <span v-if="f.no_llegits>0" class="ml-2 bg-primary-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0">{{ f.no_llegits }}</span>
              </div>
              <p v-if="f.especialitat" class="text-xs text-gray-300 mt-0.5">{{ f.especialitat }}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3 flex-shrink-0">
          <template v-if="fisioSeleccionat">
            <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">{{ fisioSeleccionat.nom_complet?.split(' ').map((n:string)=>n[0]).slice(0,2).join('') }}</div>
            <div><p class="font-semibold text-gray-900 text-sm">{{ fisioSeleccionat.nom_complet }}</p><p class="text-xs text-gray-400">{{ fisioSeleccionat.especialitat || 'Fisioterapeuta' }}</p></div>
          </template>
          <template v-else><p class="text-gray-400 text-sm">Selecciona un fisioterapeuta per veure la conversa</p></template>
        </div>
        <div ref="chatEl" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50">
          <div v-if="!fisioSeleccionat" class="h-full flex items-center justify-center">
            <div class="text-center text-gray-300"><svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg><p>Selecciona un fisioterapeuta</p></div>
          </div>
          <div v-else-if="loadingMsg" class="flex justify-center py-8"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div></div>
          <div v-else-if="missatges.length===0" class="flex items-center justify-center h-full"><p class="text-sm text-gray-400">Sense missatges. Inicia la conversa!</p></div>
          <template v-else>
            <div v-for="m in missatges" :key="m.id" class="flex" :class="esAdmin(m)?'justify-end':'justify-start'">
              <div class="max-w-[75%]"><div class="rounded-2xl px-4 py-2.5 text-sm" :class="esAdmin(m)?'bg-primary-600 text-white rounded-br-sm':'bg-white text-gray-800 shadow-sm border border-gray-100 rounded-bl-sm'">{{ m.contingut }}</div><p class="text-xs text-gray-400 mt-1" :class="esAdmin(m)?'text-right':'text-left'">{{ formatHora(m.created_at) }}<span v-if="esAdmin(m)&&m.llegit" class="ml-1 text-primary-400">✓✓</span></p></div>
            </div>
          </template>
        </div>
        <div v-if="fisioSeleccionat" class="px-4 py-3 border-t border-gray-100 bg-white flex-shrink-0">
          <div class="flex gap-2"><input v-model="nouMissatge" @keydown.enter.prevent="enviarMissatge" type="text" placeholder="Escriu un missatge..." class="flex-1 text-sm border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all" :disabled="enviant"/><button @click="enviarMissatge" :disabled="!nouMissatge.trim()||enviant" class="bg-primary-600 hover:bg-primary-700 disabled:opacity-40 text-white px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 text-sm font-medium"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>Enviar</button></div>
        </div>
      </div>
    </div>
  </div>
</template>
