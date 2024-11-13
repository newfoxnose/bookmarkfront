<template>
  <v-sheet
    class="text-center pa-16"
    color="accent"
  >
    <div class="text-h3 font-weight-bold mb-5 mb-12">
      日历和待办
    </div>

    <v-container>
      <v-row align="center" class="mx-15">
        <v-col
          v-for="item in agency"
          :key="item.title"
          cols="12"
          md="3"
          sm="6"
        >
          <v-img height="56" :src="item.icon" />

          <div class="text-h4 font-weight-bold mt-5">
            {{ item.title }}
          </div>

          <div class="text-body-1 mt-5">
            {{ item.description }}
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const agency = [
    {
      icon: '/assets/agency/completed.png',
      title: '1050+',
      description: '待办事项标记',
    },
    {
      icon: '/assets/agency/heart.png',
      title: '217k',
      description: '设定重复任务',
    },
    {
      icon: '/assets/agency/awards.png',
      title: '1210',
      description: '同步手机日历',
    },
    {
      icon: '/assets/agency/anchor.png',
      title: '2137',
      description: '高效管理',
    },
  ]
</script>
