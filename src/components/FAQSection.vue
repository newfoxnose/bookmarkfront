<template>
  <v-sheet
    class="text-center"
    id="3"
    min-height="700"
  >
    <div class="text-h4 font-weight-medium pt-15 ">
      常见问题
    </div>

    <div class="text-body-1 text-medium-emphasis mt-5">
      在使用全能书签网之前， 您可能会有一些疑虑， 我们为您解答常见的问题。
    </div>

    <v-container>
      <v-row class="pa-10 text-left">
        <v-col
          v-for="n in faq"
          :key="n"
          class="pa-10"
          cols="12"
          md="6"
        >
          <div class="text-h6 font-weight-bold text-blue-grey-darken-2">
            {{n.q}}
          </div>

          <div class="text-body-1 text-medium-emphasis mt-5">
            {{n.a}}
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>
<script setup>
  const faq = [
    {
      q: '什么是全能书签网？',
      a: '全能书签网是一个综合性的网络平台，提供书签管理、Markdown笔记、RSS阅读、文件中转、卡片记忆学习等功能。',
    },
    {
      q: '我如何使用书签功能？',
      a: '您可以通过简单的点击操作来导入、导出、添加、删除和编辑您的书签，支持多级分类和标签管理。',
    },
    {
      q: '文件中转服务安全么？',
      a: '文件中转连接七牛云平台，确保数据安全，支持常见格式文件的在线预览。',
    },
    {
      q: 'Markdown笔记支持哪些功能？',
      a: '支持实时预览、代码高亮、标签分类、私有笔记保护，多种编辑器主题和夜间模式。',
    },
    {
      q: 'RSS阅读器怎么使用？',
      a: '只需添加您感兴趣的RSS源，系统将自动获取最新内容，集中获取资讯，轻松追踪喜欢的内容。',
    },
    {
      q: '卡片记忆学习是什么？',
      a: '简单高效的间隔重复记忆工具，支持各种适合对照记忆的学习内容。',
    },
    {
      q: '这个网站免费么？',
      a: '是的，所有基础功能都是免费的，您可以享受多项便捷服务。',
    },
    {
      q: '如何注册全能书签网？',
      a: '只需填写邮箱和密码信息，即可快速创建账户并开始使用。',
    },
  ]
</script>
