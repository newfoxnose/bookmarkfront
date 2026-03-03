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
      meta: { keepAlive: true, title: "书签" },
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
      meta: { keepAlive: true, title: "管理目录" },
      component: () => import("../views/ManageView.vue"),
    },
    {
      path: "/upload",
      name: "upload",
      meta: { keepAlive: true, title: "导入书签" },
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
      meta: { keepAlive: true, title: "导出书签至本地" },
      component: () => import("../components/export.vue"),
    },
    {
      path: "/note",
      name: "note",
      meta: { keepAlive: true, title: "随手记" },
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
      meta: { keepAlive: true, title: "文件中转" },
      component: () => import("../views/FileView.vue"),
    },
    {
      path: "/profile",
      name: "profile",
      meta: { keepAlive: true, title: "个人设置" },
      component: () => import("../views/ProfileView.vue"),
    },
    {
      path: "/email",
      name: "email",
      meta: { keepAlive: true, title: "发送书签至邮箱" },
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
      meta: { keepAlive: true, title: "笔记" },
      component: () => import("../views/BlogView.vue"),
    },{
      path: "/chat",
      name: "chat",
      meta: { keepAlive: true, title: "CHATGPT" },
      component: () => import("../views/ChatView.vue"),
    },{
      path: "/calendar",
      name: "calendar",
      meta: { keepAlive: true, title: "日历" },
      component: () => import("../views/CalendarView.vue"),
    },{
      path: "/rss",
      name: "rss",
      meta: { keepAlive: true, title: "RSS阅读器" },
      component: () => import("../views/RSSView.vue"),
    },{
      path: "/frame/:id",
      name: "frame",
      meta: { keepAlive: true, title: "外部链接" },
      component: () => import("../views/FrameView.vue"),
    },{
      path: "/monitoring",
      name: "monitoring",
      meta: { keepAlive: true, title: "监控网站状态" },
      component: () => import("../views/MonitoringView.vue"),
    },{
      path: "/fetch",
      name: "fetch",
      meta: { keepAlive: true, title: "数据抓取" },
      component: () => import("../views/FetchView.vue"),
      },{
        path: "/pwdmemo",
        name: "pwdmemo",
        meta: { keepAlive: true, title: "密码管理" },
        component: () => import("../views/PwdMemoView.vue"),
      },{
        path: "/hot",
        name: "hot",
        meta: { keepAlive: true, title: "热榜" },
        component: () => import("../views/HotView.vue"),
      },{
        path: "/txtreader",
        name: "txtreader",
        meta: { keepAlive: true, title: "TXT电子书" },
        component: () => import("../views/TxtReaderView.vue"),
      },{
        path: "/dict",
        name: "dict",
        meta: { keepAlive: true, title: "词典管理" },
        component: () => import("../views/DictView.vue"),
      },
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
