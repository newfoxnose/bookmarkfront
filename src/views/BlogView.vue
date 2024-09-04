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

  <h3 style="margin-top: 15px">笔记</h3>
  <div>
    <a-image-preview-group>
      <div v-for="item in fileitems" style="margin-bottom: 5px">
        <span class="ext">{{ item.id }}</span>
        <a :href="'/editblog/' + item.id" style="margin-left: 5px">
          {{ item.title }}
        </a>
        <eye-invisible-two-tone
          v-if="item.is_private == '1'"
          style="margin-left: 3px"
        />
        <like-two-tone
          v-if="item.is_recommend == '1'"
          style="margin-left: 3px"
        />
        <span style="margin-left: 20px">( {{ item.createtime }})</span>
        <a style="margin-left: 20px" @click="deletepost(item.id,currentpage)">删除</a>
        <br /><a-image
          v-for="img in item.img_arr"
          :key="img.label"
          :width="200"
          :src="img"
        />
      </div>
    </a-image-preview-group>
  </div>
  <div style="clear:both;">
  <a-pagination v-model:current="currentpage" v-model:pageSize="pagesize" :total="total" @change="handlepagechange" show-less-items />
  </div>
</template>
<style scoped>
.ext {
  text-align: center;
  display: inline-block;
  width: 40px;
  margin-right: 3px;
  color: coral;
  font-weight: bold;
  border-style: solid;
  border-width: thin;
  border-color: crimson;
  padding: 2px;
  margin: 3px;
}

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

import {
  InboxOutlined,
  EyeInvisibleTwoTone,
  LikeTwoTone,
} from "@ant-design/icons-vue";
import { onMounted, getCurrentInstance, defineComponent, ref } from "vue";
import * as qiniu from "qiniu-js";
import { Base64 } from "js-base64";

export default {
  components: {
    InboxOutlined,
    EyeInvisibleTwoTone,
    LikeTwoTone,
  },
  setup() {
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const currentpage = ref(1);
    const pagesize = ref(1);
    const total = ref(1);

    const { proxy } = getCurrentInstance();

    const file_key = ref("");
    const fileitems = ref([]);

    onMounted(() => {
      handlepagechange(1);
    });
    const handlepagechange = (values) => {
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

      proxy.$http
        .post("/ajax/list_blog_ajax/" + currentpage.value, params)
        .then((res) => {
          //console.log(res.data);
          fileitems.value = res.data.data.blog;
          pagesize.value = res.data.data.pagesize;
          total.value = res.data.data.total;
          defaultPercent.value = 100;
          loadingdone.value = true;
        });
    };
    const deletepost = (id,page) => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("id_b64", proxy.$func.urlsafe_b64encode(Base64.encode(id)));
      params.append("page", page);
      //params.append("is_private", 0);   //博客都为公开
      proxy.$http.post("/ajax/delete_blog_ajax/", params).then((res) => {
        fileitems.value = res.data.data.blog;
        pagesize.value = res.data.data.pagesize;
        total.value = res.data.data.total;
        defaultPercent.value = 100;
        loadingdone.value = true;
      });
    };
    return {
      file_key,
      fileitems,
      deletepost,
      fileList: ref([]),
      defaultPercent,
      loadingdone,
      handlepagechange,
      currentpage,
      pagesize,
      total,
    };
  },
};
</script>
