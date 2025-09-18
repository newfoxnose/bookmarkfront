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
  <h3  class="content-title">笔记<br><br>
    <a-button type="primary" @click="showDrawer('新建笔记', 0, '', false)"><form-outlined />新建</a-button>
    <a-button type="primary" style="margin-left: 8px" @click="showDrawer('新建笔记（MarkDown）', 0, '', true)"><form-outlined />新建（MarkDown）</a-button>
  </h3>

  <a-tabs v-model:activeKey="activeKey" @tabClick="clicktab">
    <a-tab-pane :key="0" tab="公开">
      <div>
        <a-image-preview-group>
          <div v-for="(item, index) in fileitems" style="margin-bottom: 5px">
            <span class="ext">{{ index + 1 }}</span>
            <a
              style="margin-left: 20px"
              @click="showDrawer(item.title, item.id)"
              > {{ item.title }}</a
            >
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
            <div style="margin-left: 50px"><a-image
              v-for="img in item.img_arr"
              :key="img.label"
              :width="200"
              :src="img"
            /></div>
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
              style="margin-left: 20px"
              @click="
                  showDrawer(
                    item.title,
                    item.id,
                    privateCode
                  )
                "
              > {{ item.title }}</a
            >
             
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
                    item.id, privateCode
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
            @change="handleprivatepagechange(privateCode)"
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
    <div class="form-header">
      <a-form :model="formState">
        <a-row>
          <a-col :span="8">
            <p>
              <a-input
                v-model:value="formState.title"
                placeholder="请输入标题"
              /></p
          ></a-col>
          <a-col :span="4">
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
          <a-col :span="8" align="right">
            <a-button
              type="primary"
              @click="save(privateCode)"
              :loading="iconLoading"
              >保存并关闭</a-button
            >
            <a-button
              type="primary"
              style="margin-left: 8px"
              @click="saveOnly(privateCode)"
              :loading="iconLoading"
              >保存（Ctrl+S）</a-button
            >
            &nbsp;
            <a-button style="margin-right: 8px" @click="onClose">取消</a-button></a-col
          >
        </a-row>
      </a-form>
    </div>
    <div class="editor-container">
      <!-- 根据is_markdown字段选择编辑器类型 -->
      <vue-ueditor-wrap
        v-if="!isMarkdownMode"
        v-model="valueHtml"
        :config="editorConfig"
        editor-id="editor-demo-01"
      ></vue-ueditor-wrap>
      
      <!-- Markdown编辑器 -->
      <md-editor
        v-else
        v-model="markdownContent"
        :toolbars="markdownToolbars"
        :preview="true"
        :code-theme="'github'"
        :language="'zh-CN'"
        style="height: 100%;"
      />
    </div>
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

  <!-- 虚拟数字键盘 -->
  <VirtualKeyboard 
    v-model="privateCode"
    :visible="showKeyboard"
    @confirm="confirmKeyboardInput"
    @close="closeKeyboard"
  />
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
    position: relative;
    overflow: hidden;
  }
}

.form-header {
  position: sticky;
  top: 0;
  z-index: 10;
  background: #fff;

  border-bottom: 1px solid #f0f0f0;
  margin-bottom: 16px;
}

.editor-container {
  height: calc(100vh - 120px);
  overflow-y: auto;
}

/* Markdown编辑器样式 */
.editor-container .md-editor {
  height: 100% !important;
  border: none !important;
}

.editor-container .md-editor .md-toolbar {
  border-bottom: 1px solid #f0f0f0;
}

.editor-container .md-editor .md-content {
  height: calc(100% - 50px) !important;
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
import md5 from 'js-md5';
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
// 导入md-editor-v3
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
// 导入虚拟键盘组件
import VirtualKeyboard from '@/components/VirtualKeyboard.vue';

export default {
  components: {
    CloseOutlined,
    InboxOutlined,
    EyeInvisibleTwoTone,
    LikeTwoTone,
    StarOutlined,
    FormOutlined,
    SearchOutlined,
    MdEditor,
    VirtualKeyboard
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
    
    // Markdown内容
    const markdownContent = ref("# 写点什么呢？");
    
    // 是否为Markdown模式
    const isMarkdownMode = ref(false);

    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const currentpage = ref(1);
    const pagesize = ref(10);
    const total = ref(1);
    const uid = ref('');

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
    const isSaving = ref(false);
    const saveRetryCount = ref(0);
    const MAX_RETRIES = 3;

    // 虚拟键盘相关状态
    const showKeyboard = ref(false);
    const privateCode = ref('');

    // Markdown编辑器工具栏配置
    const markdownToolbars = [
      'bold', 'underline', 'italic', 'strikethrough', 'title', 'sub', 'sup', 'quote', 'unordered-list', 'ordered-list', 'task-list', '-',
      'code', 'code-block', 'link', 'image', 'table', 'mermaid', 'katex', '-',
      'preview', 'fullscreen', 'content'
    ];

    const clicktab = (key) => {
      currentpage.value=1;
      if (key == -1) {
        // 显示虚拟键盘输入私有内容口令
        showKeyboard.value = true;
        privateCode.value = '';
      } else {
        show_private.value = false;
        visible_inputpassword.value = false;
        formState_inputpassword.value.password = "";
        showKeyboard.value = false;
        privateCode.value = '';
        handlepagechange(1);
      }
    };

    // 虚拟键盘相关方法
    const openKeyboard = () => {
      showKeyboard.value = true;
    };
    
    // 关闭虚拟键盘
    const closeKeyboard = () => {
      showKeyboard.value = false;
    };
    
    // 确认虚拟键盘输入
    const confirmKeyboardInput = (value) => {
      privateCode.value = value;
      // 使用虚拟键盘输入的口令调用私有内容加载
      handleprivatepagechange(value);
      closeKeyboard();
    };

    const showDrawer = (drawerTitle, id, password, isMarkdown) => {
      drawerclass.value = "drawer-" + $cookies.get("theme") + "-theme";
      updatedDrawerTitle.value = drawerTitle;
      
      // 如果是新建操作，清除本地存储的blog_id
      if (id === 0) {
        localStorage.removeItem('current_blog_id');
        visible.value = true;
        formState.value.title = "无标题";
        formState.value.folder_id = -1;
        formState.value.is_private = false;
        formState.value.is_recommend = false;
        
        // 根据isMarkdown参数决定使用哪种编辑器
        if (isMarkdown) {
          isMarkdownMode.value = true;
          markdownContent.value = "# 写点什么呢？\n\n开始你的 Markdown 笔记...";
        } else {
          isMarkdownMode.value = false;
          valueHtml.value = "<p>写点什么呢？</p>";
        }
        return;
      }
      
      if (id != 0) {
        blog_id.value = id;
        let params = new URLSearchParams();
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("blog_id", blog_id.value);
        params.append("password", password ? md5(password) : '');
        proxy.$http.post("/ajax/get_blog_ajax/", params).then((res) => {
          console.log(res.data);
          if (res.data.code == "201") {
            message.error("密码错误");
          } else {
            visible.value = true;
            visible_inputpassword.value = false;
            
            // 根据is_markdown字段决定使用哪种编辑器
            if (res.data.data.blog.is_markdown == 1) {
              isMarkdownMode.value = true;
              markdownContent.value = res.data.data.blog.content;
            } else {
              isMarkdownMode.value = false;
              valueHtml.value = res.data.data.blog.content;
            }
            
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
      // 重置编辑器模式
      isMarkdownMode.value = false;
      // 重置虚拟键盘状态
      showKeyboard.value = false;
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
      window.addEventListener("keydown", handleEvent); //监听按键事件
    });
    const handleEvent = (event) => {
        if (event.ctrlKey && (event.key === 's' || event.keyCode === 83)) {
          event.preventDefault();
          event.returnValue = false;
          saveOnly();
        }
    };
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
        uid.value = res.data.data.uid;
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
      params.append("password", md5(password));
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
      params.append("password", md5(password));
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
      // 防重复提交检查
      if (isSaving.value) {
        message.info("正在保存中，请稍候...");
        return;
      }

      // blog_id 验证
      if (!blog_id.value && !updatedDrawerTitle.value.includes("新建笔记")) {
        message.error("保存失败：博客ID丢失");
        return;
      }

      isSaving.value = true;
      iconLoading.value = true;

      if (!formState.value.title) {
        message.info("标题不能为空");
        isSaving.value = false;
        iconLoading.value = false;
        return;
      }

      let params = new URLSearchParams();
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      
      // 根据编辑器类型获取内容
      const content = isMarkdownMode.value ? markdownContent.value : valueHtml.value;
      params.append("content", content);
      params.append("title", formState.value.title);
      params.append("folder_id", formState.value.folder_id);
      params.append("password", password ? md5(password) : '');
      
      // 保存当前 blog_id 到本地存储
      if (blog_id.value !== 0) {
        localStorage.setItem('current_blog_id', blog_id.value);
        params.append("post_id", blog_id.value);
      }

      params.append("is_private", formState.value.is_private ? 1 : 0);
      params.append("is_recommend", formState.value.is_recommend ? 1 : 0);
      // 添加is_markdown字段
      params.append("is_markdown", isMarkdownMode.value ? 1 : 0);

      const trySave = () => {
        proxy.$http
          .post("/ajax/save_blog_ajax/", params)
          .then((res) => {
            if (res.data.code === 200) {
              // 如果是新建操作，更新 blog_id
              if (blog_id.value === 0 && res.data.data.blog_id) {
                blog_id.value = res.data.data.blog_id;
                localStorage.setItem('current_blog_id', res.data.data.blog_id);
              }
              message.info(res.data.msg);
              if (show_private.value == false) {
                handlepagechange();
              } else {
                handleprivatepagechange(password);
              }
              onClose();
            } else {
              message.error(res.data.msg || "保存失败");
            }
            isSaving.value = false;
            iconLoading.value = false;
            saveRetryCount.value = 0;
          })
          .catch((error) => {
            console.error("保存失败:", error);
            if (saveRetryCount.value < MAX_RETRIES) {
              saveRetryCount.value++;
              message.info(`保存失败，正在重试(${saveRetryCount.value}/${MAX_RETRIES})...`);
              setTimeout(trySave, 1000);
            } else {
              message.error("保存失败，请稍后重试");
              isSaving.value = false;
              iconLoading.value = false;
              saveRetryCount.value = 0;
            }
          });
      };

      trySave();
    };
    const saveOnly = (password) => {
      // 防重复提交检查
      if (isSaving.value) {
        message.info("正在保存中，请稍候...");
        return;
      }

      // blog_id 验证
      if (!blog_id.value && !updatedDrawerTitle.value.includes("新建笔记")) {
        message.error("保存失败：博客ID丢失");
        return;
      }

      isSaving.value = true;
      iconLoading.value = true;

      if (!formState.value.title) {
        message.info("标题不能为空");
        isSaving.value = false;
        iconLoading.value = false;
        return;
      }

      let params = new URLSearchParams();
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      
      // 根据编辑器类型获取内容
      const content = isMarkdownMode.value ? markdownContent.value : valueHtml.value;
      params.append("content", content);
      params.append("title", formState.value.title);
      params.append("folder_id", formState.value.folder_id);
      params.append("password", password ? md5(password) : '');
      
      // 保存当前 blog_id 到本地存储
      if (blog_id.value !== 0) {
        localStorage.setItem('current_blog_id', blog_id.value);
        params.append("post_id", blog_id.value);
      }

      params.append("is_private", formState.value.is_private ? 1 : 0);
      params.append("is_recommend", formState.value.is_recommend ? 1 : 0);
      // 添加is_markdown字段
      params.append("is_markdown", isMarkdownMode.value ? 1 : 0);

      const trySave = () => {
        proxy.$http
          .post("/ajax/save_blog_ajax/", params)
          .then((res) => {
            if (res.data.code === 200) {
              // 如果是新建操作，更新 blog_id
              if (blog_id.value === 0 && res.data.data.blog_id) {
                blog_id.value = res.data.data.blog_id;
                localStorage.setItem('current_blog_id', res.data.data.blog_id);
              }
              message.info(res.data.msg);
              if (show_private.value == false) {
                handlepagechange();
              } else {
                handleprivatepagechange(password);
              }
            } else {
              message.error(res.data.msg || "保存失败");
            }
            isSaving.value = false;
            iconLoading.value = false;
            saveRetryCount.value = 0;
          })
          .catch((error) => {
            console.error("保存失败:", error);
            if (saveRetryCount.value < MAX_RETRIES) {
              saveRetryCount.value++;
              message.info(`保存失败，正在重试(${saveRetryCount.value}/${MAX_RETRIES})...`);
              setTimeout(trySave, 1000);
            } else {
              message.error("保存失败，请稍后重试");
              isSaving.value = false;
              iconLoading.value = false;
              saveRetryCount.value = 0;
            }
          });
      };

      trySave();
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
      markdownContent,
      isMarkdownMode,
      markdownToolbars,
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
      searchstring,
      uid,
      saveOnly,
      isSaving,
      saveRetryCount,
      MAX_RETRIES,
      // 虚拟键盘相关
      showKeyboard,
      privateCode,
      openKeyboard,
      closeKeyboard,
      confirmKeyboardInput
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
            'fullscreen', 'undo', 'redo', '|',
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
  data() {
    return {
      go_url: this.$remoteDomain  +  "/post/"
    };
  }
};
</script>
