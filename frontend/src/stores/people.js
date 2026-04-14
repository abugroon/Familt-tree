import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

const api = axios.create({ baseURL: '/api' })

export const usePeopleStore = defineStore('people', () => {
  const trees = ref([])
  const allPeople = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchTrees() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/tree')
      trees.value = res.data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchAllPeople() {
    const res = await api.get('/people')
    allPeople.value = res.data
  }

  async function addPerson(formData) {
    const res = await api.post('/people', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    await fetchTrees()
    await fetchAllPeople()
    return res.data
  }

  async function updatePerson(id, formData) {
    formData.append('_method', 'PUT')
    const res = await api.post(`/people/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
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

  return {
    trees,
    allPeople,
    loading,
    error,
    fetchTrees,
    fetchAllPeople,
    addPerson,
    updatePerson,
    deletePerson,
    getPerson,
  }
})
