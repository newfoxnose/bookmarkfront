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
  <p style="margin-top: 15px">
    <a-button type="primary" @click="showDrawer('新建笔记', 0, 0)"
      ><form-outlined />新建笔记</a-button
    >
  </p>
  <div>
    <a-image-preview-group>
      <div v-for="item in fileitems" style="margin-bottom: 5px">
        <span class="ext">{{ item.id }}</span>
        <a @click="showDrawer(item.title, item.id, item.is_private)">
          {{ item.title == "" ? "无标题" : item.title }}
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
        <a
          style="margin-left: 20px"
          @click="showconfirmdelete(item.id, currentpage)"
          >删除</a
        >
        <br /><a-image
          v-for="img in item.img_arr"
          :key="img.label"
          :width="200"
          :src="img"
        />
      </div>
    </a-image-preview-group>
  </div>
  <div style="clear: both">
    <a-pagination
      v-model:current="currentpage"
      v-model:pageSize="pagesize"
      :total="total"
      @change="handlepagechange"
      show-less-items
    />
  </div>
  <a-modal
    v-model:visible="visible"
    :title="updatedDrawerTitle"
    width="100%"
    wrap-class-name="full-modal"
    :class="drawerclass"
    @close="onClose"
    @ok="save"
  >
    <template #footer>
      <a-button type="primary" @click="save()" :loading="iconLoading"
        >保存</a-button
      >
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button>
    </template>

    <a-form :model="formState">
      <a-row>
        <a-col :span="8">
          <p>
            <a-input
              v-model:value="formState.title"
              placeholder="请输入标题"
            /></p
        ></a-col>
        <a-col :span="8">
          <p style="padding-left: 20px">
            <a-select
              style="width: 100%"
              v-model:value="formState.folder_id"
              v-if="folder_list"
            >
              <a-select-option
                v-for="item in folder_list"
                :value="item.value"
                :lv="item.lv"
                :class="drawerclass"
              >
                {{ item.name }}</a-select-option
              >
            </a-select>
          </p></a-col
        >
        <a-col :span="8">
          <p style="padding-left: 20px; white-space: nowrap">
            <a-checkbox v-model:checked="formState.is_recommend"
              >置顶</a-checkbox
            >
            <a-checkbox v-model:checked="formState.is_private">私密</a-checkbox>
          </p></a-col
        >
      </a-row>
    </a-form>
    <vue-ueditor-wrap
      v-model="valueHtml"
      :config="editorConfig"
      editor-id="editor-demo-01"
    ></vue-ueditor-wrap>
  </a-modal>
  <a-modal
    v-model:visible="visible_inputpassword"
    title="请输入密码查看私密笔记"
  >
    <a-form :model="formState_inputpassword">
      <a-form-item
        label="密码"
        name="password2"
        :rules="[{ required: true, message: '请输入至少6位密码' }]"
      >
        <a-input-password v-model:value="formState_inputpassword.password" />
      </a-form-item>
    </a-form>
    <template #footer>
      <a-button
        type="primary"
        @click="
          showDrawer(
            private_blog.title,
            private_blog.id,
            0,
            formState_inputpassword.password
          )
        "
        :loading="iconLoading"
        >提交</a-button
      >
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button>
    </template>
  </a-modal>
</template>
<style lang="less">
.full-modal {
  .ant-modal {
    max-width: 100%;
    top: 0;
    padding-bottom: 0;
    margin: 0;
  }
  .ant-modal-content {
    display: flex;
    flex-direction: column;
    height: calc(100vh);
  }
  .ant-modal-body {
    flex: 1;
  }
}
</style>
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
  margin-right: 5px;
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
import { message, Modal } from "ant-design-vue";

import {
  InboxOutlined,
  EyeInvisibleTwoTone,
  LikeTwoTone,
  StarOutlined,
  FormOutlined,
} from "@ant-design/icons-vue";
import { onMounted, getCurrentInstance, defineComponent, ref } from "vue";
import * as qiniu from "qiniu-js";
import { Base64 } from "js-base64";
import { useRouter } from "vue-router";
export default {
  components: {
    InboxOutlined,
    EyeInvisibleTwoTone,
    LikeTwoTone,
    StarOutlined,
    FormOutlined,
  },
  setup() {
    $cookies.set("selectedkey", "5", "720h");
    $cookies.set("openkey", "");
    const formState = ref([]);
    formState.value.title = "";
    formState.value.is_private = false;
    formState.value.is_recommend = false;
    const folder_list = ref([]);
    const router = useRouter();
    const iconLoading = ref(false);

    // 内容 HTML
    const valueHtml = ref("<p>写点什么呢？</p>");

    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const currentpage = ref(1);
    const pagesize = ref(1);
    const total = ref(1);

    const { proxy } = getCurrentInstance();

    const file_key = ref("");
    const fileitems = ref([]);

    const updatedDrawerTitle = ref(String);
    const visible = ref(false);
    const blog_id = ref(0);
    const drawerclass = ref("");

    const visible_inputpassword = ref(false);
    const formState_inputpassword = ref([]);
    const private_blog = ref([]);
    const showDrawer = (drawerTitle, id, is_private, password) => {
      console.log(drawerTitle, id, is_private, password)
      if (is_private == 0) {
        drawerclass.value = "drawer-" + $cookies.get("theme") + "-theme";
        updatedDrawerTitle.value = drawerTitle;
        if (id != 0) {
          blog_id.value = id;
          let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
          params.append("token", $cookies.get("token"));
          params.append("timestamp", new Date().getTime());
          params.append("blog_id", blog_id.value);
          params.append("password", password);
          proxy.$http.post("/ajax/get_blog_ajax/", params).then((res) => {
            console.log(res.data)
            if (res.data.code == "201") {
              message.error("密码错误");
            } else {
              visible.value = true;
              visible_inputpassword.value = false;
              valueHtml.value = res.data.data.blog.content;
              defaultPercent.value = 100;
              loadingdone.value = true;
              formState.value.title = res.data.data.blog.title;
              formState.value.folder_id = res.data.data.blog.folder_id;
              if (res.data.data.blog.is_private == 1) {
                formState.value.is_private = true;
              } else {
                formState.value.is_private = false;
              }
              if (res.data.data.blog.is_recommend == 1) {
                formState.value.is_recommend = true;
              } else {
                formState.value.is_recommend = false;
              }
            }
          });
        } else {
          visible.value = true;
          formState.value.title = "无标题";
          formState.value.folder_id = -1;
          formState.value.is_private = false;
          formState.value.is_recommend = false;
          valueHtml.value = "<p>写点什么呢？</p>";
        }
      } else {
        private_blog.value.title = drawerTitle;
        private_blog.value.id = id;
        visible_inputpassword.value = true;
      }
    };

    const showconfirmdelete = (editId, currentpage) => {
      Modal.confirm({
        title: "确认删除该项目吗？",
        content: "点击OK删除且无法找回, 点击cancel取消",
        onOk() {
          deletepost(editId, currentpage);
        },
        // eslint-disable-next-line @typescript-eslint/no-empty-function
        onCancel() {},
      });
    };
    const onClose = () => {
      iconLoading.value = false;
      visible.value = false;
      visible_inputpassword.value = false;
      blog_id.value = 0;
    };

    onMounted(() => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      proxy.$http.post("/ajax/get_folder_ajax/", params).then((res) => {
        if (res.data.code == "401") {
          //不在登陆状态
          window.location.href = "/";
        }
        folder_list.value = res.data.data.data;
        formState.value.folder_id = res.data.data.data[0].value;
      });
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
    const deletepost = (id, page) => {
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
      if (
        proxy.$func.getVarType(formState.value.title) == "undefined" ||
        formState.value.title == ""
      ) {
        message.info("标题不能为空");
        iconLoading.value = false;
      } else {
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("content", valueHtml.value);
        params.append("title", formState.value.title);
        params.append("folder_id", formState.value.folder_id);
        if (blog_id.value != 0) {
          params.append("post_id", blog_id.value);
        }
        if (formState.value.is_private == true) {
          params.append("is_private", 1);
        } else {
          params.append("is_private", 0);
        }
        if (formState.value.is_recommend == true) {
          params.append("is_recommend", 1);
        } else {
          params.append("is_recommend", 0);
        }
        proxy.$http
          .post("/ajax/save_blog_ajax/", params)
          .then((res) => {
            //console.log(res.data.msg);
            if (res.data.msg == "新建成功") {
              //这条提示如果改的话要和后端一起改
              message.info("新建笔记成功"); //看不到效果
              iconLoading.value = false;
              handlepagechange(1);
            } else {
              message.info(res.data.msg);
              iconLoading.value = false;
              handlepagechange(1);
            }
            onClose();
          })
          .catch((error) => {
            message.info("无法正常保存");
            iconLoading.value = false;
            console.log(error);
          });
      }
    };
    return {
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
      visible,
      showDrawer,
      onClose,
      updatedDrawerTitle,
      blog_id,
      showconfirmdelete,
      drawerclass,
      visible_inputpassword,
      formState_inputpassword,
      private_blog,
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
      UEDITOR_HOME_URL: "/UEditor/", // 访问 UEditor 静态资源的根路径，可参考常见问题1
      lang: "zh-cn",
      // 初始容器高度
      initialFrameHeight: 300,
      serverUrl:
        this.$remoteDomain +
        "/ueditor/controller.php?token=" +
        $cookies.get("token") +
        "&timestamp=" +
        new Date().getTime(), // 服务端接口
    };
  },
};
</script>
