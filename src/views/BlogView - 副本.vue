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
    <h3 style="margin-top:15px;">写笔记</h3>
  <a-button type="primary" @click="save" :loading="iconLoading">保存</a-button>
  <a-form :model="formState">
    <a-form-item label="标题" name="title" :rules="[{ required: true, message: '标题不能为空' }]">
      <a-input v-model:value="formState.title" />
    </a-form-item>
    <p>
      <a-select style="width: 100%" v-model:value="formState.folder_id" v-if="folder_list">
        <a-select-option v-for="item in folder_list" :value="item.value" :lv="item.lv"> {{ item.name }}</a-select-option>
      </a-select>
    </p>
    <p>
      <a-checkbox v-model:checked="formState.is_recommend">置顶</a-checkbox>
      <a-checkbox v-model:checked="formState.is_private">不公开</a-checkbox>
    </p>
  </a-form>
  <vue-ueditor-wrap v-model="valueHtml" :config="editorConfig" editor-id="editor-demo-01"></vue-ueditor-wrap>

  <div>
    <a-modal v-model:visible="visible" title="新建笔记成功" @ok="handleOk" ok-text="确认" cancel-text="取消">
      <p>跳转到笔记列表页请点击“确定”</p>
      <p>再写一篇请点击“取消”</p>
    </a-modal>
  </div>
  <div>
    <a-image-preview-group>
      <div v-for="item in fileitems" style="margin-bottom: 5px">
        <span class="ext">{{ item.id }}</span>
        <a :href="'/editblog/' + item.id" style="margin-left: 5px">
          {{  item.title=='' ? '无标题' : item.title }}
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
import { useRouter } from 'vue-router'
export default {
  components: {
    InboxOutlined,
    EyeInvisibleTwoTone,
    LikeTwoTone,
  },
  setup() {

    const formState = ref([])
    formState.value.title = '';
    formState.value.is_private = false;
    formState.value.is_recommend = false;
    const folder_list = ref([])
    const router = useRouter()
    const iconLoading = ref(false);

    // 内容 HTML
    const valueHtml = ref('<p>hello</p>')

    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const currentpage = ref(1);
    const pagesize = ref(1);
    const total = ref(1);

    const { proxy } = getCurrentInstance();

    const file_key = ref("");
    const fileitems = ref([]);

    onMounted(() => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/get_folder_ajax/', params).then(res => {
        if (res.data.code=='401'){      //不在登陆状态
      window.location.href ="/";
    }
        folder_list.value = res.data.data.data
        formState.value.folder_id = res.data.data.data[0].value
      })
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
    const save = () => {
      console.log(router.currentRoute.value.params);
      iconLoading.value = true;
      if (proxy.$func.getVarType(formState.value.title) == "undefined" || formState.value.title == '') {
        message.info("标题不能为空");
        iconLoading.value = false;
      }
      else {
        let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get('token'));
        params.append("timestamp",new Date().getTime());
        params.append("content", valueHtml.value);
        params.append("title", formState.value.title);
        params.append("folder_id", formState.value.folder_id);
        if (formState.value.is_private == true) {
          params.append("is_private", 1);
        }
        else {
          params.append("is_private", 0);
        }
        if (formState.value.is_recommend == true) {
          params.append("is_recommend", 1);
        }
        else {
          params.append("is_recommend", 0);
        }
        proxy.$http.post('/ajax/save_blog_ajax/', params).then(res => {
          //console.log(res.data.msg);
          if (res.data.msg == "新建成功") {    //这条提示如果改的话要和后端一起改
            visible.value = true;
            //message.info("已保存，即将跳转到列表页面",10);
            iconLoading.value = false;
            //window.location.href = "/blog";
          }
          else{
            message.info(res.data.msg);
            iconLoading.value = false;
          }
        }).catch(error => {
          message.info("无法正常保存");
          iconLoading.value = false;
          console.log(error);
        });
      }
    }
    const visible = ref(false);
    const handleOk = e => {
      visible.value = false;
      window.location.href = "/blog";
    };
    return {
      visible,
      handleOk,
      formState,
      folder_list,
      valueHtml,
      save,
      iconLoading,
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
  created() {
    // 更多 UEditor 配置，参考 http://fex.baidu.com/ueditor/#start-config
    this.editorConfig = {
      /*
      toolbars: [
        ['source', 'undo', 'redo'],
        ['bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc','simpleupload']
      ],
      */
      UEDITOR_HOME_URL: '/UEditor/', // 访问 UEditor 静态资源的根路径，可参考常见问题1
      lang: 'zh-cn',
      // 初始容器高度
      initialFrameHeight: 360,
      serverUrl: this.$remoteDomain+'/ueditor/controller.php?token='+$cookies.get('token')+'&timestamp='+(new Date().getTime()), // 服务端接口
    };
  }
};
</script>
