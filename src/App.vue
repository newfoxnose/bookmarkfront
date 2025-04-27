<script>
import IndexView from "./views/IndexView.vue";

import bookmarkitem from "./components/bookmarkitem.vue";
import subfolder from "./components/subfolder.vue";
import CryptoJS from 'crypto-js';

import dayjs from "dayjs";
import "dayjs/locale/zh-cn";
dayjs.locale("zh-cn");
import { RouterLink, RouterView } from "vue-router";
import { message,Modal } from "ant-design-vue";
import { defineComponent, ref, watch,nextTick } from "vue";
import { onMounted, reactive, getCurrentInstance, toRefs } from "vue";
import {
  SearchOutlined,
  CloseOutlined,
  StarOutlined,
  FormOutlined,
  DatabaseOutlined,
  CommentOutlined,
  ProfileOutlined,
  WifiOutlined,
  MoreOutlined,
  UserAddOutlined,
  LoginOutlined,
  CalendarOutlined,
  PlusOutlined,GlobalOutlined,ApiOutlined,KeyOutlined,CoffeeOutlined
} from "@ant-design/icons-vue";
import create from "@ant-design/icons-vue/lib/components/IconFont";
// 在 App.vue 或父组件中提供刷新方法
import { provide } from "vue";

export default defineComponent({
  components: {
    IndexView,
    StarOutlined,
    FormOutlined,
    DatabaseOutlined,
    CommentOutlined,
    ProfileOutlined,
    WifiOutlined,
    MoreOutlined,
    UserAddOutlined,
    LoginOutlined,
    CalendarOutlined,
    PlusOutlined,
    CloseOutlined,
    SearchOutlined,GlobalOutlined,ApiOutlined,KeyOutlined,CoffeeOutlined
  },
  setup() {
    const items = ref([]);    // 书签列表
    const loadingdone = ref(false);   //书签列表加载完成
    const logourl = ref("../images/logo-white.png");
    const contenttheme = ref("content-dark-theme");
    const footertheme = ref("footer-dark-theme");
    const collapsetheme = ref("collapse-dark-theme");
    const state = reactive({
      theme: $cookies.get("theme"),
      selectedKeys: [$cookies.get("selectedkey")],
      openKeys: [$cookies.get("openkey")],
      collapsed: Boolean($cookies.get("collapsed") == 1),
    });
    const { proxy } = getCurrentInstance();
    const todo_items = ref([]);
    const newtodosummary = ref("");
    const useremail = ref("");
    const activeKey = ref(["xx"]);
    const badge_type = ref("#666");
    const breakpoint_active = ref(false);
    watch(state, (val) => {
      if (val.collapsed == true) {
        $cookies.set("collapsed", 1, "720h");
      } else {
        $cookies.set("collapsed", 0, "720h");
      }
    });
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
    onMounted(() => {
      if (state.theme == "dark") {
        changeTheme(true);
      } else {
        changeTheme(false);
      }

      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());

      proxy.$http.post("/ajax/toggle_todo_ajax", params).then((res) => {
        if (res.data.code == "401") {
          $cookies.set("token", "", "-720h");
        } else {
          console.log(res.data.data.email);
          useremail.value = res.data.data.email;
          toggleTodo();
        }
      });

     

      if ($cookies.get('token') != null && $cookies.get('token') != ''){

        proxy.$http.post("/ajax/get_folder_ajax/", params).then((folder_res) => {
        if (folder_res.data.code == "401") {
          //不在登陆状态跳转到首页
          window.location.href = "/";
        }
        folder_list.value = folder_res.data.data.data;
        folder_id.value = folder_res.data.data.data[0].value;
      });


        proxy.$http.post("/ajax/home_stream_ajax/0/", params).then((res) => {
        items.value = res.data.data;
        if (res.data.data.next_folder_index != -1) {
          home_stream_ajax(0);
        }
      });
      }
    });

    const home_stream_ajax = (folder_index) => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("timestamp", new Date().getTime());
      params.append("token", $cookies.get("token"));
      params.append("folder_md5", localStorage.getItem(folder_index+'md5'));
      proxy.$http
        .post("/ajax/home_stream_ajax/0/" + folder_index, params)
        .then((res) => {
          
         
    //console.log("recevied folder_index ",res.data.data.folder_index,"recevied local_folder_md5 ",res.data.data.local_folder_md5,"received server_folder_md5 ",res.data.data.server_folder_md5);
    //console.log("localstorage md5 is ",localStorage.getItem(folder_index+'md5'));
          if (res.data.data.next_folder_index != -1) {
if (res.data.data.is_same==1){
  items.value.folder[folder_index] = JSON.parse(localStorage.getItem(folder_index));
  console.log("same folder, no need to update");
}else{
  let temp=res.data.data.server_folder_json;
          localStorage.setItem(folder_index,temp );
          localStorage.setItem(folder_index+'md5',CryptoJS.MD5(temp).toString() );
  items.value.folder[folder_index] = res.data.data.folder[folder_index];
  console.log("different folder, need to update");
}            

            home_stream_ajax(res.data.data.next_folder_index);
            increaseloading();
          } else {
            finishloading();
            loadingdone.value = true;
            proxy.$http.post("/ajax/update_http_code/", params).then((res) => {
              //console.log(res.data);
            });
          }
        });
    };


    const toggleTodo = (id) => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("id", id);
      proxy.$http.post("/ajax/toggle_todo_ajax", params).then((res) => {
        todo_items.value = res.data.data.todo;
        for (let i = 0; i < todo_items.value.length; i++) {
          if (todo_items.value[i].is_done == "1") {
            todo_items.value[i].is_done = true;
          } else {
            todo_items.value[i].is_done = false;
          }
        }
      });
    };
    const createTodo = (value) => {
      if (value != "") {
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("newtodosummary", value);
        params.append("badge_type", badge_type.value);
        proxy.$http.post("/ajax/create_todo_ajax", params).then((res) => {
          console.log(res.data.data.todo);
          todo_items.value = res.data.data.todo;
          for (let i = 0; i < todo_items.value.length; i++) {
            if (todo_items.value[i].is_done == "1") {
              todo_items.value[i].is_done = true;
            } else {
              todo_items.value[i].is_done = false;
            }
          }
        });
        newtodosummary.value = "";
      } else {
        message.info("内容不能为空");
      }
    };
    const changeTheme = (checked) => {
      state.theme = checked ? "dark" : "light";
      if (state.theme == "dark") {
        $cookies.set("theme", "dark", "720h");
        logourl.value = "../images/logo-white.png";
        contenttheme.value = "content-dark-theme";
        footertheme.value = "footer-dark-theme";
        collapsetheme.value = "collapse-dark-theme";
      } else {
        $cookies.set("theme", "light", "720h");
        logourl.value = "../images/logo.png";
        contenttheme.value = "content-light-theme";
        footertheme.value = "footer-light-theme";
        collapsetheme.value = "collapse-light-theme";
      }
    };
    const onBreakpoint = (broken) => {
      //console.log(broken);
      breakpoint_active.value = broken;
    };
    const handleClick = () => {
      console.log("ddd");
      if (breakpoint_active.value == true) {
        state.collapsed = true;
      }
    };
//弹出编辑书签抽屉
    const drawerclass = ref("");
    const url = ref(String);
    const title = ref(String);
    const folder_id = ref(String);
    const iconLoading = ref(false);
    const visible = ref(false);
    const drawer2visible = ref(false);
    const is_private = ref(false);
    const is_published = ref(false);
    const is_recommend = ref(false);
    const is_friendlink = ref(false);
    const folder_list = ref([]);
    const updatedDrawerTitle = ref(String);
    const showDrawer = (drawerTitle) => {
      drawerclass.value = "drawer-" + $cookies.get("theme") + "-theme";
      visible.value = true;
      updatedDrawerTitle.value = drawerTitle;
    };
    const onClose = () => {
      iconLoading.value = false;
      visible.value = false;
    };

    const onClose2 = () => {
      iconLoading.value = false;
      drawer2visible.value = false;
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
                  items.value.popular_bookmarks = res.data.data.popular_bookmarks;
                });
              onClose();
            }
          })
          .catch((error) => {
            //obj.error ? obj.error(error) : null;
            console.log(error);
            message.info("出错了，请刷新");
            onClose();
          });
      } else {
        message.info("请填写必要项目");
      }
    };
    //弹出新建书签抽屉

    //搜索结果
    const search = ref("");
    const inputRef = ref(null);
    const DrawerinputRef = ref(null);
    const visibleSearch = ref(false);
    const activeTabKey = ref('1');
    const searchBlogResults = ref([]); // 添加笔记搜索结果变量
    const blog_id = ref(""); // 添加博客ID
    const valueHtml = ref(""); // 添加HTML内容
    const formState = ref({ // 添加表单状态
      title: "",
      folder_id: -1,
      is_private: false,
      is_recommend: false
    });
    
    // 修改为笔记专用的变量名
    const blogFormState = ref({
      blog_title: "",
      blog_folder_id: -1,
      blog_is_private: false,
      blog_is_recommend: false
    });

    const clearQuestion = () => {
      search.value = "";
      visibleSearch.value = false;
    };
    const handleSearchOk = e => {
      console.log(e);
      visibleSearch.value = false;
    };
    watch(search, (newVal) => {
      if (newVal) {
        drawerclass.value = "drawer-" + $cookies.get("theme") + "-theme";
        visibleSearch.value = true;
        nextTick(() => {
          setTimeout(() => {
            DrawerinputRef.value.focus();
            console.log("search is ",newVal);
            // 发起笔记搜索请求
            const getStringLength = (str) => {
              let length = 0;
              for (let i = 0; i < str.length; i++) {
                if (/[\u4e00-\u9fa5]/.test(str[i])) {
                  length += 2; // 中文字符长度计为2
                } else {
                  length += 1; // 其他字符长度计为1
                }
              }
              return length;
            };
            
            if (getStringLength(newVal) >= 4) {
              let params = new URLSearchParams();
              params.append("token", $cookies.get("token"));
              params.append("timestamp", new Date().getTime());
              params.append("searchstring", newVal);
              proxy.$http.post("/ajax/search_blog_ajax", params).then((res) => {
                console.log("search blog ajax res is ",res.data);
                if (res.data.code == "200") {
                  searchBlogResults.value = res.data.data.blog;
                }
              });
            } else {
              searchBlogResults.value = []; // 清空搜索结果
            }
          }, 30);
        });
      }
      else{
        visibleSearch.value = false;
        nextTick(() => {
          inputRef.value.focus();
        });
      }
    });

    
    const showDrawer2 = (drawerTitle, id) => {
      drawerclass.value = "drawer-" + $cookies.get("theme") + "-theme";
      updatedDrawerTitle.value = drawerTitle;
      if (id != 0) {
        blog_id.value = id;
        let params = new URLSearchParams();
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("blog_id", blog_id.value);
        proxy.$http.post("/ajax/get_blog_ajax/", params).then((res) => {
          console.log(res.data);
          if (res.data.code == "201") {
            message.error("密码错误");
          } else {
            drawer2visible.value = true;
            valueHtml.value = res.data.data.blog.content;
            defaultPercent.value = 100;
            loadingdone.value = true;
            blogFormState.value.blog_title = res.data.data.blog.title;
            blogFormState.value.blog_folder_id = res.data.data.blog.folder_id;
            if (res.data.data.blog.is_private == 1) {
              blogFormState.value.blog_is_private = true;
            } else {
              blogFormState.value.blog_is_private = false;
            }
            if (res.data.data.blog.is_recommend == 1) {
              blogFormState.value.blog_is_recommend = true;
            } else {
              blogFormState.value.blog_is_recommend = false;
            }
          }
        });
      } else {
        drawer2visible.value = true;
        blogFormState.value.blog_title = "无标题";
        blogFormState.value.blog_folder_id = -1;
        blogFormState.value.blog_is_private = false;
        blogFormState.value.blog_is_recommend = false;
        valueHtml.value = "<p>写点什么呢？</p>";
      }
    };
    const save = () => {
      iconLoading.value = true;
      if (
        proxy.$func.getVarType(blogFormState.value.blog_title) == "undefined" ||
        blogFormState.value.blog_title == ""
      ) {
        message.info("标题不能为空");
        iconLoading.value = false;
      } else {
        let params = new URLSearchParams();
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("content", valueHtml.value);
        params.append("title", blogFormState.value.blog_title);
        params.append("folder_id", blogFormState.value.blog_folder_id);
        if (blog_id.value != 0) {
          params.append("post_id", blog_id.value);
        }
        if (blogFormState.value.blog_is_private == true) {
          params.append("is_private", 1);
        } else {
          params.append("is_private", 0);
        }
        if (blogFormState.value.blog_is_recommend == true) {
          params.append("is_recommend", 1);
        } else {
          params.append("is_recommend", 0);
        }
        proxy.$http
          .post("/ajax/save_blog_ajax/", params)
          .then((res) => {
            message.info(res.data.msg);
            iconLoading.value = false;
            onClose2();
          })
          .catch((error) => {
            message.info("无法正常保存");
            iconLoading.value = false;
            console.log(error);
          });
      }
    };

    //搜索结果
    provide("reloadtodo", toggleTodo); //向其他组件提供刷新方法，要在本页函数初始化之后
    provide('sharedItems', items);
    provide('folder_list', folder_list);
    return {
      ...toRefs(state),
      changeTheme,
      logourl,
      contenttheme,
      footertheme,
      collapsetheme,
      todo_items,
      toggleTodo,
      newtodosummary,
      createTodo,
      activeKey,
      badge_type,
      useremail,
      handleClick,
      onBreakpoint,
      breakpoint_active,
      showDrawer,
      showDrawer2,
      visible,
      drawer2visible,
      url,
      title,
      folder_id,
      onClose,
      onClose2,
      iconLoading,
      drawerclass,
      is_private,
      is_published,
      is_recommend,
      is_friendlink,
      folder_list,
      addBookmark,
      visibleSearch,
      handleSearchOk,
      items,
      DrawerinputRef,
      inputRef,
      search,
      clearQuestion,
      loadingdone,
      defaultPercent,
      increaseloading,
      updatedDrawerTitle,
      showconfirmdelete,
      activeTabKey,
      searchBlogResults,
      blog_id,
      valueHtml,
      blogFormState,
      save
    };
  },
  
  data() {
    return {
      editable: "yes",
      clicked: false,
      new_folder: "",
      new_folder_clicked: false,
    };
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
    highlightKeyword(text, keyword) {
      if (!text || !keyword) return text;
      const reg = new RegExp(keyword, 'gi');
      return text.replace(reg, match => `<span style="color: #1890FF; font-weight: bold;">${match}</span>`);
    },
    getContextWithHighlight(content, keyword) {
      if (!content || !keyword) return '';
      
      // 找到关键词的位置
      const index = content.toLowerCase().indexOf(keyword.toLowerCase());
      if (index === -1) return '';
      
      // 计算截取的起始和结束位置
      let start = Math.max(0, index - 20);
      let end = Math.min(content.length, index + keyword.length + 20);
      
      // 如果不是从开头截取，加上省略号
      let result = start > 0 ? '...' : '';
      
      // 获取上下文
      result += content.substring(start, end);
      
      // 如果不是截取到结尾，加上省略号
      result += end < content.length ? '...' : '';
      
      // 高亮关键词
      return this.highlightKeyword(result, keyword);
    }
  },
});
</script>

<template>

    <a-layout style="min-height: 100vh"
  v-if="$cookies.get('token') != null && $cookies.get('token') != ''">
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
    <a-layout-content  :class="contenttheme">
      <a-layout>
        
  <a-layout-sider v-model:collapsed="collapsed" :theme="theme"  breakpoint="lg" collapsed-width="0" @breakpoint="onBreakpoint">
      <div class="logo" :theme="theme">
        <img :src="logourl" height="30" />
      </div>
      <a-collapse v-model:activeKey="activeKey" :class="collapsetheme" v-if="collapsed==false">
            <a-collapse-panel key="xx" header="待办">
              <p v-for="item in todo_items">
                <a-checkbox
                  v-if="item.is_done == 0"
                  @change="toggleTodo(item.id)"
                  v-model:checked="item.is_done"
                  ><span :style="{color:item.badge_type}">{{ item.summary }}</span></a-checkbox
                >
                <a-checkbox
                  v-else
                  @change="toggleTodo(item.id)"
                  v-model:checked="item.is_done"
                  ><a-typography-text delete @change="toggleTodo(item.id)">
                    <span :style="{color:item.badge_type}">{{item.summary }}</span>
                 </a-typography-text></a-checkbox
                >
              </p>
              <div class="search">
                <a-input v-model:value="newtodosummary">

                  <template #addonBefore>
                    <a-select :showArrow=false
      ref="select"
      v-model:value="badge_type"
      style="width: 32px"
    >
      <a-select-option value="#666"><a-badge color="#666"  class="big-dot" /></a-select-option>
      <a-select-option value="red"><a-badge color="red"  class="big-dot" /></a-select-option>
      <a-select-option value="orange"><a-badge color="orange"  class="big-dot" /></a-select-option>
      <a-select-option value="green"><a-badge color="green"  class="big-dot" /></a-select-option>
      <a-select-option value="blue"><a-badge color="blue"  class="big-dot" /></a-select-option>
      <a-select-option value="purple"><a-badge color="purple"  class="big-dot" /></a-select-option>
    </a-select>
</template>
                  <template #addonAfter>
                    <plus-outlined @click="createTodo(newtodosummary)" />
                  </template>
                </a-input>
              </div>
            </a-collapse-panel>
            
          </a-collapse>
      <a-menu
        v-model:selectedKeys="selectedKeys"
        mode="inline"
        :theme="theme"
        v-model:openKeys="openKeys"
      >
          <a-menu-item key="1"  @click="handleClick">
            <star-outlined />
            <span>
            <RouterLink to="/home" style="padding-left: 8px">书签</RouterLink>
          </span>
          </a-menu-item>
          <a-menu-item key="2"  @click="handleClick">
            <form-outlined /><span>
            <RouterLink to="/note" style="padding-left: 8px">随手记</RouterLink></span>
          </a-menu-item>
          <a-menu-item key="3"  @click="handleClick">
            <database-outlined /><span>
            <RouterLink to="/file" style="padding-left: 8px">文件中转</RouterLink></span>
          </a-menu-item>

          <a-menu-item key="5"  @click="handleClick">
            <profile-outlined /><span>
            <RouterLink to="/blog" style="padding-left: 8px">笔记</RouterLink></span>
          </a-menu-item>
          <a-menu-item key="15"  @click="handleClick">
            <global-outlined /><span>
              <RouterLink to="/monitoring" style="padding-left: 8px">监控网站状态</RouterLink></span>
            </a-menu-item>
            <a-menu-item key="17"  @click="handleClick">
            <api-outlined /><span>
              <RouterLink to="/fetch" style="padding-left: 8px">数据抓取</RouterLink></span>
            </a-menu-item>
            <a-menu-item key="18"  @click="handleClick">
            <key-outlined /><span>
              <RouterLink to="/pwdmemo" style="padding-left: 8px">密码管理</RouterLink></span>
            </a-menu-item>
            <a-menu-item key="19"  @click="handleClick">
            <coffee-outlined /><span>
              <RouterLink to="/pond" style="padding-left: 8px">鱼塘</RouterLink></span>
            </a-menu-item>
          <a-sub-menu key="sub1">
            <template #title>
              <span>
                <more-outlined />
                <span>更多</span>
              </span>
            </template>
            <a-menu-item key="4"  @click="handleClick">
            <comment-outlined /><span>
            <RouterLink to="/chat" style="padding-left: 8px"
              >CHATGPT</RouterLink
            ></span>
          </a-menu-item>
            <a-menu-item key="6"  @click="handleClick">
            <wifi-outlined /><span>
            <RouterLink to="/rss" style="padding-left: 8px">RSS阅读器</RouterLink></span>
          </a-menu-item>
          <a-menu-item key="16"  @click="handleClick">
            <calendar-outlined /><span>
            <RouterLink to="/calendar" style="padding-left: 8px"
              >日历</RouterLink
            ></span>
          </a-menu-item>
            <a-menu-item key="8"  @click="handleClick">
              <RouterLink to="/manage">管理目录</RouterLink>
            </a-menu-item>
            <a-menu-item key="9" v-if="useremail != 'test@test.com'"  @click="handleClick">
              <RouterLink to="/profile">个人设置</RouterLink>
            </a-menu-item>
            <a-menu-item key="10" v-if="1 == 2"  @click="handleClick">
              <RouterLink to="/clear">清理七牛云无用图片</RouterLink>
            </a-menu-item>
            <a-menu-item key="11"  @click="handleClick">
              <RouterLink to="/upload">导入书签</RouterLink>
            </a-menu-item>
            <a-menu-item key="12"  @click="handleClick">
              <RouterLink to="/export">导出书签至本地</RouterLink>
            </a-menu-item>
            <a-menu-item key="13" v-if="useremail != 'test@test.com'"  @click="handleClick">
              <RouterLink to="/email">发送书签至邮箱</RouterLink>
            </a-menu-item>
            <a-menu-item key="14">
              <RouterLink to="/logout">退出</RouterLink>
            </a-menu-item>
          </a-sub-menu>
      </a-menu>
      <div style="text-align: center; padding-top: 30px">
        <a-switch
          :checked="theme == 'dark'"
          checked-children="Dark"
          un-checked-children="Light"
          @change="changeTheme"
        />
      </div>
    </a-layout-sider>
    <a-layout-content :class="contenttheme" style="padding-bottom: 80px">
        <RouterView />
      </a-layout-content>
      </a-layout>
    </a-layout-content>
    <a-layout-footer style="text-align: center" :class="contenttheme">
        <div class="search-div" v-if="search == ''">
<div class="bookmark-search">
  <a-input v-model:value="search" ref="inputRef">
    <template #addonBefore>
      <star-outlined @click="fatherMethod('新建书签')" />
    </template>
    <template #addonAfter>
      <close-outlined @click="clearQuestion" />
    </template>
  </a-input>
</div>
</div>
  </a-layout-footer>
  </a-layout>
  <a-layout v-else>
    <IndexView />
  </a-layout>




  <a-drawer v-model:visible="visibleSearch" :title="`关键词${search}的搜索结果`" @ok="handleSearchOk" placement="bottom" :class="drawerclass" :footer="null" height="100%" @close="clearQuestion">
    <a-tabs v-model:activeKey="activeTabKey">
      <a-tab-pane key="1" tab="书签">
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
            :subfolderx="item.subfolder"
            :search="search"
            :editable="editable"
            :display_offset="item.display_offset"
            :fatherMethod="fatherMethod"
          >
          </subfolder>
        </div>
      </a-tab-pane>
      <a-tab-pane key="2" tab="笔记">
        <div v-if="searchBlogResults && searchBlogResults.length > 0">
          <div v-for="blog in searchBlogResults" :key="blog.id" style="margin-bottom: 20px;">
            <h3>
              <a
              style="margin-left: 20px"
              @click="showDrawer2(blog.title, blog.id)"
              ><span v-html="highlightKeyword(blog.title, search)"></span></a
            >
              <template v-if="blog.content">
                <span style="font-size: 14px; font-weight: normal; margin-left: 10px; color: #666;" v-html="getContextWithHighlight(blog.content, search)"></span>
              </template>
            </h3>
            <div style="color: #999; font-size: 12px; margin-top: 5px;">
              {{ blog.created_at }}
            </div>
          </div>
        </div>
        <div v-else>
          <a-empty description="暂无匹配的笔记" />
        </div>
      </a-tab-pane>
    </a-tabs>
    <div class="search-div" v-if="search != ''">
      <div class="bookmark-search">
        <a-input v-model:value="search" ref="DrawerinputRef">
          <template #addonBefore>
            <star-outlined @click="fatherMethod('新建书签')" />
          </template>
          <template #addonAfter>
            <close-outlined @click="clearQuestion" />
          </template>
        </a-input>
      </div>
    </div>
  </a-drawer>
  
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


  <a-modal
    v-model:visible="drawer2visible"
    :title="updatedDrawerTitle"
    width="100%"
    wrap-class-name="full-modal"
    :class="drawerclass"
    :footer="null"
    @close="onClose2"
  >
    <a-form :model="blogFormState">
      <a-row>
        <a-col :span="8">
          <p>
            <a-input
              v-model:value="blogFormState.blog_title"
              placeholder="请输入标题"
            /></p
        ></a-col>
        <a-col :span="8">
          <p style="padding-left: 20px">
            <a-select
              style="width: 100%"
              v-model:value="blogFormState.blog_folder_id"
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
            <a-checkbox v-model:checked="blogFormState.blog_is_recommend"
              >置顶</a-checkbox
            >
            <a-checkbox v-model:checked="blogFormState.blog_is_private">私密</a-checkbox>
          </p></a-col
        >
        <a-col :span="4" align="right">
          <a-button
        type="primary"
        @click="save()"
        :loading="iconLoading"
        >保存</a-button
      >
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose2">取消</a-button></a-col
        >
      </a-row>
    </a-form>
    <vue-ueditor-wrap
      v-model="valueHtml"
      :config="editorConfig"
      editor-id="editor-demo-02"
    ></vue-ueditor-wrap>
  </a-modal>

</template>

<style scoped>

.search-div {
  display: flex;
  flex-direction: column;
}

.bookmark-search {
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
</style>