// User types
export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'fisioterapeuta' | 'pacient' | 'viewer'
  phone?: string
  avatar?: string
  is_active: boolean
  email_verified_at?: string
  created_at: string
  updated_at: string
  fisioterapeuta?: Fisioterapeuta
  pacient?: Pacient
}

// Centre types
export interface Centre {
  id: number
  name: string
  code: string
  address?: string
  city?: string
  postal_code?: string
  province?: string
  latitude?: number
  longitude?: number
  phone?: string
  email?: string
  is_active: boolean
  coordinates?: { lat: number; lng: number }
  created_at: string
  updated_at: string
}

// Fisioterapeuta types
export interface Fisioterapeuta {
  id: number
  user_id?: number
  centre_id?: number
  nom: string
  cognoms: string
  num_colegiat?: string
  especialitat?: string
  data_alta?: string
  actiu: boolean
  nom_complet?: string
  user?: User
  centre?: Centre
  created_at: string
  updated_at: string
}

// Pacient types
export interface Pacient {
  id: number
  user_id?: number
  centre_id?: number
  nom: string
  cognoms: string
  dni?: string
  cip?: string
  data_naixement?: string
  sexe?: 'home' | 'dona' | 'altre'
  telefon?: string
  email?: string
  adreca?: string
  poblacio?: string
  codi_postal?: string
  data_alta: string
  data_baixa?: string
  actiu: boolean
  consentiment_rgpd: boolean
  data_consentiment?: string
  nom_complet?: string
  edat?: number
  centre?: Centre
  created_at: string
  updated_at: string
}

// Enquesta (Survey) types
export type EnquestaTipus = 'nps' | 'satisfaccio' | 'qualitat' | 'general'
export type EnquestaEstat = 'esborrany' | 'activa' | 'tancada' | 'arxivada'

export interface Enquesta {
  id: number
  titol: string
  slug: string
  descripcio?: string
  tipus: EnquestaTipus
  estat: EnquestaEstat
  centre_id?: number
  created_by?: number
  data_inici?: string
  data_fi?: string
  anonima: boolean
  requereix_autenticacio: boolean
  temps_estimat_minuts?: number
  imatge_capcalera?: string
  configuracio?: Record<string, any>
  total_participacions?: number
  centre?: Centre
  creador?: User
  preguntes?: Pregunta[]
  created_at: string
  updated_at: string
}

// Pregunta (Question) types
export type PreguntaTipus = 
  | 'nps'
  | 'escala'
  | 'text_curt'
  | 'text_llarg'
  | 'opcio_unica'
  | 'opcio_multiple'
  | 'si_no'
  | 'data'
  | 'valoracio_estrelles'

export interface Pregunta {
  id: number
  enquesta_id: number
  text_pregunta: string
  descripcio?: string
  tipus: PreguntaTipus
  ordre: number
  obligatoria: boolean
  opcions?: string[]
  configuracio?: Record<string, any>
  activa: boolean
  created_at: string
  updated_at: string
}

// Participacio types
export type ParticipacioEstat = 'pendent' | 'en_curs' | 'completada' | 'expirada'

export interface Participacio {
  id: number
  enquesta_id: number
  pacient_id?: number
  fisioterapeuta_id?: number
  token: string
  estat: ParticipacioEstat
  data_inici?: string
  data_completat?: string
  ip_address?: string
  user_agent?: string
  enquesta?: Enquesta
  pacient?: Pacient
  fisioterapeuta?: Fisioterapeuta
  respostes?: Resposta[]
  nps_resultat?: NpsResultat
  created_at: string
  updated_at: string
}

// Resposta types
export interface Resposta {
  id: number
  participacio_id: number
  pregunta_id: number
  valor_text?: string
  valor_numeric?: number
  valor_json?: any
  pregunta?: Pregunta
  created_at: string
  updated_at: string
}

// NPS types
export type NpsCategoria = 'promotor' | 'passiu' | 'detractor'

export interface NpsResultat {
  id: number
  enquesta_id: number
  participacio_id: number
  centre_id?: number
  fisioterapeuta_id?: number
  puntuacio: number
  categoria: NpsCategoria
  comentari?: string
  data: string
  enquesta?: Enquesta
  centre?: Centre
  fisioterapeuta?: Fisioterapeuta
  created_at: string
  updated_at: string
}

export interface NpsEstadistiques {
  total_respostes: number
  promotors: number
  passius: number
  detractors: number
  percent_promotors?: number
  percent_passius?: number
  percent_detractors?: number
  nps_score: number | null
  mitjana_puntuacio: number | null
  distribucio?: Record<number, number>
}

export interface NpsEvolucio {
  periode: string
  total: number
  promotors: number
  passius: number
  detractors: number
  nps_score: number | null
  mitjana: number
}

// Informe types
export type InformeTipus = 'nps' | 'fisioterapeuta' | 'centre' | 'enquesta' | 'general'
export type InformeEstat = 'pendent' | 'processant' | 'completat' | 'error'

export interface Informe {
  id: number
  titol: string
  descripcio?: string
  tipus: InformeTipus
  enquesta_id?: number
  centre_id?: number
  fisioterapeuta_id?: number
  created_by: number
  data_inici: string
  data_fi: string
  filtres?: Record<string, any>
  dades?: Record<string, any>
  fitxer_path?: string
  estat: InformeEstat
  enquesta?: Enquesta
  centre?: Centre
  fisioterapeuta?: Fisioterapeuta
  creador?: User
  created_at: string
  updated_at: string
}

// API Response types
export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}
