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
    '保留源格式粘贴，自动上传图片到服务器',
    '可设置私有笔记，查看时需要再次输入密码',
    '自动生成博客网站（针对设置为发表的笔记）',
  ]
</script>
