import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomeView,
    },
    {
      path: "/login",
      name: "login",
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import("../views/LoginView.vue"),
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
      path: "/editpost/:id",
      name: "editpost",
      component: () => import("../views/EditpostView.vue"),
    },
    {
      path: "/newblog",
      name: "newblog",
      component: () => import("../views/NewblogView.vue"),
      //meta: { "composetype": "diary" }   //这是另一种传参方法，备用
    },
    {
      path: "/newdiary",
      name: "newdiary",
      component: () => import("../views/NewdiaryView.vue"),
      //meta: { "composetype": "diary" }   //这是另一种传参方法，备用
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
    },
    {
      path: "/diary",
      name: "diary",
      component: () => import("../views/DiaryView.vue"),
    },
    {
      path: "/editblog/:id",
      name: "editblog",
      component: () => import("../views/EditblogView.vue"),
    },
    {
      path: "/editdiary/:id",
      name: "editdiary",
      component: () => import("../views/EditdiaryView.vue"),
    },
    {
      path: "/collection",
      name: "collection",
      component: () => import("../views/CollectionView.vue"),
    },
    {
      path: "/gallery",
      name: "gallery",
      component: () => import("../views/GalleryView.vue"),
    },
    {
      path: "/editimg/:id",
      name: "editimg",
      component: () => import("../views/EditimgView.vue"),
    },
    {
      path: "/newimg",
      name: "newimg",
      component: () => import("../views/NewimgView.vue"),
    },{
      path: "/chat",
      name: "chat",
      component: () => import("../views/ChatView.vue"),
    },{
      path: "/feed",
      name: "feed",
      component: () => import("../views/FeedView.vue"),
    },{
      path: "/rss/:id",
      name: "rss",
      component: () => import("../views/RSSView.vue"),
    },{
      path: "/test",
      name: "test",
      component: () => import("../views/TestView.vue"),
    },
  ],
});

//这里是为了避免出现Failed to fetch dynamically imported module错误打不开网页
router.onError((error, to) => {
  if (error.message.includes("Failed to fetch dynamically imported module")) {
    //window.location = to.fullPath
  }
});

export default router;
