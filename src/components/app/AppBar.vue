<template>
  <v-app-bar>
    <template #title>
      <div class="d-inline-flex align-center text-h4 font-weight-bold my-5 text-primary">
        <v-icon icon="$vuetify" start />

        Landing Page
      </div>
    </template>

    <template v-if="$vuetify.display.mdAndUp" #append>
      <div class="hidden-sm-and-down">
        <v-btn
          v-for="(item,index) in menu"
          :key="item"
          class="text-body-1 blue-grey-darken-4"
          :href="`#${index}`"
          variant="text"
        >
          {{ item }}
        </v-btn>
      </div>
    </template>
  </v-app-bar>
</template>

<script setup>
  const menu = [
    'Home',
    '功能',
    'Testimonials',
    'Work',
    'Features',
    '登入/注册',
  ]
</script>
