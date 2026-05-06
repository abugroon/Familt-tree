import axios from 'axios'

export const api = axios.create({ baseURL: import.meta.env.VITE_API_URL })

api.interceptors.request.use(cfg => {
  const t = localStorage.getItem('auth_token')

  if (t) {
    cfg.headers.Authorization = `Bearer ${t}`
  }

  cfg.headers.Accept = 'application/json'

  return cfg
})

export default api
