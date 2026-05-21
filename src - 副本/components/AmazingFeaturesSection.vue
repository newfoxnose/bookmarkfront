<template>
  <v-sheet id="features" class="text-center py-16">
    <div class="text-h4 font-weight-medium">
      插件扩展
    </div>

    <div class="text-body-1 text-medium-emphasis my-5">
      通过可启用插件支持无限功能
    </div>

    <v-container>
      <v-row>
        <v-col
          v-for="item in features"
          :key="item.title"
          class="d-flex ga-5 pa-5 text-left"
          cols="12"
          md="4"
        >
          <div class="mt-2">
            <v-img :src="item.icon" width="40" />
          </div>

          <div>
            <div class="text-h6 font-weight-bold">
              {{ item.title }}
            </div>

            <div class="text-body-1 text-medium-emphasis mt-2">
              {{ item.description }}
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const features = [
  {
      icon: '/assets/icons/netdisk.png',
      title: '文件中转',
      description:
        '方便上传、下载、分享文件，随时随地访问文件，支持常见格式文件的在线预览',
    },
  {
      icon: '/assets/icons/rss.png',
      title: 'RSS阅读器',
      description:
        '即时更新、自定义订阅、简洁界面，集中获取资讯， 轻松追踪喜欢的内容， 节省浏览时间',
    },
    {
      icon: '/assets/icons/ocr.png',
      title: 'OCR文字识别',
      description:
        '自动识别并提取图片中的文字，支持中文、英文、法语、阿拉伯语、日语、韩语等十余种语言',
    },
    {
      icon: '/assets/icons/dict.png',
      title: '词典大全',
      description:
        '包括新华字典、辞海、英汉词典、汉英词典、日和词典、和日词典',
    },
    {
      icon: '/assets/icons/cards.png',
      title: '卡片记忆学习法',
      description:
        '简单高效，支持各种适合对照记忆的学习内容',
    },
    {
      icon: '/assets/icons/more.png',
      title: '更多插件在路上',
      description:
        '。。。。。。',
    },
  ]
</script>
