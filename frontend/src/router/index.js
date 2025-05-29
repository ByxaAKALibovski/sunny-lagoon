import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import HomeLayout from '../layouts/HomeLayout.vue'
import DefaultLayout from '../layouts/DefaultLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: HomeLayout,
      children: [
        {
          path: '',
          name: 'home',
          component: HomeView,
        }
      ]
    },
    {
      path: '/',
      component: DefaultLayout,
      children: [
        {
          path: 'about',
          name: 'about',
          component: () => import('../views/AboutView.vue'),
        },
        {
          path: 'category',
          name: 'category',
          component: () => import('../views/CategoryView.vue'),
        },
        {
          path: 'house',
          name: 'house',
          component: () => import('../views/HouseView.vue'),
        },
        {
          path: 'contact',
          name: 'contact',
          component: () => import('../views/ContactView.vue'),
        },
        {
          path: 'gaiety',
          name: 'gaiety',
          component: () => import('../views/GaietyView.vue'),
        },
        {
          path: 'promotion',
          name: 'promotion',
          component: () => import('../views/PromotionView.vue'),
        },
        {
          path: 'reviews',
          name: 'reviews',
          component: () => import('../views/ReviewsView.vue'),
        },
      ]
    }
  ],
})

export default router
