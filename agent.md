# Family Tree System — Project Reference

## Overview

A genealogy web application for building and exploring family trees.
- **Backend**: Laravel 11 (PHP) — REST API
- **Frontend**: Vue 3 + Vite + TailwindCSS v4
- **Database**: MySQL
- **Language**: Arabic (default, RTL) / English toggle
- **Theme**: Light / Dark toggle

---

## Directory Structure

```
Family Tree System/
├── backend/          ← Laravel 11 API
│   ├── app/
│   │   ├── Http/Controllers/PersonController.php
│   │   └── Models/Person.php
│   ├── database/
│   │   ├── migrations/2026_04_14_111332_create_people_table.php
│   │   └── seeders/PeopleSeeder.php
│   ├── routes/api.php
│   └── bootstrap/app.php
└── frontend/         ← Vue 3 SPA
    └── src/
        ├── App.vue
        ├── main.js
        ├── style.css
        ├── components/
        │   ├── FamilyTree.vue
        │   ├── FamilyNavigator.vue
        │   ├── TreeNode.vue
        │   ├── PersonCard.vue
        │   ├── PersonDetailsModal.vue
        │   └── AddPersonModal.vue
        ├── stores/people.js
        ├── composables/useTheme.js
        └── i18n/
            ├── index.js
            └── locales/
                ├── ar.js   ← default
                └── en.js
```

---

## Backend

### Running
```bash
cd backend
php artisan serve          # runs on http://localhost:8000
php artisan storage:link   # required once for photo URLs
```

### Database — `people` table
| Column      | Type                   | Notes                          |
|-------------|------------------------|--------------------------------|
| id          | bigint (PK)            |                                |
| name        | string                 | required                       |
| gender      | enum('male','female')  | required                       |
| birth_date  | date                   | nullable                       |
| death_date  | date                   | nullable                       |
| photo       | string                 | nullable, path in storage/     |
| parent_id   | foreignId → people.id  | nullable, nullOnDelete         |
| created_at  | timestamp              |                                |
| updated_at  | timestamp              |                                |

Self-referential: `parent_id` points to another row in `people`.

### Model — `Person.php`
- `$fillable`: name, gender, birth_date, death_date, photo, parent_id
- `$casts`: birth_date, death_date → 'date'
- `$appends`: ['photo_url']
- Relations: `parent()` (BelongsTo self), `children()` (HasMany self)
- Accessor `getPhotoUrlAttribute()`: returns `asset('storage/' . $this->photo)` or null

### API Routes (`routes/api.php`)
| Method | URI                | Action                        |
|--------|--------------------|-------------------------------|
| GET    | /api/tree          | roots() — all root trees      |
| GET    | /api/tree/{id}     | tree($id) — single tree       |
| GET    | /api/people        | index() — flat list           |
| POST   | /api/people        | store()                       |
| GET    | /api/people/{id}   | show()                        |
| PUT    | /api/people/{id}   | update() — via POST+_method   |
| DELETE | /api/people/{id}   | destroy()                     |

### Key Controller Logic (`PersonController.php`)
- `roots()`: fetches all people with `parent_id = null`, builds recursive tree for each
- `buildTree(Person)`: recursively calls `$person->children()->get()` and builds nested array
- `store/update`: validates, handles photo upload to `storage/photos/`, stores path
- `destroy`: deletes photo from disk before deleting record
- No CSRF middleware (`statefulApi()` removed from `bootstrap/app.php`)

---

## Frontend

### Running
```bash
cd frontend
npm run dev    # dev server on http://localhost:5173
npm run build  # production build to dist/
```

Vite proxies `/api` and `/storage` to `http://localhost:8000`.

### Dependencies
| Package            | Version  | Purpose                    |
|--------------------|----------|----------------------------|
| vue                | ^3.5.32  | UI framework               |
| vite               | ^8.0.8   | Build tool                 |
| @vitejs/plugin-vue | ^6.0.6   | Vue SFCs support           |
| tailwindcss        | ^4.2.2   | Styling                    |
| @tailwindcss/vite  | ^4.2.2   | Tailwind v4 Vite plugin    |
| pinia              | ^3.0.4   | State management           |
| vue-i18n           | ^9.14.5  | Internationalization       |
| axios              | ^1.15.0  | HTTP client                |

### Tailwind v4 Notes
- No `tailwind.config.js` — configured via `@tailwindcss/vite` plugin
- Dark mode via `@custom-variant dark (&:where(.dark, .dark *))` in `style.css`
- Dark mode activated by adding class `dark` to `<html>` element
- **Do NOT use `@apply` in `<style scoped>` blocks** — causes build error in v4. Use inline Tailwind classes only.
- For RTL layouts, use logical properties: `start-0`, `end-6`, `ps-4`, `pe-4`
- **Exception**: canvas/SVG coordinate systems must use physical `left-0` not `start-0`

### State Management — `stores/people.js`
```
State: trees[], allPeople[], loading, error
Actions:
  fetchTrees()       → GET /api/tree → trees[]
  fetchAllPeople()   → GET /api/people → allPeople[]
  addPerson(fd)      → POST /api/people (FormData) → refetches both
  updatePerson(id,fd)→ POST /api/people/:id + _method=PUT → refetches both
  deletePerson(id)   → DELETE /api/people/:id → refetches both
  getPerson(id)      → GET /api/people/:id
```

### i18n (`src/i18n/`)
- Default locale: Arabic (`ar`), persisted in `localStorage.locale`
- Fallback locale: English (`en`)
- `legacy: false` mode (Composition API)
- `applyLocaleDir(locale)` sets `document.documentElement.dir` (rtl/ltr) and `lang`
- Called on boot and on every locale toggle
- Translation keys: `app.*`, `person.*`, `actions.*`, `nav.*`

### Theme (`src/composables/useTheme.js`)
- Shared singleton `isDark` ref
- `init()`: reads `localStorage.theme` or falls back to `prefers-color-scheme`
- `toggle()`: flips isDark, saves to localStorage, adds/removes `.dark` on `<html>`

---

## Component Architecture

### `App.vue`
- Root component, header with: logo, member count, theme toggle, language toggle, Add Person button
- Renders `FamilyTree`, `AddPersonModal`, `PersonDetailsModal`
- Fetches `store.fetchTrees()` and `store.fetchAllPeople()` on mount
- Header buttons: `themeToggle()`, `toggleLocale()` (switches ar↔en, updates dir)

### `FamilyTree.vue`
Main canvas component. Key features:

**Navigator**: `<FamilyNavigator>` at top for selecting a root family.
- `selectedRootId` ref (null = all families)
- `visibleTrees` computed: filters `props.trees` by selectedRootId
- On select: resets pan to center on chosen tree without changing stored position

**Canvas**: `<div ref="containerRef">` with `overflow-hidden`
- Transformable inner div `<div ref="canvasRef">` with `transform: translate(panX, panY) scale(scale)`
- `transformOrigin: '0 0'`
- **Must use `left-0` not `start-0`** on canvasRef and SVG (physical positioning for coordinate math)

**Root positions**: stored in `localStorage['familytree_root_positions']` as `{ [id]: {x, y} }`
- `initMissingPositions`: places new trees at `i * 300, 60`
- Individual trees are `position: absolute` inside the canvas at `left: pos(id).x, top: pos(id).y`

**Dragging system**:
- Canvas pan: `isDragging` + `dragStart` + `panStart`
- Root node drag: `draggingRootId` + `rootDragOrigin`
- `onCanvasMouseDown`: skips if target is inside `.person-card` or `button`
- `onMouseMove`: routes to root drag or canvas pan based on `draggingRootId`
- Positions saved to localStorage on `mouseup`

**SVG Lines** (cubic bezier parent→child):
- Drawn by `drawLines()`, scheduled via `MutationObserver` + 120ms debounce
- Uses `canvas.querySelector('[data-person-id="X"]')` to find card elements
- Converts screen coords to canvas coords: `(rect.left - originX) / scale`
- Colors: blue for male parents, pink for female parents
- Dot circle drawn at child connection point

**Zoom**: wheel scroll, +/- buttons, reset. Range: 0.2–2.0.

**v-for key**: `` `${selectedRootId ?? 'all'}-${tree.id}` `` — forces TreeNode remount on navigator tab change.

### `FamilyNavigator.vue`
- Horizontal scrollable chip bar above the canvas
- "كل العائلات / All Families" chip (emits `select(null)`)
- One chip per root person (emits `select(root.id)`)
- Color: violet for all, blue for male roots, pink for female roots
- Shows children count badge on chip

### `TreeNode.vue`
- Recursive component for one person + their subtree
- `<div :data-person-id="node.id">` — used by FamilyTree SVG line logic
- `collapsed` ref (default false) — toggled by expand/collapse button
- Expand/collapse button: `absolute -bottom-3 left-1/2 -translate-x-1/2`
- Children rendered in `<Transition name="tree-expand">` when `!collapsed`
- Emits: `person-click`, `collapse-toggle`

### `PersonCard.vue`
- Visual card for one person (width: w-44)
- `@mousedown.stop` — prevents canvas from starting a drag when clicking a card
- `@click` — emits `click` event (received by TreeNode)
- Shows: avatar/initials, name, gender emoji, age badge, birth-death dates, alive/deceased, children count badge
- Color-coded: blue (male), pink (female), gray (deceased)
- `defineEmits(['click'])`

### `PersonDetailsModal.vue`
- Full-screen overlay (`z-50`, Teleport to body)
- Colored gradient banner (blue/pink/gray based on gender/deceased)
- Large avatar (w-24 rounded-2xl) overlapping banner
- Age displayed large on right
- Sections: name, dates grid, parent badge (violet), children grid (2 columns)
- Footer: Edit button (opens `AddPersonModal` inline), Delete button
- Delete shows confirmation sub-dialog (`z-60`)
- On edit saved: closes both edit modal and details modal

### `AddPersonModal.vue`
- Form modal for add/edit (edit mode when `editPerson` prop provided)
- Fields: photo upload, name, gender (radio), birth date, death date, parent (searchable)
- Photo: file input, preview, clear button
- **Parent searchable dropdown**:
  - `<input>` with `@focus` opens dropdown
  - Dropdown rendered via `<Teleport to="body">` with `position: fixed`
  - Position calculated from `getBoundingClientRect()` of input wrapper
  - Opens upward if insufficient space below (< 240px)
  - `@mousedown.prevent` on dropdown buttons (prevents input blur before selection)
  - Keyboard: ↑↓ to highlight, Enter to select, Escape to close
  - `onOutsideClick` via document `mousedown` listener
  - Filtered by `parentSearch` text, excludes self in edit mode
- Submit: builds FormData, appends `_method=PUT` for updates
- Emits: `saved`, `close`

---

## Known Issues Fixed

| Issue | Fix |
|-------|-----|
| POST 419 CSRF error | Removed `$middleware->statefulApi()` from `bootstrap/app.php` |
| SVG lines not drawing | Switched from event registry to `data-person-id` + `querySelector` |
| `death_date` validation error | Changed from `after_or_equal:birth_date` to `nullable\|date` |
| Dark mode not working in Tailwind v4 | Added `@custom-variant dark (&:where(.dark, .dark *))` in `style.css` |
| `@apply` error in build | Removed `<style scoped>` with `@apply`, inlined classes in template |
| Canvas lines wrong position in RTL | Changed `start-0` → `left-0` on `canvasRef` and SVG element |
| Person click not opening modal | Added `@mousedown.stop` on `.person-card` div in PersonCard |
| Navigator tab not centering tree | `onSelectRoot` now calculates pan offset from stored tree position |
| Navigator showing other family members | Added `selectedRootId` to v-for key to force TreeNode remount |
| Parent dropdown clipped by overflow | Moved dropdown to `<Teleport to="body">` with `position: fixed` |

---

## Data Flow Summary

```
User Action
    │
    ▼
Component (PersonCard click / AddPersonModal submit / delete)
    │
    ▼
Pinia Store (people.js)
    │  addPerson / updatePerson / deletePerson / fetchTrees / fetchAllPeople
    ▼
Axios → Laravel API (/api/people, /api/tree)
    │
    ▼
PersonController → Person Model → MySQL
    │
    ▼ (response)
store.trees / store.allPeople updated
    │
    ▼
FamilyTree re-renders visibleTrees
SVG lines redrawn via MutationObserver
```

---

## localStorage Keys

| Key                          | Value                                | Purpose                    |
|------------------------------|--------------------------------------|----------------------------|
| `theme`                      | `'dark'` or `'light'`                | Theme persistence          |
| `locale`                     | `'ar'` or `'en'`                     | Language persistence       |
| `familytree_root_positions`  | `{ [personId]: { x, y } }`          | Root tree drag positions   |
