
<script>
import { message } from 'ant-design-vue';
import { onMounted, ref } from 'vue'
export default {
  setup() {
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    onMounted(() => {
      const interval = setInterval(() => {
        const percent = defaultPercent.value + 10;
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value > 90) {
          clearInterval(interval);
        }
      }, 100)
    })
    return {
      defaultPercent,
      loadingdone
    };
  },
  data() {
    return {
      items: {},
    }
  },
  async mounted() {
    this.$http.get('/ajax/index_ajax').then(res => {
      this.items = res.data.data
      this.defaultPercent = 95;
      this.loadingdone = true
    }).catch(error => {
      // obj.error ? obj.error(error) : null;
      console.log(error);
      message.info("出错了，请刷新");      //其他几个页面也要参考这个写出错提示
    });
  },
}

</script>
<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
  </div>

  <h3 style="margin-top:15px;">随机公开书签</h3>
  <div v-for="(item, index) in items.root_bookmarks" class="item">
    <img :src="item.icon_display" style="width:16px;height:16px;margin-right:3px;">
    <a :href="item.url" :title="item.title" target="_blank">
      {{ item.short_title }}
    </a>
    <p v-if="index == 12 || index == 13 || index == 14 || index == 27 || index == 28 || index == 29" class="line"></p>
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

.line {
  border-bottom-style: dashed;
  border-bottom-width: thin;
  margin-bottom: 0 !important;
  margin-top: 15px !important;
}

.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}
</style>