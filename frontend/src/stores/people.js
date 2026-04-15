import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

const api = axios.create({ baseURL: 'https://familyapi.moawiaabugroon.com/api' })

// Attach auth token to every request
api.interceptors.request.use(cfg => {
  const t = localStorage.getItem('auth_token')
  if (t) cfg.headers.Authorization = `Bearer ${t}`
  return cfg
})

export const usePeopleStore = defineStore('people', () => {
  const trees     = ref([])
  const allPeople = ref([])
  const loading   = ref(false)
  const error     = ref(null)

  // Add-child modal state
  const showAddModal     = ref(false)
  const addChildParentId = ref(null)

  function openAddModal(parentId = null) {
    console.log('openAddModal123', parentId)
    addChildParentId.value = parentId
    showAddModal.value = true
  }
  function closeAddModal() {
    showAddModal.value = false
    addChildParentId.value = null
  }

  // ── People CRUD ────────────────────────────────────────────────────────────

  async function fetchTrees() {
    loading.value = true
    error.value   = null
    try {
      const res    = await api.get('/tree')
      trees.value  = res.data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchAllPeople() {
    const res       = await api.get('/people')
    allPeople.value = res.data
  }

  async function addPerson(formData) {
    const res = await api.post('/people', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    await fetchTrees()
    await fetchAllPeople()
    return res.data
  }

  async function updatePerson(id, formData) {
    formData.append('_method', 'PUT')
    const res = await api.post(`/people/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    await fetchTrees()
    await fetchAllPeople()
    return res.data
  }

  async function deletePerson(id) {
    await api.delete(`/people/${id}`)
    await fetchTrees()
    await fetchAllPeople()
  }

  async function getPerson(id) {
    const res = await api.get(`/people/${id}`)
    return res.data
  }

  // ── Marriage ───────────────────────────────────────────────────────────────

  async function addMarriage(personId, spouseId, marriageDate = null) {
    const res = await api.post('/marriages', {
      person_id:     personId,
      spouse_id:     spouseId,
      marriage_date: marriageDate || undefined,
    })
    await fetchTrees()
    await fetchAllPeople()
    return res.data
  }

  async function fetchMarriage(personId) {
    const res = await api.get(`/marriages/${personId}`)
    return res.data   // null if none
  }

  async function deleteMarriage(marriageId) {
    await api.delete(`/marriages/${marriageId}`)
    await fetchTrees()
    await fetchAllPeople()
  }

  // ── Relationship detection ─────────────────────────────────────────────────

  async function checkRelationship(personAId, personBId) {
    const res = await api.post('/relationships/check', {
      person_a_id: personAId,
      person_b_id: personBId,
    })
    return res.data
  }

  return {
    trees,
    allPeople,
    loading,
    error,
    showAddModal,
    addChildParentId,
    openAddModal,
    closeAddModal,
    fetchTrees,
    fetchAllPeople,
    addPerson,
    updatePerson,
    deletePerson,
    getPerson,
    addMarriage,
    fetchMarriage,
    deleteMarriage,
    checkRelationship,
  }
})
