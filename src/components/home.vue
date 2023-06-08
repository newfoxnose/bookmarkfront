
<script>
import { ref } from 'vue'
export default {
  setup(){
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
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
    const interval=setInterval(() => {
        this.defaultPercent = (this.defaultPercent+10) > 95 ? 95 : this.defaultPercent;
        if (this.defaultPercent>90){
          clearInterval(interval);
        }
      }, 1500)
    const { data: res } = await this.$http.get('/ajax/index_ajax')
    this.items = res.data
    this.defaultPercent = 95;
    this.loadingdone=true
  },
}

</script>
<template>
<div class="loadingbar" v-show="loadingdone==false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
        '0%': '#108ee9',
        '100%': '#87d068',
      }"/>
  </div>

   <h3 style="margin-top:15px;">随机公开书签</h3>
   <div v-for="(item, index) in items.root_bookmarks" class="item">
    <img :src="item.icon_display" style="width:16px;height:16px;margin-right:3px;">
    <a :href="item.url" :title="item.title" target="_blank">
      {{ item.short_title }}
    </a>
    <p v-if="index==12||index==13||index==14||index==27||index==28||index==29" class="line"></p>
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

.line{
  border-bottom-style:dashed;
  border-bottom-width:thin;
  margin-bottom:0 !important;
  margin-top:15px !important;
}

.loadingbar {
  position:fixed;
  top:50%;
left:50%;
transform: translate(-50%,-50%);
  z-index: 10;
}
</style>