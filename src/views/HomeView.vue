<script>
import bookmarkitem from "../components/bookmarkitem.vue";
import subfolder from "../components/subfolder.vue";
import {
  CloseOutlined,
  SearchOutlined,
  StarOutlined,
  PlusOutlined,
} from "@ant-design/icons-vue";
import {
  defineComponent,
  ref,
  reactive,
  getCurrentInstance,
  onMounted,
} from "vue";
import { message, Modal } from "ant-design-vue";
import html2canvas from "html2canvas";
export default defineComponent({
  components: {
    bookmarkitem,
    subfolder,
    CloseOutlined,
    StarOutlined,
    SearchOutlined,
    PlusOutlined,
  },
  setup() {
    $cookies.set("selectedkey", "1", "720h");
    $cookies.set("openkey", "");
    const iconLoading = ref(false);
    const visible = ref(false);
    const updatedDrawerTitle = ref(String);

    const screenshot = ref(null);

    const url = ref(String);
    const title = ref(String);
    const folder_id = ref(String);
    const items = ref([]);
    const folder_list = ref([]);
    const is_private = ref(false);
    const is_published = ref(false);
    const is_recommend = ref(false);
    const is_friendlink = ref(false);
    const loadingdone = ref(false);
    const urlshot_items = ref([]);
    const { proxy } = getCurrentInstance();

    const drawerclass = ref("");

    const activeKey = ref(0);

    const visible_inputpassword = ref(false);
    const formState_inputpassword = ref([]);
    const show_private = ref(false);

    const showDrawer = (drawerTitle) => {
      visible.value = true;
      updatedDrawerTitle.value = drawerTitle;
      urlshot_items.value = null;
      drawerclass.value = "drawer-" + $cookies.get("theme") + "-theme";
    };
    const onClose = () => {
      iconLoading.value = false;
      visible.value = false;
      visible_inputpassword.value = false;
    };
    const defaultPercent = ref(5);
    const increaseloading = () => {
      const percent = defaultPercent.value + 10;
      defaultPercent.value = percent > 95 ? 95 : percent;
    };
    const finishloading = () => {
      defaultPercent.value = 100;
    };

    const showconfirmdelete = (editId) => {
      Modal.confirm({
        title: "确认删除该项目吗？",
        content: "点击OK删除且无法找回, 点击cancel取消",
        onOk() {
          addBookmark(editId, "删除");
        },
        // eslint-disable-next-line @typescript-eslint/no-empty-function
        onCancel() {},
      });
    };

    const clicktab = (key) => {
      console.log(key);
      if (key == -1) {
        visible_inputpassword.value = true;
      } else {
        show_private.value = false;
        visible_inputpassword.value = false;
        formState_inputpassword.value.password = "";
      }
    };

    async function takeUrlshot(id, url) {
      console.log(id);
      console.log(url);
      try {
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        //params.append("url", formState.value.feed_url);
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("url", url);
        const { data: res } = proxy.$http
          .post("/ajax/fetch_pure_ajax", params)
          .then((res) => {
            console.log(res.data);
            // obj.success ? obj.success(res) : null
            if (res.data.data != "") {
              //console.log(res.data.data);
              message.info("成功获取网页内容");
              takeScreenshot(id, res.data.data);
            } else {
              message.info("未获取到网页内容");
            }
          })
          .catch((error) => {
            // obj.error ? obj.error(error) : null;
            console.log(error);
          });
        iconLoading.value = false;
      } catch (error) {
        console.error("生成快照失败:", error);
      }
    }

    async function takeScreenshot(id, content) {
      try {
        let tempNode = document.createElement("div");
        //tempNode.innerHTML ='<div><h1>哈哈哈</h1></div>';
        tempNode.innerHTML = content;
        document.body.after(tempNode); //这句一定要有

        //const node = document.documentElement;
        const canvas = await html2canvas(tempNode, {
          scale: 1,
          useCORS: true, // 如果图片跨域，设置为true
        });
        screenshot.value = canvas.toDataURL("image/png");
        tempNode.innerHTML = "";
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("base64", screenshot.value);
        params.append("id", id);
        proxy.$http
          .post("/ajax/upload_base64file_ajax/", params, {
            headers: {
              //"Content-Type": "multipart/form-data",    //因为是base64上传，所以不使用multipart
            },
          })
          .then((res) => {
            //console.log(res.data);
            if (res.data.code == "401") {
              //不在登陆状态
              window.location.href = "/";
            }
            defaultPercent.value = 100;
            loadingdone.value = true;
          });
      } catch (error) {
        console.error("生成截图失败:", error);
      }
    }

    onMounted(() => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());

      proxy.$http.post("/ajax/get_folder_ajax/", params).then((folder_res) => {
        if (folder_res.data.code == "401") {
          //不在登陆状态跳转到首页
          window.location.href = "/";
        }
        folder_list.value = folder_res.data.data.data;
        folder_id.value = folder_res.data.data.data[0].value;
      });

      proxy.$http.post("/ajax/home_stream_ajax/0/", params).then((res) => {
        console.log(res.data);
        items.value = res.data.data;
        if (res.data.data.next_folder_index != -1) {
          home_stream_ajax(0);
        }
      });
    });
    const private_stream_ajax = (password) => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("timestamp", new Date().getTime());
      params.append("token", $cookies.get("token"));
      params.append("password", password);
      proxy.$http.post("/ajax/private_stream_ajax", params).then((res) => {
        if (res.data.code == 200) {
          console.log(res.data.data);
          items.value.private_bookmarks = res.data.data.private_bookmarks;
          visible_inputpassword.value = false;
          show_private.value = true;
        } else {
          message.error("密码错误");
        }
      });
    };
    const home_stream_ajax = (folder_index) => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("timestamp", new Date().getTime());
      params.append("token", $cookies.get("token"));
      proxy.$http
        .post("/ajax/home_stream_ajax/0/" + folder_index, params)
        .then((res) => {
          //console.log(folder_index);
          //console.log(res.data);
          if (res.data.data.next_folder_index != -1) {
            items.value.folder[folder_index] =
              res.data.data.folder[folder_index];
            home_stream_ajax(res.data.data.next_folder_index);
            increaseloading();
          } else {
            finishloading();
            loadingdone.value = true;
            proxy.$http.post("/ajax/update_http_code/", params).then((res) => {
              console.log(res.data);
            });
          }
        });
    };

    const addBookmark = (id, action) => {
      if (url.value != "" && title.value != "" && folder_id.value != "") {
        iconLoading.value = true;
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("url", url.value);
        params.append("title", title.value);
        params.append("folder_id", folder_id.value);
        if (is_private.value == true) {
          params.append("is_private", 1);
        } else {
          params.append("is_private", 0);
        }
        if (is_published.value == true) {
          params.append("is_published", 1);
        } else {
          params.append("is_published", 0);
        }
        if (is_recommend.value == true) {
          params.append("is_recommend", 1);
        } else {
          params.append("is_recommend", 0);
        }
        if (is_friendlink.value == true) {
          params.append("is_friendlink", 1);
        } else {
          params.append("is_friendlink", 0);
        }
        params.append("timestamp", new Date().getTime());
        params.append("token", $cookies.get("token"));
        let ajax_url = "";
        if (id != "" && action == "删除") {
          params.append("id", id);
          ajax_url = "/ajax/delete_bookmark_ajax";
        } else if (id != "") {
          params.append("id", id);
          ajax_url = "/ajax/edit_bookmark_ajax";
        } else {
          ajax_url = "/ajax/add_bookmark_ajax";
        }
        const { data: res } = proxy.$http
          .post(ajax_url, params)
          .then((res) => {
            iconLoading.value = false;
            //console.log(res.data);
            // obj.success ? obj.success(res) : null
            message.info(res.data.msg);
            if (res.data.data != null) {
              let temp_item = res.data.data;
              let folder_path = res.data.data.folder_path;
              if (id != "" && action == "删除") {
                console.log(
                  "start to delete item,folder_path is " + folder_path
                );
                items.value = proxy.$func.insert_item(
                  items.value,
                  folder_path,
                  temp_item,
                  "delete"
                );
                //document.querySelector("[itemid='" + id + "']").remove();
              } else if (id != "") {
                let old_folder_path = res.data.data.old_folder_path;
                items.value = proxy.$func.insert_item(
                  items.value,
                  old_folder_path,
                  temp_item,
                  "delete"
                ); //删除旧的
                if (is_private.value == false) {
                  items.value = proxy.$func.insert_item(
                    items.value,
                    folder_path,
                    temp_item
                  ); //插入新的
                }
              } else {
                if (is_private.value == false) {
                  items.value = proxy.$func.insert_item(
                    items.value,
                    folder_path,
                    temp_item
                  );
                }
              }
              proxy.$http
                .post("/ajax/latest_stream_ajax", params)
                .then((res) => {
                  //console.log(folder_index);
                  items.value.latest_bookmarks = res.data.data.latest_bookmarks;
                });
              if (formState_inputpassword.value.password != "") {
                params.append(
                  "password",
                  formState_inputpassword.value.password
                );
                proxy.$http
                  .post("/ajax/private_stream_ajax", params)
                  .then((res) => {
                    if (res.data.code == 200) {
                      items.value.private_bookmarks =
                        res.data.data.private_bookmarks;
                    }
                  });
              }
              onClose();
            }
          })
          .catch((error) => {
            //obj.error ? obj.error(error) : null;
            //console.log(error);
            message.info("出错了，请刷新");
            onClose();
          });
      } else {
        message.info("请填写必要项目");
      }
    };

    return {
      visible,
      showDrawer,
      updatedDrawerTitle,
      onClose,
      defaultPercent,
      increaseloading,
      finishloading,
      iconLoading,
      showconfirmdelete,
      addBookmark,
      url,
      title,
      folder_id,
      items,
      is_private,
      is_published,
      is_recommend,
      is_friendlink,
      folder_list,
      loadingdone,
      takeUrlshot,
      takeScreenshot,
      screenshot,
      urlshot_items,
      drawerclass,
      activeKey,
      clicktab,
      visible_inputpassword,
      formState_inputpassword,
      show_private,
      private_stream_ajax,
    };
  },
  data() {
    return {
      question: "",
      search: "",
      editable: "yes",
      clicked: false,
      editId: "",
      new_folder: "",
      new_folder_clicked: false,
    };
  },
  watch: {
    // 每当 question 改变时，这个函数就会执行
    question(newQuestion, oldQuestion) {
      this.search = newQuestion;
    },
  },
  methods: {
    fatherMethod(
      drawerTitle,
      id,
      url,
      title,
      folder_id,
      is_private,
      is_published,
      is_recommend,
      is_friendlink
    ) {
      if (drawerTitle == "编辑书签") {
        this.showDrawer(drawerTitle);
        this.editId = id;
        this.url = url;
        this.title = title;
        if (is_private == 1) {
          this.is_private = true;
        } else {
          this.is_private = false;
        }
        if (is_published == 1) {
          this.is_published = true;
        } else {
          this.is_published = false;
        }
        if (is_recommend == 1) {
          this.is_recommend = true;
        } else {
          this.is_recommend = false;
        }
        if (is_friendlink == 1) {
          this.is_friendlink = true;
        } else {
          this.is_friendlink = false;
        }
        if (folder_id == -1) {
          this.folder_id = this.folder_list[0].value;
        } else {
          this.folder_id = folder_id;
        }
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("id", id);
        this.$http.post("/ajax/list_urlshot_ajax/", params).then((res) => {
          //console.log(res.data);
          if (res.data.code == "200") {
            if (res.data.data != null) {
              this.urlshot_items = res.data.data.urlshot;
            }
          }
        });
      } else {
        this.showDrawer("新建书签");
        this.editId = "";
        this.url = "";
        this.title = "";
        this.folder_id = this.folder_list[0].value;
        this.is_private = false;
        this.is_published = false;
        this.is_recommend = false;
        this.is_friendlink = false;
      }
    },
    clearQuestion() {
      this.question = "";
      this.search = "";
    },
    getUrl() {
      let theurl = this.url.toLowerCase();
      if (theurl == "") {
        message.info("网址不能为空");
      } else if (
        theurl.substring(0, 7) != "http://" &&
        theurl.substring(0, 8) != "https://"
      ) {
        message.info("网址必须以http://或https://开头");
      } else {
        if (this.clicked == false) {
          this.clicked = true;
          message.info("自动获取网页标题中，请稍等");
          let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
          params.append("url", theurl);
          params.append("token", $cookies.get("token"));
          params.append("timestamp", new Date().getTime());
          const { data: res } = this.$http
            .post("/ajax/url_title", params)
            .then((res) => {
              console.log(res.data);
              // obj.success ? obj.success(res) : null
              if (res.data.msg == "请求成功") {
                message.info("成功获取网页标题");
                this.title = res.data.data.title;
              } else {
                message.info("未获取到网页标题，但仍可以直接添加网址");
                this.title = res.data.data.title;
              }
              this.clicked = false;
            })
            .catch((error) => {
              // obj.error ? obj.error(error) : null;
              console.log(error);
              this.clicked = false;
            });
        } else {
          message.info("正在请求数据，请勿重复点击");
        }
      }
    },
    newFolder() {
      if (this.new_folder == "") {
        message.info("新目录名称不能为空");
      } else {
        if (this.new_folder_clicked == false) {
          this.new_folder_clicked = true;
          message.info("创建新目录中，请稍等");
          let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
          params.append("new_folder", this.new_folder);
          params.append("folder_id", this.folder_id);
          params.append("token", $cookies.get("token"));
          params.append("timestamp", new Date().getTime());
          const { data: res } = this.$http
            .post("/ajax/new_folder_ajax", params)
            .then((res) => {
              // obj.success ? obj.success(res) : null
              console.log(res.data);
              if (res.data.msg == "新建目录成功") {
                message.info("新建目录成功");
                this.folder_list = res.data.select_folder;
                this.folder_id = String(res.data.inserted_id);
                let temp_folder = res.data.data;
                let folder_path = temp_folder.folder_path;
                folder_path.pop();
                items.value = this.$func.insert_item(
                  items.value,
                  folder_path,
                  temp_folder,
                  "newfolder"
                );
              } else {
                message.info(res.data.msg);
              }
              this.new_folder_clicked = false;
            })
            .catch((error) => {
              // obj.error ? obj.error(error) : null;
              console.log(error);
              this.new_folder_clicked = false;
            });
        } else {
          message.info("正在请求数据，请勿重复点击");
        }
      }
    },
  },
});
</script>
 
<template>
  <h3>书签</h3>
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

  <a-modal
    v-model:visible="visible_inputpassword"
    title="请输入密码查看私有书签"
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
        @click="private_stream_ajax(formState_inputpassword.password)"
        :loading="iconLoading"
        >提交</a-button
      >
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button>
    </template>
  </a-modal>

  <a-tabs
    v-if="search == ''"
    v-model:activeKey="activeKey"
    @tabClick="clicktab"
  >
    <a-tab-pane :key="0" tab="根目录">
      <div :folderid="-1">
        <bookmarkitem
          v-for="bookmarkitem in items.root_bookmarks"
          :id="bookmarkitem.id"
          :folder_id="bookmarkitem.folder_id"
          :url="bookmarkitem.url"
          :title="bookmarkitem.title"
          :pinyin="bookmarkitem.pinyin"
          :short_title="bookmarkitem.short_title"
          :is_private="bookmarkitem.is_private"
          :is_published="bookmarkitem.is_published"
          :is_recommend="bookmarkitem.is_recommend"
          :is_friendlink="bookmarkitem.is_friendlink"
          :http_code="bookmarkitem.http_code"
          :icon="bookmarkitem.icon_display"
          :search="''"
          :editable="editable"
          @editbookmark="fatherMethod"
        ></bookmarkitem>
      </div>
    </a-tab-pane>
    <a-tab-pane :key="1" tab="最近收藏">
      <div>
        <bookmarkitem
          v-for="bookmarkitem in items.latest_bookmarks"
          :id="bookmarkitem.id"
          :folder_id="bookmarkitem.folder_id"
          :url="bookmarkitem.url"
          :title="bookmarkitem.title"
          :pinyin="bookmarkitem.pinyin"
          :short_title="bookmarkitem.short_title"
          :is_private="bookmarkitem.is_private"
          :is_published="bookmarkitem.is_published"
          :is_recommend="bookmarkitem.is_recommend"
          :is_friendlink="bookmarkitem.is_friendlink"
          :http_code="bookmarkitem.http_code"
          :icon="bookmarkitem.icon_display"
          :search="''"
          :editable="editable"
          @editbookmark="fatherMethod"
        ></bookmarkitem>
      </div>
    </a-tab-pane>
    <a-tab-pane
      v-for="item in items.folder"
      :key="item.id"
      :tab="`${item.folder_name}`"
    >
      <subfolder
        :folder_name="item.folder_name"
        :folder_id="item.id"
        :father_id="item.father_id"
        :folder_bookmark="item.bookmarks"
        :subfolder="item.subfolder"
        :search="''"
        :editable="editable"
        :fatherMethod="fatherMethod"
        :display_offset="item.display_offset"
      >
      </subfolder>
    </a-tab-pane>

    <a-tab-pane :key="-1" tab="私有">
      <div v-if="show_private == true">
        <bookmarkitem
          v-for="bookmarkitem in items.private_bookmarks"
          :id="bookmarkitem.id"
          :folder_id="bookmarkitem.folder_id"
          :url="bookmarkitem.url"
          :title="bookmarkitem.title"
          :pinyin="bookmarkitem.pinyin"
          :short_title="bookmarkitem.short_title"
          :is_private="bookmarkitem.is_private"
          :is_published="bookmarkitem.is_published"
          :is_recommend="bookmarkitem.is_recommend"
          :is_friendlink="bookmarkitem.is_friendlink"
          :http_code="bookmarkitem.http_code"
          :icon="bookmarkitem.icon_display"
          :search="''"
          :editable="editable"
          @editbookmark="fatherMethod"
        ></bookmarkitem>
      </div>
    </a-tab-pane>
  </a-tabs>

  <div v-if="search !== ''">
    关键词<span
      style="
        color: red;
        padding-left: 3px;
        padding-right: 3px;
        font-weight: bold;
      "
      >{{ search }}</span
    >的搜索结果：
    <hr />
    <h3>根目录</h3>
    <bookmarkitem
      v-for="bookmarkitem in items.root_bookmarks"
      :id="bookmarkitem.id"
      :folder_id="bookmarkitem.folder_id"
      :url="bookmarkitem.url"
      :title="bookmarkitem.title"
      :pinyin="bookmarkitem.pinyin"
      :short_title="bookmarkitem.short_title"
      :is_private="bookmarkitem.is_private"
      :is_published="bookmarkitem.is_published"
      :is_recommend="bookmarkitem.is_recommend"
      :is_friendlink="bookmarkitem.is_friendlink"
      :http_code="bookmarkitem.http_code"
      :icon="bookmarkitem.icon_display"
      :search="search"
      :editable="editable"
      @editbookmark="fatherMethod"
    ></bookmarkitem>
    <div v-for="item in items.folder">
      <subfolder
        :folder_name="item.folder_name"
        :folder_id="item.id"
        :folder_bookmark="item.bookmarks"
        :subfolder="item.subfolder"
        :search="search"
        :editable="editable"
        :fatherMethod="fatherMethod"
        :display_offset="item.display_offset"
      >
      </subfolder>
    </div>
  </div>
  <div style="margin-bottom: 20px">&nbsp;</div>

  <div class="search-div">
    <div class="search">
      <a-input v-model:value="question">
        <template #addonBefore>
          <star-outlined @click="fatherMethod('新建书签')" />
        </template>
        <template #addonAfter>
          <close-outlined @click="clearQuestion" />
        </template>
      </a-input>
    </div>
  </div>

  <a-drawer
    :width="500"
    :title="updatedDrawerTitle"
    :class="drawerclass"
    placement="bottom"
    :visible="visible"
    @close="onClose"
  >
    <template #extra v-if="updatedDrawerTitle == '编辑书签'">
      <a-button
        type="primary"
        danger
        @click="showconfirmdelete(editId)"
        :loading="iconLoading"
        >删除</a-button
      >
    </template>
    <p style="display: none">
      <span v-if="urlshot_items">已有快照：</span>
      <span v-for="item in urlshot_items">
        <a :href="item.key" target="_blank">{{ item.date }}</a>
      </span>
      <a-button
        type="primary"
        @click="takeUrlshot(editId, url)"
        :loading="iconLoading"
        >生成快照</a-button
      >
    </p>
    <p>
      <a-input v-model:value="url" placeholder="网址">
        <template #addonAfter>
          <search-outlined @click="getUrl" v-if="!clicked" />
          <a-spin size="small" v-if="clicked" />
        </template>
      </a-input>
    </p>
    <p>
      <a-input v-model:value="title" placeholder="标题" />
    </p>
    <p>
      <a-select
        style="width: 100%"
        v-model:value="folder_id"
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
    </p>
    <p>
      <a-input
        v-model:value="new_folder"
        placeholder="在当前位置创建新目录"
        style="width: 100%"
      >
        <template #addonAfter>
          <plus-outlined @click="newFolder" v-if="!new_folder_clicked" />
          <a-spin size="small" v-if="new_folder_clicked" />
        </template>
      </a-input>
    </p>
    <p>
      <a-checkbox v-model:checked="is_private">私有</a-checkbox>
      <a-checkbox v-model:checked="is_recommend">推荐</a-checkbox>
    </p>
    <p>
      <a-button
        type="primary"
        @click="addBookmark(editId)"
        :loading="iconLoading"
        >提交</a-button
      >
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button>
    </p>
  </a-drawer>
</template>

<style scoped>
.folder-name {
  margin-top: 2rem;
  display: flex;
}

.folder-name i {
  display: flex;
  place-items: center;
  place-content: center;
  width: 32px;
  height: 32px;
  color: var(--color-text);
}

@media (min-width: 1024px) {
  .folder-name {
    margin-top: 0;
    padding: 0.4rem 0 1rem calc(var(--section-gap) / 2);
  }

  .folder-name i {
    top: calc(50% - 25px);
    left: -26px;
    position: absolute;
    border: 1px solid var(--color-border);
    background: var(--color-background);
    border-radius: 8px;
    width: 80px;
    height: 50px;
  }

  .folder-name:before {
    content: " ";
    border-left: 1px solid var(--color-border);
    position: absolute;
    left: 0;
    bottom: calc(50% + 25px);
    height: calc(50% - 25px);
  }

  .folder-name:after {
    content: " ";
    border-left: 1px solid var(--color-border);
    position: absolute;
    left: 0;
    top: calc(50% + 25px);
    height: calc(50% - 25px);
  }

  .folder-name:first-of-type:before {
    display: none;
  }

  .folder-name:last-of-type:after {
    display: none;
  }
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
