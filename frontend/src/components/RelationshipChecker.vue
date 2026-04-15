<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- Panel -->
      <div class="relative w-full sm:max-w-md bg-white dark:bg-slate-900 sm:rounded-3xl rounded-t-3xl shadow-2xl shadow-black/30 ring-1 ring-gray-200 dark:ring-slate-700 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-100 dark:border-slate-700/60">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
              </svg>
            </div>
            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $t('relationship.title') }}</h2>
          </div>
          <button @click="$emit('close')"
            class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 flex items-center justify-center text-gray-500 dark:text-slate-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4">

          <!-- Two person selectors -->
          <div class="grid grid-cols-2 gap-3">

            <!-- Person A -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1.5">
                {{ $t('relationship.personA') }}
              </label>
              <div class="relative" ref="dropdownARef">
                <button
                  type="button"
                  @click="toggleDropdown('a')"
                  class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl border text-sm text-start transition-colors"
                  :class="personA
                    ? 'border-violet-300 dark:border-violet-600 bg-violet-50 dark:bg-violet-900/20 text-violet-800 dark:text-violet-200 font-semibold'
                    : 'border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-400 dark:text-slate-500'"
                >
                  <span v-if="personA" class="text-base leading-none shrink-0">{{ personA.gender === 'male' ? '👨' : '👩' }}</span>
                  <span class="truncate">{{ personA ? personA.name.split(' ')[0] : $t('relationship.selectA') }}</span>
                </button>
                <!-- Dropdown A -->
                <div v-if="activeDropdown === 'a'"
                  class="absolute top-full mt-1 start-0 end-0 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-xl z-10 overflow-hidden"
                >
                  <div class="p-2 border-b border-gray-100 dark:border-slate-700">
                    <input
                      v-model="searchA"
                      ref="searchAInput"
                      type="text"
                      :placeholder="$t('relationship.selectA')"
                      class="w-full text-xs px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 focus:outline-none focus:border-violet-400"
                    />
                  </div>
                  <div class="max-h-44 overflow-y-auto">
                    <button
                      v-for="p in filteredA" :key="p.id"
                      type="button"
                      @mousedown.prevent="selectPersonA(p)"
                      class="w-full flex items-center gap-2 px-3 py-2 text-sm text-start hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors"
                      :class="personA?.id === p.id ? 'bg-violet-50 dark:bg-violet-900/20 font-semibold text-violet-700 dark:text-violet-300' : 'text-gray-700 dark:text-slate-300'"
                    >
                      <span class="text-base leading-none shrink-0">{{ p.gender === 'male' ? '👨' : '👩' }}</span>
                      <span class="truncate">{{ p.name }}</span>
                    </button>
                    <div v-if="!filteredA.length" class="px-3 py-3 text-xs text-center text-gray-400 dark:text-slate-500">
                      {{ $t('person.noResults') }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Person B -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1.5">
                {{ $t('relationship.personB') }}
              </label>
              <div class="relative" ref="dropdownBRef">
                <button
                  type="button"
                  @click="toggleDropdown('b')"
                  class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl border text-sm text-start transition-colors"
                  :class="personB
                    ? 'border-indigo-300 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-800 dark:text-indigo-200 font-semibold'
                    : 'border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-400 dark:text-slate-500'"
                >
                  <span v-if="personB" class="text-base leading-none shrink-0">{{ personB.gender === 'male' ? '👨' : '👩' }}</span>
                  <span class="truncate">{{ personB ? personB.name.split(' ')[0] : $t('relationship.selectB') }}</span>
                </button>
                <!-- Dropdown B -->
                <div v-if="activeDropdown === 'b'"
                  class="absolute top-full mt-1 start-0 end-0 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-xl z-10 overflow-hidden"
                >
                  <div class="p-2 border-b border-gray-100 dark:border-slate-700">
                    <input
                      v-model="searchB"
                      ref="searchBInput"
                      type="text"
                      :placeholder="$t('relationship.selectB')"
                      class="w-full text-xs px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 focus:outline-none focus:border-indigo-400"
                    />
                  </div>
                  <div class="max-h-44 overflow-y-auto">
                    <button
                      v-for="p in filteredB" :key="p.id"
                      type="button"
                      @mousedown.prevent="selectPersonB(p)"
                      class="w-full flex items-center gap-2 px-3 py-2 text-sm text-start hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                      :class="personB?.id === p.id ? 'bg-indigo-50 dark:bg-indigo-900/20 font-semibold text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-slate-300'"
                    >
                      <span class="text-base leading-none shrink-0">{{ p.gender === 'male' ? '👨' : '👩' }}</span>
                      <span class="truncate">{{ p.name }}</span>
                    </button>
                    <div v-if="!filteredB.length" class="px-3 py-3 text-xs text-center text-gray-400 dark:text-slate-500">
                      {{ $t('person.noResults') }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Check button -->
          <button
            @click="checkRelationship"
            :disabled="!personA || !personB || checking"
            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 disabled:from-gray-300 disabled:to-gray-300 dark:disabled:from-slate-700 dark:disabled:to-slate-700 text-white text-sm font-semibold shadow-md shadow-violet-200/60 dark:shadow-violet-900/30 transition-all active:scale-[.98]"
          >
            <svg v-if="checking" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            {{ checking ? $t('relationship.checking') : $t('relationship.check') }}
          </button>

          <!-- ── Result card ──────────────────────────────────────────────── -->
          <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div v-if="result !== null" class="rounded-2xl overflow-hidden">

              <!-- Same person warning -->
              <div v-if="result.type === 'same_person'"
                class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/40 rounded-2xl text-center"
              >
                <span class="text-2xl">⚠️</span>
                <p class="mt-1 text-sm font-semibold text-yellow-700 dark:text-yellow-300">{{ $t('relationship.samePerson') }}</p>
              </div>

              <!-- No relation -->
              <div v-else-if="!result.found"
                class="p-4 bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700 rounded-2xl text-center"
              >
                <span class="text-2xl">🤷</span>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ $t('relationship.noRelation') }}</p>
              </div>

              <!-- Found relationship -->
              <div v-else
                class="bg-gradient-to-br from-violet-50 to-indigo-50 dark:from-violet-900/30 dark:to-indigo-900/20 border border-violet-200 dark:border-violet-700/40 rounded-2xl p-5"
              >
                <div class="text-center mb-4">
                  <span class="text-3xl">{{ typeEmoji }}</span>
                  <div class="mt-1 text-xs font-semibold uppercase tracking-wider text-violet-400 dark:text-violet-500">
                    {{ $t('relationship.result') }}
                  </div>
                </div>

                <!-- A → B -->
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800/60 rounded-xl mb-2 shadow-sm">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-lg shrink-0"
                    :class="result.person_a.gender === 'male' ? 'bg-blue-100 dark:bg-blue-900/50' : 'bg-pink-100 dark:bg-pink-900/50'"
                  >
                    {{ result.person_a.gender === 'male' ? '👨' : '👩' }}
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ result.person_a.name }}</div>
                    <div class="text-xs text-gray-400 dark:text-slate-500">{{ $t('relationship.isThe') }}</div>
                  </div>
                  <div class="shrink-0 text-end">
                    <span class="inline-block px-2.5 py-1 rounded-lg bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300 text-sm font-bold">
                      {{ result.a_to_b }}
                    </span>
                  </div>
                </div>

                <!-- Connector arrow -->
                <div class="text-center text-gray-300 dark:text-slate-600 text-xs my-1">↕</div>

                <!-- B → A -->
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800/60 rounded-xl shadow-sm">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-lg shrink-0"
                    :class="result.person_b.gender === 'male' ? 'bg-blue-100 dark:bg-blue-900/50' : 'bg-pink-100 dark:bg-pink-900/50'"
                  >
                    {{ result.person_b.gender === 'male' ? '👨' : '👩' }}
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ result.person_b.name }}</div>
                    <div class="text-xs text-gray-400 dark:text-slate-500">{{ $t('relationship.isThe') }}</div>
                  </div>
                  <div class="shrink-0 text-end">
                    <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm font-bold">
                      {{ result.b_to_a }}
                    </span>
                  </div>
                </div>

              </div>
            </div>
          </Transition>

        </div><!-- end body -->
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { usePeopleStore } from '@/stores/people'

const props  = defineProps({
  people: { type: Array, default: () => [] },
})
defineEmits(['close'])

const store = usePeopleStore()

// ── State ──────────────────────────────────────────────────────────────────
const personA       = ref(null)
const personB       = ref(null)
const searchA       = ref('')
const searchB       = ref('')
const activeDropdown = ref(null)   // 'a' | 'b' | null
const checking      = ref(false)
const result        = ref(null)

const dropdownARef  = ref(null)
const dropdownBRef  = ref(null)
const searchAInput  = ref(null)
const searchBInput  = ref(null)

// ── Filtered lists ─────────────────────────────────────────────────────────
const filteredA = computed(() => {
  const q = searchA.value.trim().toLowerCase()
  return props.people
    .filter(p => p.id !== personB.value?.id && (!q || p.name.toLowerCase().includes(q)))
    .slice(0, 20)
})

const filteredB = computed(() => {
  const q = searchB.value.trim().toLowerCase()
  return props.people
    .filter(p => p.id !== personA.value?.id && (!q || p.name.toLowerCase().includes(q)))
    .slice(0, 20)
})

// ── Emoji by relationship type ─────────────────────────────────────────────
const typeEmoji = computed(() => {
  const map = {
    spouse:       '💍',
    parent_child: '👨‍👧',
    siblings:     '👫',
    grandparent:  '👴',
    uncle_aunt:   '🤝',
    cousins:      '👥',
  }
  return map[result.value?.type] ?? '🔗'
})

// ── Methods ────────────────────────────────────────────────────────────────
function toggleDropdown(which) {
  if (activeDropdown.value === which) {
    activeDropdown.value = null
    return
  }
  activeDropdown.value = which
  nextTick(() => {
    if (which === 'a') searchAInput.value?.focus()
    else searchBInput.value?.focus()
  })
}

function selectPersonA(p) {
  personA.value        = p
  searchA.value        = ''
  activeDropdown.value = null
  result.value         = null
}

function selectPersonB(p) {
  personB.value        = p
  searchB.value        = ''
  activeDropdown.value = null
  result.value         = null
}

// Auto-check when both selected
watch([personA, personB], ([a, b]) => {
  if (a && b) checkRelationship()
})

async function checkRelationship() {
  if (!personA.value || !personB.value) return
  checking.value = true
  result.value   = null
  try {
    result.value = await store.checkRelationship(personA.value.id, personB.value.id)
  } catch (e) {
    result.value = { found: false, type: 'unknown', a_to_b: null, b_to_a: null }
  } finally {
    checking.value = false
  }
}

// Click-outside to close dropdowns
function handleClickOutside(e) {
  if (activeDropdown.value === 'a' && dropdownARef.value && !dropdownARef.value.contains(e.target)) {
    activeDropdown.value = null
  }
  if (activeDropdown.value === 'b' && dropdownBRef.value && !dropdownBRef.value.contains(e.target)) {
    activeDropdown.value = null
  }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside))
</script>
