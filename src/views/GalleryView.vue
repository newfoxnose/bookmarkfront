<script>
import { defineComponent, ref,onMounted,getCurrentInstance } from "vue";
import { useRouter } from "vue-router";

export default defineComponent({
  setup() {
    const iconLoading = ref(false);
    const router = useRouter();
    const { proxy } = getCurrentInstance()
    const defaultPercent = ref(5);
    const loadingdone = ref(false);
    const folder_list = ref([])
    const currentpage=ref(1)
    const pagesize=ref(1)
    const total=ref(1)
    const items = ref([])
    // ajax 异步获取内容
    onMounted(() => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      proxy.$http.post("/ajax/get_folder_ajax/", params).then((res) => {
        if (res.data.code == "401") {
          //不在登陆状态
          window.location.href = "/login";
        }
        folder_list.value = res.data.data.data;
      });
      handlepagechange(1);
    });
    const handlepagechange = (values) => {
      const interval=setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random()*7+2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value>90){
          clearInterval(interval);
        }
      }, 100)

      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      
      proxy.$http.post("/ajax/home_page_ajax/"+currentpage.value, params).then((res) => {
        //console.log(res.data);
        items.value = res.data.data.post;
        pagesize.value = res.data.data.pagesize;
        total.value = res.data.data.total;
        defaultPercent.value = 100;
        loadingdone.value = true
      });
    };
    const deletepost= (id,page) => {
      console.log(id)
      console.log(page)
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("id", id);
      params.append("page", page);
      proxy.$http.post("/ajax/delete_imgpost_ajax/", params).then((res) => {
        //console.log(res.data);
        items.value = res.data.data.post;
        pagesize.value = res.data.data.pagesize;
        total.value = res.data.data.total;
        defaultPercent.value = 100;
        loadingdone.value = true
      });
    };
    return {
      deletepost,
      folder_list,
      items,
      defaultPercent,
      iconLoading,
      loadingdone,
      currentpage,
      pagesize,
      total,
      handlepagechange
    };
  }
});
</script>
 
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
  <a-image-preview-group>
    <div class="img-div" v-for="item in items" :key="item.label" :id="item.id"
      :folder_id="item.folder_id"
      :title="item.title"
      :is_private="item.is_private"
      :is_recommend="item.is_recommend" >
      <p>{{item.title}}</p>

    <p><a-image v-for="img in item.img_arr" :key="img.label" :width="200" :src="img"/>
    <br>
    <RouterLink :to="/editimg/+item.id">编辑</RouterLink>
    |
    <a @click="deletepost(item.id,currentpage)">删除</a>
  </p>
    </div>
  </a-image-preview-group>
  <div style="clear:both;">
  <a-pagination v-model:current="currentpage" v-model:pageSize="pagesize" :total="total" @change="handlepagechange" show-less-items />
  </div>
</template>

<style scoped>

.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -10;
}

.img-div{
  float:left;
}

</style>
