<template>
  <v-sheet
    class="py-16"
    color="secondary"
  >
    <v-container>
      <v-row align="center">
        <v-col cols="12" md="7">
          <v-list
            base-color="transparent"
            bg-color="transparent"
            class="px-2"
            lines="three"
            variant="flat"
          >
            <v-list-item
              v-for="(item, index) in customize"
              :key="item.title"
              class="ga-5"
              :class="{ 'bg-grey-lighten-5 elevation-5 rounded': index === 0 }"
            >
              <template #prepend>
                <img :src="item.icon">
              </template>

              <template #title>
                <span class="text-h6 font-weight-bold">{{ item.title }}</span>
              </template>

              <template #subtitle>
                <span class="text-body-1 text-grey-darken-3">{{ item.subtitle }}</span>
              </template>
            </v-list-item>
          </v-list>
        </v-col>

        <v-col cols="12" md="5">
          <v-img src="/assets/customize_bg.png" />
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const customize = [
    {
      icon: './assets/customize.png',
      title: '文件中转服务',
      subtitle:
        '方便上传、下载、分享文件，随时随地访问文件，支持常见格式文件的在线预览。',
    },
    {
      icon: './assets/onboarding.png',
      title: 'ChatGPT集成',
      subtitle:
        '随时问答， 提供智能化建议和辅助， 提升工作效率。',
    },
    {
      icon: './assets/retention.png',
      title: 'RSS阅读器',
      subtitle:
        '即时更新、自定义订阅、简洁界面，集中获取资讯， 轻松追踪喜欢的内容， 节省浏览时间。',
    },
  ]; </script>
