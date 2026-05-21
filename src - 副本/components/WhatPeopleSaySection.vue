<template>
  <v-sheet
    id="2"
    class="py-16"
    color="secondary"
  >
    <v-container>
      <v-row align="center">
        <v-col cols="12" md="6">
          <div class="text-h4 font-weight-medium">
            用户反馈与评价
          </div>

          <div class="text-body-1 text-medium-emphasis mt-5">
            我们来自全球用户的真实反馈， 展现全能书签网的实力与效果。
          </div>

          <v-row class="mt-5">
            <div
              v-for="(item, index) in ['TrustPilot', 'Feefo']"
              :key="item"
            >
              <v-alert
                :border="index === 1 ? 'start' : undefined"
                class="pt-3 rounded-0"
                color="secondary"
              >
                <div class="font-weight-bold">
                  {{ item }}
                </div>

                <div>
                  <v-rating
                    color="yellow-darken-2"
                    density="compact"
                    model-value="5"
                  />
                </div>
              </v-alert>
            </div>
          </v-row>
        </v-col>

        <v-col cols="12" md="6">
          <v-alert
            v-for="person in feedback"
            :key="person.name"
            border="start"
            border-color="accent"
            class="my-5 border-opacity-100"
            color="white"
            elevation="5"
            rounded
          >
            <div class="d-flex flex-row align-center ga-3">
              <div class="font-weight-normal">
                {{ person.feedback }}  —— 
                <span class="text-primary">{{ person.name }}</span>
              </div>
            </div>
          </v-alert>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const feedback = [
  {
      name: '周大强',
      feedback:
        '再也不用为多个浏览器的书签同步问题担心！ ',
    },
    {
      name: '王佳欣',
      feedback:
        '使用笔记功能， 随时记录课堂灵感， 很赞！ ',
    },
   
    {
      name: '张伟',
      feedback:
        '公司和家里同时登陆，非常方便！',
    },
  ]
</script>
