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
    <a-button type="primary" @click="showDrawer('新建笔记', 0)"><form-outlined />新建笔记</a-button>
  </p>

  <a-tabs v-model:activeKey="activeKey" @tabClick="clicktab">
    <a-tab-pane :key="0" tab="公开">
      <div>
        <a-image-preview-group>
          <div v-for="(item, index) in fileitems" style="margin-bottom: 5px">
            <span class="ext">{{ index + 1 }}</span>
            <a @click="showDrawer(item.title, item.id)">
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

            <span style="margin-left: 20px" class="font-color-by-theme">( {{ item.createtime }})</span>
            <a
              style="margin-left: 20px"
              @click="showconfirmdelete(item.id,  '')"
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
    </a-tab-pane>
    <a-tab-pane :key="-1" tab="私有">
      <div v-if="show_private == true">
        <div>
          <a-image-preview-group>
            <div v-for="(item, index) in fileitems" style="margin-bottom: 5px">
              <span class="ext">{{ index + 1 }}</span>
              <a
                @click="
                  showDrawer(
                    item.title,
                    item.id,
                    formState_inputpassword.password
                  )
                "
              >
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
              <span style="margin-left: 20px" class="font-color-by-theme">( {{ item.createtime }})</span>
              <a
                style="margin-left: 20px"
                @click="
                  showconfirmdelete(
                    item.id,formState_inputpassword.password
                  )
                "
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
            @change="handleprivatepagechange(formState_inputpassword.password)"
            show-less-items
          />
        </div>
      </div>
    </a-tab-pane>
  </a-tabs>
  <a-modal
    v-model:visible="visible"
    :title="updatedDrawerTitle"
    width="100%"
    wrap-class-name="full-modal"
    :class="drawerclass"
    :footer="null"
    @close="onClose"
  >
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
        <a-col :span="4">
          <p style="padding-left: 20px; white-space: nowrap">
            <a-checkbox v-model:checked="formState.is_recommend"
              >置顶</a-checkbox
            >
            <a-checkbox v-model:checked="formState.is_private">私密</a-checkbox>
          </p></a-col
        >
        <a-col :span="4" align="right">
          <a-button
        type="primary"
        @click="save(formState_inputpassword.password)"
        :loading="iconLoading"
        >保存</a-button
      >
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button></a-col
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
    :class="drawerclass"
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
        @click="handleprivatepagechange(formState_inputpassword.password)"
        :loading="iconLoading"
        >提交</a-button
      >
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button>
    </template>
  </a-modal>
  <div class="search-div">
    <div class="search">
      <a-input v-model:value="searchstring">
        <template #addonAfter>
          <search-outlined @click="search" />
        </template>
      </a-input>
    </div>
  </div>
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


.search-div {
  display: flex;
  flex-direction: column;
}

.search {
  align-self: center;
  position: fixed;
  bottom: 0;
  z-index: 2;
  display: inline;
  width: 200px;
}

.search-div button {
  height: 34px !important;
}

#search {
  border-color: #4cae4c;
  border-width: 2px;
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
  CloseOutlined,
  InboxOutlined,
  EyeInvisibleTwoTone,
  LikeTwoTone,
  StarOutlined,
  FormOutlined,
  SearchOutlined
} from "@ant-design/icons-vue";
import { onMounted, getCurrentInstance, defineComponent, ref } from "vue";
import * as qiniu from "qiniu-js";
import { Base64 } from "js-base64";
import { useRouter } from "vue-router";
export default {
  components: {
    CloseOutlined,
    InboxOutlined,
    EyeInvisibleTwoTone,
    LikeTwoTone,
    StarOutlined,
    FormOutlined,
    SearchOutlined
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
    const pagesize = ref(10);
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

    const activeKey = ref(0);
    const show_private = ref(false);
    const searchstring = ref("");
    const clicktab = (key) => {
      currentpage.value=1;
      if (key == -1) {
        visible_inputpassword.value = true;
      } else {
        show_private.value = false;
        visible_inputpassword.value = false;
        formState_inputpassword.value.password = "";
        handlepagechange(1);
      }
    };

    const showDrawer = (drawerTitle, id, password) => {
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
          console.log(res.data);
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
    };

    const showconfirmdelete = (editId,  password) => {
      Modal.confirm({
        title: "确认删除该项目吗？",
        content: "点击OK删除且无法找回, 点击cancel取消",
        onOk() {
          deletepost(editId, password);
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
    const handlepagechange = (page) => {
      const interval = setInterval(() => {
        const percent =
          defaultPercent.value + Math.round(Math.random() * 7 + 2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value > 90) {
          clearInterval(interval);
        }
      }, 100);
      if (page != 1) {
        page = currentpage.value;
      }
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("pagesize", pagesize.value);
      proxy.$http.post("/ajax/list_blog_ajax/" + page, params).then((res) => {
        //console.log(res.data);
        fileitems.value = res.data.data.blog;
        pagesize.value = Number(res.data.data.pagesize);
        total.value = res.data.data.total;
        defaultPercent.value = 100;
        loadingdone.value = true;
      });
    };

    const handleprivatepagechange = (password) => {
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
      params.append("password", password);
      params.append("pagesize", pagesize.value);
      proxy.$http
        .post("/ajax/list_private_blog_ajax/" + currentpage.value, params)
        .then((res) => {
          console.log(res.data);
          if (res.data.code == 200) {
            fileitems.value = res.data.data.blog;
            total.value = res.data.data.total;
            defaultPercent.value = 100;
            loadingdone.value = true;
            visible_inputpassword.value = false;
            show_private.value = true;
          } else {
            message.error("密码错误");
          }
        });
    };
    const deletepost = (id, password) => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("id_b64", proxy.$func.urlsafe_b64encode(Base64.encode(id)));
      params.append("password", password);
      params.append("page", currentpage.value);
      params.append("pagesize", pagesize.value);
      //params.append("is_private", 0);   //博客都为公开
      proxy.$http.post("/ajax/delete_blog_ajax/", params).then((res) => {
        fileitems.value = res.data.data.blog;
        pagesize.value = res.data.data.pagesize;
        total.value = res.data.data.total;
        defaultPercent.value = 100;
        loadingdone.value = true;
      });
    };
    const save = (password) => {
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
        params.append("password", password);
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
            message.info(res.data.msg);
            iconLoading.value = false;
            if (show_private.value == false) {
              handlepagechange();
            } else {
              handleprivatepagechange(password);
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
    const search = () => {
      iconLoading.value = true;
      if (
        searchstring.value == ""
      ) {
        message.info("搜索内容不能为空");
        iconLoading.value = false;
      } else {
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("searchstring", searchstring.value);
        params.append("pagesize", pagesize.value);
        proxy.$http
          .post("/ajax/search_blog_ajax/", params)
          .then((res) => {
            //console.log(res.data.msg);
            message.info(res.data.msg);
            iconLoading.value = false;
            fileitems.value = res.data.data.blog;
        pagesize.value = res.data.data.pagesize;
        total.value = res.data.data.total;
        defaultPercent.value = 100;
        loadingdone.value = true;
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
      activeKey,
      clicktab,
      show_private,
      handleprivatepagechange,
      search,
      searchstring
    };
  },
  watch: {
    searchstring(newQuestion) {
      if (newQuestion==''){
        this.handlepagechange();
      }
    },
  },
  created() {
    // 更多 UEditor 配置，参考 http://fex.baidu.com/ueditor/#start-config
    this.editorConfig = {
      ///*
      toolbars: [
        [
            'fullscreen',  'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', '|', 'forecolor', 'backcolor', '|', 'removeformat', '|',
            'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|',
            'link', 'unlink', 'anchor', '|',
            'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'simpleupload', 'insertimage', 'emotion','insertvideo',  'attachment',  '|',
            'insertframe', 'insertcode',  'pagebreak', 'template', 'background', '|',
            'horizontal', 'date', 'time', 'spechars', 'snapscreen', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|',
            'print', 'preview', 'searchreplace'
        ]
    ],
      //*/
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
