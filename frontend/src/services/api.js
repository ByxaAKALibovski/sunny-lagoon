import axios from 'axios'

const API_URL = 'https://api.sunny-lagoon.ru/backend'

// Настройка axios с базовым URL
const api = axios.create({
  baseURL: API_URL
})

// Добавление токена в заголовки для защищенных запросов
const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// Категории
export const getCategories = () => {
  return api.get('/categories')
}

export const getCategoryById = (id) => {
  return api.get(`/categories/${id}`)
}

export const createCategory = (data) => {
  return api.post('/categories', data, { headers: getAuthHeader() })
}

export const updateCategory = (id, data) => {
  return api.put(`/categories/${id}`, data, { headers: getAuthHeader() })
}

export const deleteCategory = (id) => {
  return api.delete(`/categories/${id}`, { headers: getAuthHeader() })
}

// Дома
export const getHouses = () => {
  return api.get('/homes')
}

export const getHouseById = (id) => {
  return api.get(`/homes/${id}`)
}

export const createHouse = (data) => {
  return api.post('/homes', data, { headers: getAuthHeader() })
}

export const updateHouse = (id, data) => {
  return api.put(`/homes/${id}`, data, { headers: getAuthHeader() })
}

export const deleteHouse = (id) => {
  return api.delete(`/homes/${id}`, { headers: getAuthHeader() })
}

// Развлечения
export const getGaieties = () => {
  return api.get('/gaiety')
}

export const getGaietyById = (id) => {
  return api.get(`/gaiety/${id}`)
}

export const createGaiety = (data) => {
  return api.post('/gaiety', data, { headers: getAuthHeader() })
}

export const updateGaiety = (id, data) => {
  return api.put(`/gaiety/${id}`, data, { headers: getAuthHeader() })
}

export const deleteGaiety = (id) => {
  return api.delete(`/gaiety/${id}`, { headers: getAuthHeader() })
}

// Акции
export const getPromotions = () => {
  return api.get('/promotions')
}

export const getPromotionById = (id) => {
  return api.get(`/promotions/${id}`)
}

export const createPromotion = (data) => {
  return api.post('/promotions', data, { headers: getAuthHeader() })
}

export const updatePromotion = (id, data) => {
  return api.put(`/promotions/${id}`, data, { headers: getAuthHeader() })
}

export const deletePromotion = (id) => {
  return api.delete(`/promotions/${id}`, { headers: getAuthHeader() })
}

// Услуги
export const getServices = () => {
  return api.get('/services')
}

export const getServiceById = (id) => {
  return api.get(`/services/${id}`)
}

export const createService = (data) => {
  return api.post('/services', data, { headers: getAuthHeader() })
}

export const updateService = (id, data) => {
  return api.put(`/services/${id}`, data, { headers: getAuthHeader() })
}

export const deleteService = (id) => {
  return api.delete(`/services/${id}`, { headers: getAuthHeader() })
}

// Отзывы
export const getReviews = () => {
  return api.get('/reviews')
}

export const createReview = (data) => {
  return api.post('/reviews', data)
}