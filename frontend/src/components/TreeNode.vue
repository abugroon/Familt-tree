<template>
  <div class="tree-node flex flex-col items-center">

    <!-- ── Couple row: main person + optional spouse ───────────────────── -->
    <div class="flex items-center">

      <!-- Main person card wrapper — data-person-id used for SVG line math -->
      <div ref="cardRef" :data-person-id="node.id" class="relative">
        <PersonCard
          :person="node"
          @click="$emit('person-click', node)"
        />

        <!-- Expand / collapse toggle -->
        <button
          v-if="node.children && node.children.length"
          @click.stop="toggleCollapse"
          class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full border-2 border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center justify-center z-10 shadow-md"
          :title="collapsed ? $t('actions.expand') : $t('actions.collapse')"
        >
          <svg
            class="w-3 h-3 text-gray-500 dark:text-slate-400 transition-transform duration-300"
            :class="{ 'rotate-180': !collapsed }"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>

      <!-- Marriage connector + spouse card ───────────────────────────── -->
      <template v-if="node.spouse">

        <!-- Dashed connector with 💍 badge -->
        <div class="relative flex-shrink-0 flex items-center" style="width: 56px;">
          <div
            class="w-full border-t-2 border-dashed border-amber-400 dark:border-amber-500"
          ></div>
          <!-- Ring badge centered on the line -->
          <div class="absolute left-1/2 -translate-x-1/2 flex flex-col items-center -translate-y-1/2" style="top: 0;">
            <div class="bg-white dark:bg-slate-900 px-1 flex flex-col items-center leading-none">
              <span style="font-size:14px; line-height:1;">💍</span>
              <span
                v-if="node.spouse.marriage_date"
                class="text-[10px] font-semibold text-amber-500 dark:text-amber-400 whitespace-nowrap mt-0.5"
              >{{ marriageYear }}</span>
            </div>
          </div>
        </div>

        <!-- Spouse card (read-only, click opens their details) -->
        <div
          @mousedown.stop
          @click.stop="$emit('person-click', spouseDisplay)"
          class="cursor-pointer"
        >
          <PersonCard :person="spouseDisplay" />
        </div>

      </template>
    </div>

    <!-- ── Add child button ────────────────────────────────────────────── -->
    <button
      type="button"
      class="person-card mt-2 flex items-center gap-1 px-3 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-white dark:bg-slate-800 border border-emerald-300 dark:border-emerald-700 rounded-full shadow-sm hover:bg-emerald-50 dark:hover:bg-emerald-900/30 active:scale-95 transition-all"
      @click="showChildModal = true"
    >
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
      </svg>
      إضافة ابن
    </button>

    <!-- Add child modal — mounted directly here -->
    <AddChildModal
      v-if="showChildModal"
      :parent-id="node.id"
      :parent-name="node.name"
      @close="showChildModal = false"
      @saved="onChildSaved"
    />

    <!-- ── Children ────────────────────────────────────────────────────── -->
    <Transition name="tree-expand">
      <div
        v-if="!collapsed && node.children && node.children.length"
        class="flex items-start mt-6 pt-2"
        :style="{ gap: childrenGap }"
      >
        <TreeNode
          v-for="child in node.children"
          :key="child.id"
          :node="child"
          :depth="depth + 1"
          @person-click="$emit('person-click', $event)"
          @collapse-toggle="$emit('collapse-toggle')"
        />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import PersonCard from './PersonCard.vue'
import AddChildModal from './AddChildModal.vue'
import { useFocusNode } from '@/composables/useFocusNode'

const props = defineProps({
  node:  { type: Object, required: true },
  depth: { type: Number, default: 0 },
})

defineEmits(['person-click', 'collapse-toggle'])

// ── Spouse display ─────────────────────────────────────────────────────────
const spouseDisplay = computed(() => {
  if (!props.node.spouse) return null
  return { ...props.node.spouse, children: [] }
})

const marriageYear = computed(() => {
  const d = props.node.spouse?.marriage_date
  return d ? new Date(d).getFullYear() : null
})

// ── Children gap ───────────────────────────────────────────────────────────
const childrenGap = computed(() => {
  const count = props.node.children?.length ?? 0
  const px = 28 + Math.max(0, count - 1) * 22
  return `${px}px`
})

// ── Add child modal ────────────────────────────────────────────────────────
const showChildModal = ref(false)
const { focusNode }  = useFocusNode()

function onChildSaved() {
  showChildModal.value = false
  focusNode(props.node.id)
}

// ── Collapse / expand ──────────────────────────────────────────────────────
const cardRef    = ref(null)
const STORAGE_KEY = 'familytree_collapsed_nodes'

function loadCollapsed() {
  try { return new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')) }
  catch { return new Set() }
}

function saveCollapsed(set) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify([...set]))
}

const collapsed = ref(loadCollapsed().has(props.node.id))

function toggleCollapse() {
  collapsed.value = !collapsed.value
  const set = loadCollapsed()
  if (collapsed.value) set.add(props.node.id)
  else set.delete(props.node.id)
  saveCollapsed(set)
}
</script>

<style scoped>
.tree-expand-enter-active,
.tree-expand-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}
.tree-expand-enter-from,
.tree-expand-leave-to {
  opacity: 0;
  transform: scaleY(0.8) translateY(-16px);
  transform-origin: top center;
}
</style>
