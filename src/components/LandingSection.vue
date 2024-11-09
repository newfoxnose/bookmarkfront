<template>
  <v-sheet
    id="0"
    class="d-flex justify-center align-center text-center flex-column"
    color="primary"
    min-height="600"
  >
    <div class="text-h3 font-weight-medium mb-5">
     书签网
    </div>

    <div class="text-h6 font-weight-light mb-5" style="max-width: 468px;">
      书签网为您提供一个简单、 高效的在线书签管理解决方案， 帮助您轻松管理和访问网络上的所有书签。
    </div>

    <div class="d-flex ga-5 flex-1-1-auto flex-wrap justify-center px-4">
      <v-btn
        :block="$vuetify.display.mdAndDown"
        color="accent"
        :href="`#5`"
        flat
        height="55"
        rounded
        text="开始使用"
        width="150"
      />
    </div>
  </v-sheet>
</template>
