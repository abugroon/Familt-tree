<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')"></div>

      <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-slate-700/60">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">إضافة ابن</h2>
            <p class="text-sm text-gray-400 dark:text-slate-500">الأب: <span class="font-medium text-violet-600 dark:text-violet-400">{{ parentName }}</span></p>
          </div>
          <button @click="$emit('close')" class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 flex items-center justify-center text-gray-400 dark:text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
          <!-- Name -->
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">الاسم *</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="اكتب الاسم"
              required
              autofocus
              class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 text-sm"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">وصف أو ملاحظات</label>
            <textarea
              v-model="form.description"
              placeholder="أي وصف أو ملاحظات"
              class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 text-sm"
            />
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">الجنس *</label>
            <div class="flex gap-3">
              <label
                v-for="g in ['male', 'female']" :key="g"
                class="flex-1 flex items-center gap-2.5 px-4 py-2.5 rounded-xl border cursor-pointer transition-all"
                :class="form.gender === g
                  ? g === 'male'
                    ? 'bg-blue-50 dark:bg-blue-900/40 border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300'
                    : 'bg-pink-50 dark:bg-pink-900/40 border-pink-300 dark:border-pink-600 text-pink-700 dark:text-pink-300'
                  : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 text-gray-500'"
              >
                <input type="radio" v-model="form.gender" :value="g" class="hidden" />
                <span class="text-lg">{{ g === 'male' ? '👨' : '👩' }}</span>
                <span class="text-sm font-medium">{{ g === 'male' ? 'ذكر' : 'أنثى' }}</span>
              </label>
            </div>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">تاريخ الميلاد</label>
              <input v-model="form.birth_date" type="date" class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 focus:outline-none focus:border-violet-400 text-sm" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">تاريخ الوفاة</label>
              <input v-model="form.death_date" type="date" class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 focus:outline-none focus:border-violet-400 text-sm" />
            </div>
          </div>

          <!-- Error -->
          <div v-if="error" class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 text-red-600 dark:text-red-400 text-sm">
            {{ error }}
          </div>
        </form>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-slate-700/60 bg-gray-50/50 dark:bg-slate-800/30">
          <button @click="$emit('close')" type="button" class="px-5 py-2 rounded-xl text-sm font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700">
            إلغاء
          </button>
          <button
            @click="submit"
            :disabled="saving || !form.name || !form.gender"
            class="flex items-center gap-2 px-5 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-semibold disabled:opacity-50 shadow-md"
          >
            <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ saving ? 'جاري الحفظ...' : 'إضافة' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { usePeopleStore } from '@/stores/people'

const props = defineProps({
  parentId:   { type: [Number, String], required: true },
  parentName: { type: String, default: '' },
})

const emit  = defineEmits(['close', 'saved'])
const store = usePeopleStore()

const saving = ref(false)
const error  = ref('')

const form = ref({
  name:        '',
  description: '',
  gender:      '',
  birth_date:  '',
  death_date:  '',
})

async function submit() {
  if (!form.value.name || !form.value.gender) return
  saving.value = true
  error.value  = ''
  try {
    const fd = new FormData()
    fd.append('name',      form.value.name)
    fd.append('description', form.value.description)
    fd.append('gender',    form.value.gender)
    fd.append('parent_id', props.parentId)
    if (form.value.birth_date) fd.append('birth_date', form.value.birth_date)
    if (form.value.death_date) fd.append('death_date', form.value.death_date)
    await store.addPerson(fd)
    emit('saved')
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'حدث خطأ'
  } finally {
    saving.value = false
  }
}
</script>
