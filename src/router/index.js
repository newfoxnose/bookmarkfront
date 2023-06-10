import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/login',
      name: 'login',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/logout',
      name: 'logout',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../components/logout.vue')
    },
    {
      path: '/user',
      name: 'user',
      component: () => import('../views/UserView.vue')
    },
    {
      path: '/manage',
      name: 'manage',
      component: () => import('../views/ManageView.vue')
    },
    {
      path: '/upload',
      name: 'upload',
      component: () => import('../views/UploadView.vue')
    },
    {
      path: '/reg',
      name: 'reg',
      component: () => import('../views/RegView.vue')
    },
    {
      path: '/export',
      name: 'export',
      component: () => import('../components/export.vue')
    },
    {
      path: '/note',
      name: 'note',
      component: () => import('../views/NoteView.vue')
    },
    {
      path: '/qiniu',
      name: 'qiniu',
      component: () => import('../views/QiniuView.vue')
    }
    ,
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/ProfileView.vue')
    }
    ,
    {
      path: '/email',
      name: 'email',
      component: () => import('../components/email.vue')
    }
  ],
})
router.onError((error, to) => {
  if (error.message.includes('Failed to fetch dynamically imported module')) {
    window.location = to.fullPath
  }
})

export default router
