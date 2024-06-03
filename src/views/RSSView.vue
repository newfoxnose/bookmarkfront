<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress
      type="circle"
      :percent="defaultPercent"
      status="active"
      :show-info="false"
      :stroke-color="{
        '0%': '#108ee9',
        '100%': '#87d068',
      }"
    />
  </div>

  <h3 style="margin-top: 15px">{{ title }}</h3>
  <div>
    <div v-for=" item in feedItems" style="margin-bottom:5px;">
      <a :href=" item.link " style="margin-left:5px;"  class="ext" target="_blank">
        {{ item.title }}
      </a>
      <div v-if="JSON.stringify(item.description)!== '{}'" v-html="item.description"></div>
    </div>
  </div>

</template>
<style scoped>
.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}
</style>
<script>
import { message } from "ant-design-vue";
import { onMounted, getCurrentInstance, ref } from "vue";
import { useRouter } from 'vue-router'
export default {
  setup() {
    const iconLoading = ref(false);
    const router = useRouter()
    const { proxy } = getCurrentInstance();
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const title = ref('');
    const feedItems = ref([]);

    onMounted(() => {
      console.log("mounted");
      const interval = setInterval(() => {
        const percent =
          defaultPercent.value + Math.round(Math.random() * 7 + 2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value > 90) {
          clearInterval(interval);
        }
      }, 100);
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("feed_id", router.currentRoute.value.params.id);
      proxy.$http.post("/ajax/rss_ajax/", params).then((res) => {
        if (res.data.code == "401") {
          //不在登陆状态
          window.location.href = "/login";
        }
        defaultPercent.value = 100;
        loadingdone.value = true;
        title.value = res.data.data.xml_json.channel.title;
        feedItems.value = res.data.data.xml_json.channel.item;
      });
    });

    return {
      title,
      feedItems,
      iconLoading,
      defaultPercent,
      loadingdone,
    };
  },
};
</script>
