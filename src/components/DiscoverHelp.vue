<template>
  <v-sheet class="py-16">
    <v-container>
      <v-row>
        <v-col cols="12" md="5">
          <v-img src="/assets/discover_bg.png" /></v-col>

        <v-col
          cols="12"
          md="7"
        >
          <div class="text-h4">
            集成记事本
          </div>

          <div class="text-body-1 text-medium-emphasis my-5">
            随时记录灵感， 保留重要笔记， 并支持格式化文本。
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
    '富文本编辑器',
    '快捷键保存',
    '多端共享',
  ]
</script>
