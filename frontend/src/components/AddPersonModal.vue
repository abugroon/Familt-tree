<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- Modal -->
      <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-2xl shadow-gray-200/80 dark:shadow-black/60 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-slate-700/60">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ editMode ? $t('actions.editPersonTitle') : $t('actions.addPersonTitle') }}</h2>
            <p class="text-sm text-gray-400 dark:text-slate-500">{{ $t('actions.fillDetails') }}</p>
          </div>
          <button @click="$emit('close')" class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
          <!-- Photo upload -->
          <div class="flex flex-col items-center gap-3">
            <div
              class="w-20 h-20 rounded-full overflow-hidden border-2 border-dashed border-gray-300 dark:border-slate-600 hover:border-violet-400 flex items-center justify-center cursor-pointer relative group transition-colors"
              :class="{ 'border-solid border-violet-400': photoPreview }"
              @click="triggerFileInput"
            >
              <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
              <div v-else class="text-center text-gray-400 dark:text-slate-500 group-hover:text-violet-400">
                <svg class="w-7 h-7 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="text-xs mt-1">{{ $t('person.photo') }}</p>
              </div>
              <input ref="fileInputRef" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
            </div>
            <button v-if="photoPreview" type="button" @click="clearPhoto" class="text-xs text-gray-400 dark:text-slate-500 hover:text-red-500">{{ $t('person.removePhoto') }}</button>
          </div>

          <!-- Name -->
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">{{ $t('person.name') }} *</label>
            <input
              v-model="form.name"
              type="text"
              :placeholder="$t('person.namePlaceholder')"
              required
              class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 text-sm"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">وصف او ملاحظات</label>
            <textarea
              v-model="form.description"
              type="text"
              placeholder="اكتب اي وصف او ملاحظات تريد ان تظهر مع اسم الشخص المضاف"
              required
              class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 text-sm"
            />
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">{{ $t('person.gender') }} *</label>
            <div class="flex gap-3">
              <label
                v-for="g in ['male', 'female']" :key="g"
                class="flex-1 flex items-center gap-2.5 px-4 py-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.gender === g
                  ? g === 'male'
                    ? 'bg-blue-50 dark:bg-blue-900/40 border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300'
                    : 'bg-pink-50 dark:bg-pink-900/40 border-pink-300 dark:border-pink-600 text-pink-700 dark:text-pink-300'
                  : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 text-gray-500 dark:text-slate-400 hover:border-gray-300 dark:hover:border-slate-600'"
              >
                <input type="radio" v-model="form.gender" :value="g" class="hidden" />
                <span class="text-lg">{{ g === 'male' ? '👨' : '👩' }}</span>
                <span class="text-sm font-medium">{{ g === 'male' ? $t('person.male') : $t('person.female') }}</span>
              </label>
            </div>
          </div>

          <!-- Birth / Death dates -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">{{ $t('person.birthDate') }}</label>
              <input
                v-model="form.birth_date"
                type="date"
                class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 text-sm"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">{{ $t('person.deathDate') }}</label>
              <input
                v-model="form.death_date"
                type="date"
                class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 text-sm"
              />
            </div>
          </div>

          <!-- Parent — searchable dropdown -->
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">{{ $t('person.parent') }}</label>

            <!-- Trigger input -->
            <div class="relative" ref="parentInputRef">
              <input
                ref="parentInputElRef"
                type="text"
                v-model="parentSearch"
                @focus="openParentDropdown"
                @keydown.escape="parentOpen = false"
                @keydown.down.prevent="highlightNext"
                @keydown.up.prevent="highlightPrev"
                @keydown.enter.prevent="selectHighlighted"
                :placeholder="selectedParentLabel || $t('person.noParent')"
                :class="[
                  'w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border text-gray-800 dark:text-slate-200 focus:outline-none focus:ring-2 text-sm',
                  form.parent_id
                    ? 'border-violet-400 focus:border-violet-400 focus:ring-violet-100 dark:focus:ring-violet-900/30'
                    : 'border-gray-200 dark:border-slate-700 focus:border-violet-400 focus:ring-violet-100 dark:focus:ring-violet-900/30'
                ]"
                style="padding-inline-end: 2.5rem"
              />
              <!-- Icons -->
              <div class="absolute inset-y-0 flex items-center gap-1" style="inset-inline-end: 0.5rem">
                <button v-if="form.parent_id" type="button" @mousedown.prevent="clearParent"
                  class="w-5 h-5 rounded-full bg-gray-200 dark:bg-slate-600 hover:bg-red-100 dark:hover:bg-red-900/40 flex items-center justify-center text-gray-500 dark:text-slate-400 hover:text-red-500">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
                <svg class="w-4 h-4 text-gray-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
            </div>

            <!-- Selected person badge -->
            <div v-if="form.parent_id && !parentOpen" class="mt-1.5 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800/40">
              <span class="text-sm">{{ selectedParentObj?.gender === 'male' ? '👨' : '👩' }}</span>
              <span class="text-sm font-medium text-violet-700 dark:text-violet-300">{{ selectedParentObj?.name }}</span>
            </div>
          </div>

          <!-- Dropdown rendered via Teleport to escape overflow clipping -->
          <Teleport to="body">
            <div
              v-if="parentOpen"
              :style="dropdownStyle"
              data-parent-dropdown
              class="fixed z-[200] max-h-56 overflow-y-auto rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-2xl shadow-gray-300/60 dark:shadow-black/60"
            >
              <!-- No parent option -->
              <button
                type="button"
                @mousedown.prevent="selectParent(null)"
                :class="[
                  'w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-start hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors border-b border-gray-100 dark:border-slate-700/60',
                  !form.parent_id ? 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 font-medium' : 'text-gray-500 dark:text-slate-400'
                ]"
              >
                <span class="w-6 h-6 rounded-full bg-gray-100 dark:bg-slate-700 flex items-center justify-center text-gray-400 text-xs flex-shrink-0">—</span>
                {{ $t('person.noParent') }}
              </button>

              <!-- Filtered people -->
              <div v-if="filteredParents.length === 0" class="px-4 py-3 text-sm text-gray-400 dark:text-slate-500 text-center">
                {{ $t('person.noResults') }}
              </div>
              <button
                v-for="(p, i) in filteredParents"
                :key="p.id"
                type="button"
                @mousedown.prevent="selectParent(p)"
                :class="[
                  'w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-start transition-colors',
                  highlightIndex === i ? 'bg-violet-50 dark:bg-violet-900/20' : 'hover:bg-gray-50 dark:hover:bg-slate-700',
                  form.parent_id === p.id ? 'text-violet-700 dark:text-violet-300 font-semibold' : 'text-gray-700 dark:text-slate-200'
                ]"
              >
                <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center text-xs font-bold"
                  :class="p.gender === 'male' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300' : 'bg-pink-100 dark:bg-pink-900/50 text-pink-600 dark:text-pink-300'"
                >
                  <img v-if="p.photo_url" :src="p.photo_url" class="w-full h-full object-cover" />
                  <span v-else>{{ p.name.charAt(0) }}</span>
                </div>
                <span class="flex-1 truncate">{{ p.name }}</span>
                <span class="text-base leading-none">{{ p.gender === 'male' ? '👨' : '👩' }}</span>
                <svg v-if="form.parent_id === p.id" class="w-4 h-4 text-violet-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </button>
            </div>
          </Teleport>

          <!-- Error -->
          <div v-if="error" class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 text-red-600 dark:text-red-400 text-sm">
            {{ error }}
          </div>
        </form>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-slate-700/60 bg-gray-50/50 dark:bg-slate-800/30">
          <button @click="$emit('close')" type="button" class="px-5 py-2 rounded-xl text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700">
            {{ $t('actions.cancel') }}
          </button>
          <button
            @click="submit"
            :disabled="saving || !form.name || !form.gender"
            class="flex items-center gap-2 px-5 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-violet-200"
          >
            <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ saving ? $t('actions.saving') : (editMode ? $t('actions.saveChanges') : $t('actions.addPersonTitle')) }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { usePeopleStore } from '@/stores/people'

const props = defineProps({
  people: { type: Array, default: () => [] },
  editPerson: { type: Object, default: null },
})

const emit = defineEmits(['close', 'saved'])

const store = usePeopleStore()
const fileInputRef = ref(null)
const photoPreview = ref(null)
const photoFile = ref(null)
const saving = ref(false)
const error = ref('')

// ── Parent searchable dropdown ────────────────────────────────────────────
const parentInputRef    = ref(null)   // wrapper div
const parentInputElRef  = ref(null)   // the <input> element
const parentSearch      = ref('')
const parentOpen        = ref(false)
const highlightIndex    = ref(-1)
const dropdownStyle     = ref({})

const sortedPeople = computed(() =>
  [...props.people].sort((a, b) => a.name.localeCompare(b.name))
)

const filteredParents = computed(() => {
  const q = parentSearch.value.trim().toLowerCase()
  const list = editMode.value
    ? sortedPeople.value.filter(p => p.id !== props.editPerson?.id)
    : sortedPeople.value
  if (!q) return list
  return list.filter(p => p.name.toLowerCase().includes(q))
})

const selectedParentObj = computed(() =>
  props.people.find(p => p.id === form.value.parent_id) || null
)

const selectedParentLabel = computed(() =>
  selectedParentObj.value ? selectedParentObj.value.name : ''
)

function openParentDropdown() {
  // Calculate fixed position based on the input's bounding rect
  if (parentInputRef.value) {
    const rect = parentInputRef.value.getBoundingClientRect()
    const spaceBelow = window.innerHeight - rect.bottom
    const spaceAbove = rect.top
    const openUpward = spaceBelow < 240 && spaceAbove > spaceBelow

    dropdownStyle.value = {
      left:  rect.left + 'px',
      width: rect.width + 'px',
      ...(openUpward
        ? { bottom: (window.innerHeight - rect.top) + 'px', top: 'auto' }
        : { top: (rect.bottom + 4) + 'px', bottom: 'auto' }),
    }
  }
  parentOpen.value = true
}

function selectParent(p) {
  form.value.parent_id = p ? p.id : ''
  parentSearch.value   = ''
  parentOpen.value     = false
  highlightIndex.value = -1
}

function clearParent() {
  form.value.parent_id = ''
  parentSearch.value   = ''
  parentInputElRef.value?.focus()
}

function highlightNext() {
  if (!parentOpen.value) { openParentDropdown(); return }
  highlightIndex.value = Math.min(highlightIndex.value + 1, filteredParents.value.length - 1)
}

function highlightPrev() {
  highlightIndex.value = Math.max(highlightIndex.value - 1, -1)
}

function selectHighlighted() {
  if (highlightIndex.value >= 0 && filteredParents.value[highlightIndex.value]) {
    selectParent(filteredParents.value[highlightIndex.value])
  } else {
    parentOpen.value = false
  }
}

// Close dropdown when clicking outside
function onOutsideClick(e) {
  if (
    parentInputRef.value && !parentInputRef.value.contains(e.target) &&
    !e.target.closest('[data-parent-dropdown]')
  ) {
    parentOpen.value = false
  }
}
onMounted(() => document.addEventListener('mousedown', onOutsideClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', onOutsideClick))

// ── Form ──────────────────────────────────────────────────────────────────
const editMode = computed(() => !!props.editPerson)

const form = ref({
  name: '',
  description: '',
  gender: '',
  birth_date: '',
  death_date: '',
  parent_id: '',
})

watch(() => props.editPerson, (val) => {
  if (val) {
    form.value = {
      name: val.name || '',
      gender: val.gender || '',
      description: val.description || '',
      birth_date: val.birth_date ? val.birth_date.split('T')[0] : '',
      death_date: val.death_date ? val.death_date.split('T')[0] : '',
      parent_id: val.parent_id || '',
    }
    photoPreview.value = val.photo_url || null
  }
}, { immediate: true })

function triggerFileInput() {
  fileInputRef.value?.click()
}

function onPhotoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  photoFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => { photoPreview.value = ev.target.result }
  reader.readAsDataURL(file)
}

function clearPhoto() {
  photoPreview.value = null
  photoFile.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
}

async function submit() {
  if (!form.value.name || !form.value.gender) return

  saving.value = true
  error.value = ''

  try {
    const fd = new FormData()
    fd.append('name', form.value.name)
    fd.append('description', form.value.description)
    fd.append('gender', form.value.gender)
    if (form.value.birth_date) fd.append('birth_date', form.value.birth_date)
    if (form.value.death_date) fd.append('death_date', form.value.death_date)
    if (form.value.parent_id) fd.append('parent_id', form.value.parent_id)
    if (photoFile.value) fd.append('photo', photoFile.value)

    if (editMode.value) {
      await store.updatePerson(props.editPerson.id, fd)
    } else {
      await store.addPerson(fd)
    }

    emit('saved')
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'An error occurred'
  } finally {
    saving.value = false
  }
}
</script>
