<template>
  <v-sheet
    id="1"
    class="py-16 text-center"
    color="secondary"
  >
    <div class="text-h4 font-weight-bold">
      书签管理核心功能
    </div>

    <div class="text-body-1 font-weight-regular mt-3 text-medium-emphasis mb-12">
      书签管理是本网站的核心功能，它可以帮助用户在线管理自己的网址收藏，将各浏览器书签汇总，并提供便捷的搜索、分类、收藏、删除、编辑等功能。
    </div>

    <v-container>
      <v-row>
        <v-col
          v-for="item in whatWeDo"
          :key="item.title"
          class="d-flex flex-column align-center"
          cols="12"
          md="4"
        >
          <img :src="item.icon">

          <div class="text-h6 mt-1">
            {{ item.title }}
          </div>

          <div class="text-body-1 mt-2 text-medium-emphasis w-75">
            {{ item.description }}
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const whatWeDo = [
    {
      icon: './assets/what/analytics.png',
      title: '导入导出',
      description:
        '支持多种浏览器的导入导出功能，还可将导出数据直接发送至邮箱',
    },
    {
      icon: './assets/what/awareness.png',
      title: '私密性',
      description:
        '设置私有书签，重要书签输入密码才可查看',
    },
    {
      icon: './assets/what/design.png',
      title: '自动排查',
      description:
        '定期自动排查网址，过期链接显示提醒',
    },
  ]
</script>
