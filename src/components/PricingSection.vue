<template>
  <v-sheet
    id="pricing"
    class="d-flex align-center py-16"
    color="secondary"
  >
    <v-container>
      <v-row class="d-flex align-center">
        <v-col cols="12" md="5">
          <div class="text-h4 font-weight-bold">
            完全免费
          </div>

          <div class="text-body-1 text-medium-emphasis mt-5">
            所有基础功能完全免费使用，无需付费即可享受书签管理、Markdown笔记、RSS阅读、文件中转等便捷服务。
          </div>
        </v-col>

        <v-col cols="12" md="3">
          <v-card
            class="pt-10 pb-5 d-flex flex-column align-center justify-center ga-5"
            elevation="5"
          >
            <div class="text-h6 font-weight-bold">
              基础版
            </div>

            <div class="text-h2 font-weight-bold">
              免费
            </div>

            <div class="text-body-1 text-medium-emphasis">
              永久使用
            </div>

            <v-btn
              class="text-body-1 font-weight-bold"
              color="surface-variant"
              flat
              rounded
              text="开始使用"
            />

            <div class="text-center text-body-1 text-medium-emphasis">
              书签管理<br>
              Markdown笔记<br>
              RSS阅读
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card
            class="pt-10 pb-5 d-flex flex-column align-center justify-center ga-10"
            color="primary"
            elevation="5"
          >
            <div class="text-h6 font-weight-bold">
              进阶版
            </div>

            <div class="text-h2 font-weight-bold">
              免费
            </div>

            <div class="text-body-1 text-white">
              永久使用
            </div>

            <v-btn
              class="px-10 text-body-1"
              color="accent"
              flat
              height="55"
              rounded
              text="立即体验"
            />

            <div class="text-center text-body-1">
              全部基础功能<br>
              文件中转<br>
              卡片记忆学习
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>
