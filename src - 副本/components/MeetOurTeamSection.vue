<template>
  <v-sheet
    class="text-center py-16"
    color="secondary"
    min-height="800"
  >
    <div class="text-h4 font-weight-bold">
      笔记 
    </div>

    <div class="text-body-1 text-medium-emphasis mt-5">
    记录一切
    </div>

    <v-container>
      <v-row>
        <v-col
          v-for="person in team"
          :key="person.name"
          class="pa-6"
          cols="12"
          md="4"
        >
          <v-card
            class="bg-white rounded-t-xl pb-6"
            flat
          >
            <v-img
              class="w-80 rounded-t-xl"
              :src="person.image"
            />

            <div class="mt-3 text-body-1">
              {{ person.name }}
            </div>

            <div class="mt-5 text-medium-emphasis mx-5">
              {{ person.description }}
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>

<script setup>
  const team = [
    {
      image: './assets/team/person1.png',
      name: '高效记录',
      title: 'Head of SEO',
      description:
        '保留源格式粘贴，自动上传图片到服务器',
    },
    {
      image: './assets/team/person2.png',
      name: '私密性',
      title: 'Head of SEO',
      description:
        '查看私有笔记需要再次输入密码',
    },
    {
      image: './assets/team/person3.png',
      name: '共享精神',
      title: 'Head of SEO',
      description:
        '设置为公开的笔记可自动生成博客网站',
    },
  ]
</script>
