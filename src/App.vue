<script>
import dayjs from "dayjs";
import "dayjs/locale/zh-cn";
dayjs.locale("zh-cn");
import { RouterLink, RouterView } from "vue-router";
import { onMounted, defineComponent, ref, reactive, toRefs } from "vue";
import {
  StarOutlined,
  FormOutlined,
  DatabaseOutlined,
  CommentOutlined,
  ProfileOutlined,
  WifiOutlined,
  MoreOutlined,
  UserAddOutlined,
  LoginOutlined,CalendarOutlined,
} from "@ant-design/icons-vue";

export default defineComponent({
  components: {
    StarOutlined,
    FormOutlined,
    DatabaseOutlined,
    CommentOutlined,
    ProfileOutlined,
    WifiOutlined,
    MoreOutlined,
    UserAddOutlined,
    LoginOutlined,CalendarOutlined
  },
  data() {
    return {
      collapsed: ref(false),
      selectedKeys: ref([]),
    };
  },
  setup() {
    const logourl = ref("../images/logo-white.png");
    const contenttheme = ref("content-dark-theme");
    const footertheme = ref("footer-dark-theme");
    const state = reactive({
      theme: $cookies.get("theme"),
      selectedKeys: [$cookies.get("selectedkey")],
      openKeys: [$cookies.get("openkey")],
    });
    onMounted(() => {
      if (state.theme == "dark") {
        changeTheme(true);
      } else {
        changeTheme(false);
      }
    });
    const changeTheme = (checked) => {
      state.theme = checked ? "dark" : "light";
      if (state.theme == "dark") {
        $cookies.set("theme", "dark", "720h");
        logourl.value = "../images/logo-white.png";
        contenttheme.value = "content-dark-theme";
        footertheme.value = "footer-dark-theme";
      } else {
        $cookies.set("theme", "light", "720h");
        logourl.value = "../images/logo.png";
        contenttheme.value = "content-light-theme";
        footertheme.value = "footer-light-theme";
      }
    };
    return {
      ...toRefs(state),
      changeTheme,
      logourl,
      contenttheme,
      footertheme,
    };
  },
});
</script>

<template>
  <a-layout style="min-height: 100vh">
    <a-layout-sider v-model:collapsed="collapsed" collapsible :theme="theme">
      <div class="logo" :theme="theme">
        <img :src="logourl" height="30" />
      </div>
      <a-menu
        v-model:selectedKeys="selectedKeys"
        mode="inline"
        :theme="theme"
        v-model:openKeys="openKeys"
      >
        <span
          v-if="$cookies.get('token') != null && $cookies.get('token') != ''"
        >
          <a-menu-item key="1">
            <star-outlined />
            <RouterLink to="/home" style="padding-left: 8px">书签</RouterLink>
          </a-menu-item>
          <a-menu-item key="2">
            <form-outlined />
            <RouterLink to="/note" style="padding-left: 8px">随手记</RouterLink>
          </a-menu-item>
          <a-menu-item key="3">
            <database-outlined />
            <RouterLink to="/qiniu" style="padding-left: 8px">文件</RouterLink>
          </a-menu-item>
          <a-menu-item key="4">
            <comment-outlined />
            <RouterLink to="/chat" style="padding-left: 8px"
              >CHATGPT</RouterLink
            >
          </a-menu-item>
          <a-menu-item key="5">
            <profile-outlined />
            <RouterLink to="/blog" style="padding-left: 8px">笔记</RouterLink>
          </a-menu-item>
          <a-menu-item key="6">
            <wifi-outlined />
            <RouterLink to="/feed" style="padding-left: 8px">RSS</RouterLink>
          </a-menu-item>
          <a-menu-item key="17">
            <calendar-outlined />
            <RouterLink to="/calendar" style="padding-left: 8px">日历</RouterLink>
          </a-menu-item>
          <a-sub-menu key="sub1">
            <template #title>
              <span>
                <more-outlined />
                <span>更多</span>
              </span>
            </template>
            <a-menu-item key="8">
              <RouterLink to="/manage">管理目录</RouterLink>
            </a-menu-item>
            <a-menu-item key="9">
              <RouterLink to="/profile">个人设置</RouterLink>
            </a-menu-item>
            <a-menu-item key="10">
              <RouterLink to="/clear">清理七牛云无用图片</RouterLink>
            </a-menu-item>
            <a-menu-item key="11">
              <RouterLink to="/upload">导入书签</RouterLink>
            </a-menu-item>
            <a-menu-item key="12">
              <RouterLink to="/export">导出书签至本地</RouterLink>
            </a-menu-item>
            <a-menu-item key="13">
              <RouterLink to="/email">发送书签至邮箱</RouterLink>
            </a-menu-item>
            <a-menu-item key="14">
              <RouterLink to="/logout">退出</RouterLink>
            </a-menu-item>
          </a-sub-menu>
        </span>
        <span v-else>
          <a-menu-item key="15">
            <login-outlined />
            <RouterLink to="/" style="padding-left: 8px">登入</RouterLink>
          </a-menu-item>
          <a-menu-item key="16">
            <user-add-outlined />
            <RouterLink to="/reg" style="padding-left: 8px">注册</RouterLink>
          </a-menu-item>
        </span>
      </a-menu>
      <div style="text-align: center; padding-top: 30px">
        <a-switch
          :checked="theme == 'dark'"
          checked-children="Dark"
          un-checked-children="Light"
          @change="changeTheme"
        />
      </div>
    </a-layout-sider>
    <a-layout>
      <a-layout-content :class="contenttheme">
        <p></p>
        <RouterView />
      </a-layout-content>
      <a-layout-footer :class="footertheme"> ©2024 </a-layout-footer>
    </a-layout>
  </a-layout>
</template>
