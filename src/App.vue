<script>
import { RouterLink, RouterView } from "vue-router";
import { defineComponent } from "vue";
import { DownOutlined } from "@ant-design/icons-vue";

export default defineComponent({
  components: {
    DownOutlined,
  },
});
</script>

<template>
  <div>
    <header>
      <img alt="Vue logo" class="logo" src="@/assets/logo.png" width="100" />

      <div class="wrapper">
        <nav>
          <span
            v-if="$cookies.get('token') != null && $cookies.get('token') != ''"
          >
            <RouterLink to="/">书签</RouterLink>
            <RouterLink to="/note">随手记</RouterLink>
            <RouterLink to="/qiniu">文件</RouterLink>
            <RouterLink to="/chat">ChatGPT</RouterLink>

            <a-dropdown style="display: inline !important">
              <a class="ant-dropdown-link" @click.prevent>
                博客
                <DownOutlined />
              </a>
              <template #overlay>
                <a-menu>
                  <a-menu-item>
                    <RouterLink to="/newblog">写博客</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/blog">博客列表</RouterLink>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>

            <a-dropdown style="display: inline !important">
              <a class="ant-dropdown-link" @click.prevent>
                日记
                <DownOutlined />
              </a>
              <template #overlay>
                <a-menu>
                  <a-menu-item>
                    <RouterLink to="/newdiary">写日记</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/diary">日记列表</RouterLink>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>

            <a-dropdown style="display: inline !important">
              <a class="ant-dropdown-link" @click.prevent>
                图片
                <DownOutlined />
              </a>
              <template #overlay>
                <a-menu>
                  <a-menu-item>
                    <RouterLink to="/newimg">新图片</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/gallery">图库</RouterLink>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>

            <a-dropdown style="display: inline !important">
              <a class="ant-dropdown-link" @click.prevent>
                更多
                <DownOutlined />
              </a>
              <template #overlay>
                <a-menu>
                  <a-menu-item>
                    <RouterLink to="/collection">文摘</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/manage">管理目录</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/profile">个人设置</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/domain">个人域名设置</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/clear">清理七牛云无用图片</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/upload">导入书签</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/export">导出书签至本地</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/email">发送书签至邮箱</RouterLink>
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/redirect" target="_blank"
                      >个人网站</RouterLink
                    >
                  </a-menu-item>
                  <a-menu-item>
                    <RouterLink to="/logout">退出</RouterLink>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </span>
          <span v-else>
            <RouterLink to="/login">登入</RouterLink>
            <RouterLink to="/reg">注册</RouterLink>
          </span>
        </nav>
      </div>
    </header>
  </div>
  <RouterView />
  <div class="footer">
      Copyright 2023-2024
    </div>
</template>

<style scoped>
.footer{
  padding:30px;
  text-align: center;
}

header {
  line-height: 1.5;
  max-height: 100vh;
  display: block;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

nav {
  font-size: 14px;
  text-align: center;
  margin-top: 2rem;
}

nav a.router-link-exact-active {
  color: var(--color-text);
}

nav a.router-link-exact-active:hover {
  background-color: transparent;
}

nav a {
  display: inline-block;
  padding: 0 1rem;
}

nav a:first-of-type {
  border: 0;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }

  nav {
    text-align: left;
    margin-left: -1rem;
    padding: 1rem 0;
    margin-top: 1rem;
  }

}
</style>
