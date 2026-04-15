<template>
  <div class="flex flex-col h-full">

    <!-- Family Navigator -->
    <FamilyNavigator
      :roots="trees"
      :selected="selectedRootId"
      @select="onSelectRoot"
    />

    <!-- Canvas -->
    <div
      class="relative flex-1 overflow-hidden cursor-grab active:cursor-grabbing select-none"
      ref="containerRef"
      @mousedown.left="onCanvasMouseDown"
      @mousemove="onMouseMove"
      @mouseup="onMouseUp"
      @mouseleave="onMouseUp"
      @wheel.prevent="onWheel"
      @touchstart.passive="startTouch"
      @touchmove.prevent="onTouchMove"
      @touchend="stopTouch"
    >
      <!-- Dot grid background -->
      <div class="absolute inset-0 pointer-events-none" :style="gridStyle"></div>

      <!-- Zoom controls -->
      <div class="absolute bottom-6 end-6 flex flex-col gap-2 z-20">
        <button @click="zoomIn"    class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 flex items-center justify-center shadow-md active:scale-95"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg></button>
        <button @click="zoomOut"   class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 flex items-center justify-center shadow-md active:scale-95"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"   d="M20 12H4"/></svg></button>
        <button @click="resetView" class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 flex items-center justify-center shadow-md active:scale-95 text-base">↺</button>
      </div>

      <!-- Scale indicator -->
      <div class="absolute bottom-6 end-20 z-20">
        <div class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-xs text-gray-500 dark:text-slate-400 font-mono shadow-sm">
          {{ Math.round(scale * 100) }}%
        </div>
      </div>

      <!-- Transformable canvas -->
      <div
        ref="canvasRef"
        class="absolute top-0 left-0"
        :style="{ transform: `translate(${panX}px, ${panY}px) scale(${scale})`, transformOrigin: '0 0', willChange: 'transform' }"
      >
        <!-- SVG connector lines -->
        <svg
          class="absolute top-0 left-0 pointer-events-none overflow-visible"
          :width="svgSize.w" :height="svgSize.h"
          :style="{ zIndex: 1 }"
        >
          <g v-for="conn in connections" :key="conn.id">
            <path :d="conn.path" fill="none" :stroke="conn.color" stroke-width="4" opacity="0.07" />
            <path :d="conn.path" fill="none" :stroke="conn.color" stroke-width="2" opacity="0.55" stroke-linecap="round"/>
            <circle :cx="conn.cx" :cy="conn.cy" r="3.5" :fill="conn.color" opacity="0.7"/>
          </g>
        </svg>

        <!-- Root trees — absolutely positioned, individually draggable -->
        <div
          v-for="tree in visibleTrees"
          :key="`${selectedRootId ?? 'all'}-${tree.id}`"
          class="absolute"
          :style="{ left: pos(tree.id).x + 'px', top: pos(tree.id).y + 'px', zIndex: 2 }"
        >
          <!-- Drag handle for root node -->
          <div
            class="absolute -top-7 left-1/2 -translate-x-1/2 flex items-center gap-1.5 px-3 py-1 rounded-full cursor-grab active:cursor-grabbing select-none
              bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-sm text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300"
            @mousedown.stop="startRootDrag($event, tree.id)"
            @touchstart.stop.passive="startRootTouchDrag($event, tree.id)"
          >
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 9a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-6 7a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
            </svg>
            <span class="text-xs font-medium">{{ tree.name.split(' ')[0] }}</span>
          </div>

          <TreeNode
            :node="tree"
            :depth="0"
            @person-click="$emit('person-click', $event)"
            @collapse-toggle="scheduleLineUpdate"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue'
import { toPng } from 'html-to-image'
import { useFocusNode } from '@/composables/useFocusNode'
import TreeNode from './TreeNode.vue'
import FamilyNavigator from './FamilyNavigator.vue'

const props = defineProps({
  trees: { type: Array, default: () => [] }
})
defineEmits(['person-click'])

// ── Navigator ─────────────────────────────────────────────────────────────
const selectedRootId = ref(null)
const visibleTrees = computed(() =>
  selectedRootId.value === null
    ? props.trees
    : props.trees.filter(t => t.id === selectedRootId.value)
)
function onSelectRoot(id) {
  selectedRootId.value = id
  scale.value = 0.85
  if (id !== null) {
    // Pan so the selected tree starts at a comfortable top-left position
    const p = pos(id)
    panX.value = 80 - p.x * scale.value
    panY.value = 60 - p.y * scale.value
  } else {
    panX.value = 80
    panY.value = 60
  }
  nextTick(scheduleLineUpdate)
}

// ── Refs ──────────────────────────────────────────────────────────────────
const containerRef = ref(null)
const canvasRef    = ref(null)
const scale  = ref(0.85)
const panX   = ref(80)
const panY   = ref(60)
const connections = ref([])
const svgSize = reactive({ w: 4000, h: 4000 })

// ── Root node positions ───────────────────────────────────────────────────
const STORAGE_KEY = 'familytree_root_positions'
const rootPositions = ref(loadPositions())

function loadPositions() {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') }
  catch { return {} }
}
function savePositions() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(rootPositions.value))
}
function pos(id) {
  return rootPositions.value[id] || { x: 0, y: 0 }
}
function initMissingPositions(trees) {
  let changed = false
  trees.forEach((tree, i) => {
    if (!rootPositions.value[tree.id]) {
      rootPositions.value[tree.id] = { x: i * 300, y: 60 }
      changed = true
    }
  })
  if (changed) savePositions()
}

watch(() => props.trees, (trees) => {
  initMissingPositions(trees)
  nextTick(scheduleLineUpdate)
}, { immediate: true, deep: false })

// ── Canvas drag ───────────────────────────────────────────────────────────
const isDragging = ref(false)
const dragStart  = ref({ x: 0, y: 0 })
const panStart   = ref({ x: 0, y: 0 })

function onCanvasMouseDown(e) {
  if (e.target.closest('.person-card') || e.target.closest('button') || draggingRootId.value) return
  isDragging.value = true
  dragStart.value  = { x: e.clientX, y: e.clientY }
  panStart.value   = { x: panX.value, y: panY.value }
}

// ── Root drag ─────────────────────────────────────────────────────────────
const draggingRootId = ref(null)
let rootDragOrigin   = null   // { mouseX, mouseY, origX, origY }

function startRootDrag(e, rootId) {
  draggingRootId.value = rootId
  const p = pos(rootId)
  rootDragOrigin = { mouseX: e.clientX, mouseY: e.clientY, origX: p.x, origY: p.y }
}

function startRootTouchDrag(e, rootId) {
  if (!e.touches.length) return
  draggingRootId.value = rootId
  const p = pos(rootId)
  rootDragOrigin = { mouseX: e.touches[0].clientX, mouseY: e.touches[0].clientY, origX: p.x, origY: p.y }
}

function onMouseMove(e) {
  if (draggingRootId.value && rootDragOrigin) {
    const dx = (e.clientX - rootDragOrigin.mouseX) / scale.value
    const dy = (e.clientY - rootDragOrigin.mouseY) / scale.value
    rootPositions.value[draggingRootId.value] = {
      x: rootDragOrigin.origX + dx,
      y: rootDragOrigin.origY + dy,
    }
    scheduleLineUpdate()
  } else if (isDragging.value) {
    panX.value = panStart.value.x + (e.clientX - dragStart.value.x)
    panY.value = panStart.value.y + (e.clientY - dragStart.value.y)
  }
}

function onMouseUp() {
  if (draggingRootId.value) {
    savePositions()
    draggingRootId.value = null
    rootDragOrigin = null
  }
  isDragging.value = false
}

// ── Touch (canvas pan) ────────────────────────────────────────────────────
const touchState = ref(null)
function startTouch(e) {
  if (draggingRootId.value) return
  if (e.touches.length === 1)
    touchState.value = { x: e.touches[0].clientX, y: e.touches[0].clientY, panX: panX.value, panY: panY.value }
}
function onTouchMove(e) {
  if (draggingRootId.value && rootDragOrigin && e.touches.length) {
    const dx = (e.touches[0].clientX - rootDragOrigin.mouseX) / scale.value
    const dy = (e.touches[0].clientY - rootDragOrigin.mouseY) / scale.value
    rootPositions.value[draggingRootId.value] = {
      x: rootDragOrigin.origX + dx,
      y: rootDragOrigin.origY + dy,
    }
    scheduleLineUpdate()
    return
  }
  if (e.touches.length === 1 && touchState.value) {
    panX.value = touchState.value.panX + (e.touches[0].clientX - touchState.value.x)
    panY.value = touchState.value.panY + (e.touches[0].clientY - touchState.value.y)
  }
}
function stopTouch() {
  if (draggingRootId.value) { savePositions(); draggingRootId.value = null; rootDragOrigin = null }
  touchState.value = null
}

// ── Zoom ──────────────────────────────────────────────────────────────────
function onWheel(e) { scale.value = Math.min(2, Math.max(0.2, scale.value - e.deltaY * 0.001)) }
function zoomIn()   { scale.value = Math.min(2,  +(scale.value + 0.1).toFixed(2)) }
function zoomOut()  { scale.value = Math.max(0.2, +(scale.value - 0.1).toFixed(2)) }
function resetView(){ scale.value = 0.85; panX.value = 80; panY.value = 60 }

// ── Grid background ───────────────────────────────────────────────────────
const gridStyle = computed(() => {
  const dark  = document.documentElement.classList.contains('dark')
  const color = dark ? '#334155' : '#cbd5e1'
  return { backgroundImage: `radial-gradient(circle, ${color} 1px, transparent 1px)`, backgroundSize: '28px 28px' }
})

// ── SVG Lines ─────────────────────────────────────────────────────────────
let lineTimer = null
function scheduleLineUpdate() {
  clearTimeout(lineTimer)
  lineTimer = setTimeout(drawLines, 120)
}

function drawLines() {
  nextTick(() => {
    const canvas = canvasRef.value
    if (!canvas) return
    const originX = canvas.getBoundingClientRect().left
    const originY = canvas.getBoundingClientRect().top
    function toCanvas(rect) {
      return { x: (rect.left - originX) / scale.value, y: (rect.top - originY) / scale.value, w: rect.width / scale.value, h: rect.height / scale.value }
    }
    const newConns = []
    function processNode(node) {
      if (!node.children?.length) return
      const parentEl = canvas.querySelector(`[data-person-id="${node.id}"]`)
      if (!parentEl) return
      const pr = toCanvas(parentEl.getBoundingClientRect())
      const px = pr.x + pr.w / 2
      const py = pr.y + pr.h
      const color = node.gender === 'male' ? '#3b82f6' : '#ec4899'
      for (const child of node.children) {
        const childEl = canvas.querySelector(`[data-person-id="${child.id}"]`)
        if (!childEl) continue
        const cr = toCanvas(childEl.getBoundingClientRect())
        const cx = cr.x + cr.w / 2
        const cy = cr.y
        const midY = py + (cy - py) * 0.5
        newConns.push({ id: `${node.id}-${child.id}`, path: `M ${px} ${py} C ${px} ${midY}, ${cx} ${midY}, ${cx} ${cy}`, color, cx, cy })
        processNode(child)
      }
    }
    for (const tree of visibleTrees.value) processNode(tree)
    connections.value = newConns
    svgSize.w = Math.max(canvas.scrollWidth / scale.value + 400, 3000)
    svgSize.h = Math.max(canvas.scrollHeight / scale.value + 400, 3000)
  })
}

onMounted(() => {
  scheduleLineUpdate()
  const mo = new MutationObserver(scheduleLineUpdate)
  if (canvasRef.value) mo.observe(canvasRef.value, { childList: true, subtree: true, attributes: true })
})

// ── Focus / pan to node ───────────────────────────────────────────────────
const { pendingFocusId, clearFocus } = useFocusNode()

watch(pendingFocusId, (id) => {
  if (id === null) return
  // Wait for tree to re-render after data fetch, then pan
  setTimeout(() => {
    const el = canvasRef.value?.querySelector(`[data-person-id="${id}"]`)
    if (!el || !containerRef.value) { clearFocus(); return }
    const containerRect = containerRef.value.getBoundingClientRect()
    const canvasRect    = canvasRef.value.getBoundingClientRect()
    const elRect        = el.getBoundingClientRect()
    const elCX = (elRect.left - canvasRect.left + elRect.width  / 2) / scale.value
    const elCY = (elRect.top  - canvasRect.top  + elRect.height / 2) / scale.value
    panX.value = containerRect.width  / 2 - elCX * scale.value
    panY.value = containerRect.height / 2 - elCY * scale.value
    clearFocus()
    scheduleLineUpdate()
  }, 400)
})

// ── Export Image ──────────────────────────────────────────────────────────
const exporting = ref(false)

async function exportImage() {
  if (!canvasRef.value || exporting.value) return
  exporting.value = true
  try {
    // Temporarily reset transform so the full tree is captured
    const prevScale = scale.value
    const prevPanX  = panX.value
    const prevPanY  = panY.value
    scale.value = 1
    panX.value  = 20
    panY.value  = 20
    await nextTick()

    const isDark = document.documentElement.classList.contains('dark')
    const dataUrl = await toPng(canvasRef.value, {
      backgroundColor: isDark ? '#020617' : '#f8fafc',
      pixelRatio: 2,
      cacheBust: true,
    })

    // Restore transform
    scale.value = prevScale
    panX.value  = prevPanX
    panY.value  = prevPanY

    const link = document.createElement('a')
    link.download = 'family-tree.png'
    link.href = dataUrl
    link.click()
  } catch (e) {
    console.error('Export failed:', e)
  } finally {
    exporting.value = false
  }
}

defineExpose({ exportImage, exporting })
</script>

