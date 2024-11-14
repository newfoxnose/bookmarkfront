<template>
  <v-sheet
    id="0"
    class="d-flex justify-center align-center text-center flex-column"
    color="primary"
    min-height="600"
  >
    <div class="text-h3 font-weight-medium mb-5">
      全能书签网
    </div>

    <div class="text-h6 font-weight-light mb-5" style="max-width: 468px;">
      一站式服务，综合书签、 记事本、 笔记、 文件中转、 ChatGPT、 RSS阅读器、 日历、待办等功能， 全面提升你的在线体验。
    </div>

    <div class="d-flex ga-5 flex-1-1-auto flex-wrap justify-center px-4">
      <v-btn
        :block="$vuetify.display.mdAndDown"
        color="accent"
        :href="`#4`"
        flat
        height="55"
        rounded
        text="开始使用"
        width="150"
      />
    </div>
  </v-sheet>
</template>
