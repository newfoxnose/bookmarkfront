import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      redirect: "/home"
    },
    {
      path: "/home",
      name: "home",
      component: () => import("../views/HomeView.vue"),
    },
    {
      path: "/logout",
      name: "logout",
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import("../components/logout.vue"),
    },
    {
      path: "/manage",
      name: "manage",
      component: () => import("../views/ManageView.vue"),
    },
    {
      path: "/upload",
      name: "upload",
      component: () => import("../views/UploadView.vue"),
    },
    {
      path: "/reg",
      name: "reg",
      component: () => import("../views/RegView.vue"),
    },
    {
      path: "/export",
      name: "export",
      component: () => import("../components/export.vue"),
    },
    {
      path: "/note",
      name: "note",
      component: () => import("../views/NoteView.vue"),
    },
    {
      path: "/qiniu",
      name: "qiniu",
      component: () => import("../views/QiniuView.vue"),
    },
    {
      path: "/file",
      name: "file",
      component: () => import("../views/FileView.vue"),
    },
    {
      path: "/profile",
      name: "profile",
      component: () => import("../views/ProfileView.vue"),
    },
    {
      path: "/email",
      name: "email",
      component: () => import("../components/email.vue"),
    },
    {
      path: "/domain",
      name: "domain",
      component: () => import("../views/DomainView.vue"),
    },
    {
      path: "/redirect",
      name: "redirect",
      component: () => import("../components/redirect.vue"),
    },
    {
      path: "/clear",
      name: "clear",
      component: () => import("../components/clear.vue"),
    },
    {
      path: "/blog",
      name: "blog",
      component: () => import("../views/BlogView.vue"),
      //meta: { "composetype": "diary" }   //这是另一种传参方法，备用
    },{
      path: "/chat",
      name: "chat",
      component: () => import("../views/ChatView.vue"),
    },{
      path: "/calendar",
      name: "calendar",
      component: () => import("../views/CalendarView.vue"),
    },{
      path: "/rss",
      name: "rss",
      component: () => import("../views/RSSView.vue"),
    },{
      path: "/frame/:id",
      name: "frame",
      component: () => import("../views/FrameView.vue"),
    },{
      path: "/monitoring",
      name: "monitoring",
      component: () => import("../views/MonitoringView.vue"),
    },{
      path: "/fetch",
      name: "fetch",
      component: () => import("../views/FetchView.vue"),
      },{
        path: "/pwdmemo",
        name: "pwdmemo",
        component: () => import("../views/PwdMemoView.vue"),
      },{
        path: "/coffee",
        name: "coffee",
        component: () => import("../views/CoffeeView.vue"),
      }
  ],
});

//这里是为了避免出现Failed to fetch dynamically imported module错误打不开网页
router.onError((error, to) => {
  if (error.message.includes("Failed to fetch dynamically imported module")) {
    window.location.reload();
  }
});

// 添加路由守卫以确保页面正确加载
router.beforeEach((to, from, next) => {
  // 确保页面完全加载
  if (document.readyState === 'complete') {
    next();
  } else {
    window.addEventListener('load', () => {
      next();
    });
  }
});

export default router;
