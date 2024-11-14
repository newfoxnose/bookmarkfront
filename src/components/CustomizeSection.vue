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
                <img :src="item.icon" width="75px" height="75px">
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
          <v-img src="/assets/customize_bg.png" style="border-radius: 10px;"/>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const customize = [
  {
      icon: './assets/icons/chatgpt.png',
      title: 'ChatGPT集成',
      subtitle:
        '随时问答，提供智能化建议和辅助，提升工作效率。',
    },
    {
      icon: './assets/icons/todo.png',
      title: '日历和待办',
      subtitle:
        '待办事项标记，设定重复任务，高效管理时间。',
    },
    {
      icon: './assets/icons/notepad.png',
      title: '记事本',
      subtitle:
        '随时记录灵感，富文本编辑器，快捷键保存。',
    },
  ]; </script>
