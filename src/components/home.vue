
<script>
export default {
  data() {
    return {
      items: {},
    }
  },
  async mounted() {
    const { data: res } = await this.$http.get('/ajax/index_ajax')
    console.log(res.data)
    this.items = res.data
  },
}

</script>
<template>
   <h3 style="margin-top:15px;">随机公开书签</h3>
   <div v-for="item in items.root_bookmarks" class="item">
    <img :src="item.icon_display" style="width:16px;height:16px;margin-right:3px;">
    <a :href="item.url" :title="item.title" target="_blank">
      {{ item.short_title }}
    </a>
  </div>

  <div style="margin-bottom:20px;">
  </div>
</template>


<style scoped>
.item {
  margin-top: 2rem;
  width: 300px;
  display: inline-block;
}


@media (min-width: 1024px) {
  .item {
    margin-top: 0;
    padding: 0.4rem 0 1rem 0;
  }
}
</style>
