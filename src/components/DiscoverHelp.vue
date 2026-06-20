<template>
  <v-sheet class="py-16">
    <v-container>
      <v-row>
        <v-col cols="12" md="5">
          <v-img src="/assets/discover_bg.png"  style="border-radius: 10px;" /></v-col>

        <v-col
          cols="12"
          md="7"
        >
          <div class="text-h4">
            笔记
          </div>

          <div class="text-body-1 text-medium-emphasis my-5">
            高效记录一切
          </div>

          <div
            v-for="text in discover"
            :key="text"
            class="text-medium-emphasis my-2 mb-4"
          >
            <v-icon
              color="accent"
              icon="mdi-check-bold"
              start
            />

            {{ text }}
          </div>

        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const discover = [
    '基于 Markdown 编辑，支持实时预览与代码高亮',
    '标签分类 + 快速搜索，轻松找到每一篇笔记',
    '可设置私有笔记，查看时需要再次输入密码',
    '多种编辑器主题，夜间模式轻松切换',
  ]
</script>
