<template>
  <div class="tree-node flex flex-col items-center">

    <!-- ── Couple row ──────────────────────────────────────────────────── -->
    <div class="relative flex items-center" ref="coupleRowRef">

      <!-- Main person card — data-person-id used by FamilyTree SVG math -->
      <div ref="cardRef" :data-person-id="node.id" class="relative">
        <PersonCard :person="node" @click="$emit('person-click', node)" />

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

      <!-- ── Spouse section ────────────────────────────────────────────── -->
      <template v-if="node.spouse">

        <!-- Invisible flex spacer — keeps spouse at default position in flow -->
        <div :style="{ width: DEFAULT_GAP + 'px', flexShrink: 0 }"></div>

        <!-- Spouse card wrapper — draggable via CSS transform -->
        <div
          class="relative flex-shrink-0"
          :style="{
            transform: `translate(${spouseOffset.x}px, ${spouseOffset.y}px)`,
            zIndex: 5,
            cursor: spouseDragging ? 'grabbing' : 'default',
          }"
        >
          <!-- Drag handle (styled like root-node handles in FamilyTree) -->
          <div
            class="absolute -top-7 left-1/2 -translate-x-1/2 flex items-center gap-1.5 px-3 py-1 rounded-full select-none
              bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-700/60 shadow-sm
              text-amber-400 dark:text-amber-500 hover:text-amber-600 dark:hover:text-amber-300
              hover:border-amber-400 dark:hover:border-amber-500 transition-colors"
            :class="spouseDragging ? 'cursor-grabbing' : 'cursor-grab'"
            @mousedown.stop="startSpouseDrag"
            @touchstart.stop.passive="startSpouseTouchDrag"
          >
            <!-- Grip dots icon -->
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 9a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-6 7a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
            </svg>
            <span class="text-xs font-medium">{{ spouseDisplay?.name.split(' ')[0] }}</span>
          </div>

          <!-- Spouse PersonCard — click opens details -->
          <div
            @mousedown.stop
            @click.stop="$emit('person-click', spouseDisplay)"
            class="cursor-pointer"
          >
            <PersonCard :person="spouseDisplay" />
          </div>
        </div>

        <!-- ── SVG dynamic connector line ──────────────────────────────── -->
        <!--
          Draws from person card right-center → spouse card left-center.
          Uses overflow:visible so it works at any drag offset.
        -->
        <svg
          class="absolute top-0 left-0 pointer-events-none overflow-visible"
          style="width: 1px; height: 1px; z-index: 4;"
        >
          <!-- Dashed amber line -->
          <line
            :x1="CARD_W"
            :y1="halfH"
            :x2="CARD_W + DEFAULT_GAP + spouseOffset.x"
            :y2="halfH + spouseOffset.y"
            stroke="#f59e0b"
            stroke-width="2"
            stroke-dasharray="6 3"
            stroke-linecap="round"
            opacity="0.9"
          />
          <!-- 💍 emoji centered on the midpoint -->
          <text
            :x="CARD_W + (DEFAULT_GAP + spouseOffset.x) / 2"
            :y="halfH + spouseOffset.y / 2 - 8"
            text-anchor="middle"
            dominant-baseline="middle"
            style="font-size: 13px; user-select: none;"
          >💍</text>
          <!-- Marriage year just below the ring -->
          <text
            v-if="marriageYear"
            :x="CARD_W + (DEFAULT_GAP + spouseOffset.x) / 2"
            :y="halfH + spouseOffset.y / 2 + 9"
            text-anchor="middle"
            dominant-baseline="middle"
            fill="#f59e0b"
            style="font-size: 10px; font-weight: 700; user-select: none;"
          >{{ marriageYear }}</text>
        </svg>

      </template>
    </div><!-- end couple row -->

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

    <!-- Add child modal -->
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import PersonCard from './PersonCard.vue'
import AddChildModal from './AddChildModal.vue'
import { useFocusNode } from '@/composables/useFocusNode'

const props = defineProps({
  node:  { type: Object, required: true },
  depth: { type: Number, default: 0 },
})

defineEmits(['person-click', 'collapse-toggle'])

// ── Layout constants ───────────────────────────────────────────────────────
const CARD_W     = 165   // PersonCard fixed width (px)
const DEFAULT_GAP = 56   // default horizontal gap between person and spouse

// ── Couple row height (for SVG midpoint) ──────────────────────────────────
const coupleRowRef = ref(null)
const coupleRowHeight = ref(160)

onMounted(() => {
  nextTick(() => {
    coupleRowHeight.value = coupleRowRef.value?.offsetHeight ?? 160
  })
})

const halfH = computed(() => coupleRowHeight.value / 2)

// ── Spouse display ─────────────────────────────────────────────────────────
const spouseDisplay = computed(() => {
  if (!props.node.spouse) return null
  return { ...props.node.spouse, children: [] }
})

const marriageYear = computed(() => {
  const d = props.node.spouse?.marriage_date
  return d ? new Date(d).getFullYear() : null
})

// ── Spouse drag ────────────────────────────────────────────────────────────
const SPOUSE_POS_KEY = 'familytree_spouse_positions'

function loadSpouseOffset(personId) {
  try {
    const all = JSON.parse(localStorage.getItem(SPOUSE_POS_KEY) || '{}')
    return all[personId] ?? { x: 0, y: 0 }
  } catch {
    return { x: 0, y: 0 }
  }
}

function saveSpouseOffset(personId, offset) {
  try {
    const all = JSON.parse(localStorage.getItem(SPOUSE_POS_KEY) || '{}')
    all[personId] = offset
    localStorage.setItem(SPOUSE_POS_KEY, JSON.stringify(all))
  } catch {}
}

const spouseOffset   = ref(loadSpouseOffset(props.node.id))
const spouseDragging = ref(false)
let   spouseDragOrigin = null   // { mouseX, mouseY, origX, origY }

function startSpouseDrag(e) {
  spouseDragging.value = true
  spouseDragOrigin = {
    mouseX: e.clientX,
    mouseY: e.clientY,
    origX:  spouseOffset.value.x,
    origY:  spouseOffset.value.y,
  }
  document.addEventListener('mousemove', onSpouseMouseMove)
  document.addEventListener('mouseup',   onSpouseMouseUp)
}

function startSpouseTouchDrag(e) {
  if (!e.touches.length) return
  spouseDragging.value = true
  spouseDragOrigin = {
    mouseX: e.touches[0].clientX,
    mouseY: e.touches[0].clientY,
    origX:  spouseOffset.value.x,
    origY:  spouseOffset.value.y,
  }
  document.addEventListener('touchmove', onSpouseTouchMove, { passive: false })
  document.addEventListener('touchend',  onSpouseTouchEnd)
}

function onSpouseMouseMove(e) {
  if (!spouseDragging.value || !spouseDragOrigin) return
  spouseOffset.value = {
    x: spouseDragOrigin.origX + (e.clientX - spouseDragOrigin.mouseX),
    y: spouseDragOrigin.origY + (e.clientY - spouseDragOrigin.mouseY),
  }
}

function onSpouseTouchMove(e) {
  e.preventDefault()
  if (!spouseDragging.value || !spouseDragOrigin || !e.touches.length) return
  spouseOffset.value = {
    x: spouseDragOrigin.origX + (e.touches[0].clientX - spouseDragOrigin.mouseX),
    y: spouseDragOrigin.origY + (e.touches[0].clientY - spouseDragOrigin.mouseY),
  }
}

function onSpouseMouseUp() {
  if (!spouseDragging.value) return
  spouseDragging.value = false
  saveSpouseOffset(props.node.id, spouseOffset.value)
  spouseDragOrigin = null
  document.removeEventListener('mousemove', onSpouseMouseMove)
  document.removeEventListener('mouseup',   onSpouseMouseUp)
}

function onSpouseTouchEnd() {
  if (!spouseDragging.value) return
  spouseDragging.value = false
  saveSpouseOffset(props.node.id, spouseOffset.value)
  spouseDragOrigin = null
  document.removeEventListener('touchmove', onSpouseTouchMove)
  document.removeEventListener('touchend',  onSpouseTouchEnd)
}

onUnmounted(() => {
  document.removeEventListener('mousemove', onSpouseMouseMove)
  document.removeEventListener('mouseup',   onSpouseMouseUp)
  document.removeEventListener('touchmove', onSpouseTouchMove)
  document.removeEventListener('touchend',  onSpouseTouchEnd)
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
const cardRef      = ref(null)
const STORAGE_KEY  = 'familytree_collapsed_nodes'

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
