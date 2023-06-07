<script>

import bookmarkitem from './bookmarkitem.vue'
import subfolder from './subfolder.vue'
import {
  CloseOutlined, SearchOutlined, StarOutlined, PlusOutlined
} from '@ant-design/icons-vue';
import { defineComponent, ref, reactive } from 'vue';
import { message } from 'ant-design-vue';
if ($cookies.get('login') != "yes") {
  window.location.href = "/login"
}

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
    const visible = ref(false);
    const updatedDrawerTitle = ref(String);
    const showDrawer = (drawerTitle) => {
      visible.value = true;
      updatedDrawerTitle.value = drawerTitle;
    };
    const onClose = () => {
      visible.value = false;
    };
    const defaultPercent = ref(5);
    const increaseloading = () => {
      const percent = defaultPercent.value + 10;
      defaultPercent.value = percent > 95 ? 95 : percent;
    };
    const finishloading = () => {
      defaultPercent.value = 100;
    };
    return {
      visible,
      showDrawer,
      updatedDrawerTitle,
      onClose,
      defaultPercent,
      increaseloading,
      finishloading
    };
  },
  data() {
    return {
      items: {},
      question: '',
      search: '',
      editable: 'yes',
      title: '',
      url: '',
      folder_id: '',
      folder_list: {},
      clicked: false,
      editId: '',
      new_folder: '',
      new_folder_clicked: false,
      is_private: false,
      loadingdone:false
    }
  },
  watch: {
    // 每当 question 改变时，这个函数就会执行
    question(newQuestion, oldQuestion) {
      this.search = newQuestion
    }
  },
  methods: {
    fatherMethod(drawerTitle, id, url, title, folder_id, is_private) {
      if (drawerTitle == '编辑书签') {
        this.showDrawer(drawerTitle);
        this.editId = id;
        this.url = url;
        this.title = title;
        if (is_private == 1) {
          this.is_private = true;
        }
        else {
          this.is_private = false;
        }
        if (folder_id == -1) {
          this.folder_id = this.folder_list[0].value;
        }
        else {
          this.folder_id = folder_id;
        }
      }
      else {
        this.showDrawer("新建书签");
        this.editId = '';
        this.url = '';
        this.title = '';
        this.folder_id = this.folder_list[0].value;
        this.is_private = false;
      }
    },
    clearQuestion() {
      this.question = ''
      this.search = ''
    },
    getUrl() {
      let theurl = this.url.toLowerCase();
      if (theurl == '') {
        message.info('网址不能为空');
      }
      else if (theurl.substring(0, 7) != "http://" && theurl.substring(0, 8) != "https://") {
        message.info('网址必须以http://或https://开头');
      }
      else {
        if (this.clicked == false) {
          this.clicked = true;
          message.info('自动获取网页标题中，请稍等');
          let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
          params.append("url", theurl);
          params.append("teacher_id", $cookies.get('teacher_id'));
          params.append("login", $cookies.get('login'));
          params.append("level", $cookies.get('level'));
          const { data: res } = this.$http.post('/ajax/url_title', params)
            .then(res => {
              console.log(res.data);
              // obj.success ? obj.success(res) : null
              if (res.data.msg == "请求成功") {
                message.info("成功获取网页标题");
                this.title = res.data.data.title
              }
              else {
                message.info("未获取到网页标题，但仍可以直接添加网址");
                this.title = res.data.data.title
              }
              this.clicked = false;
            })
            .catch(error => {
              // obj.error ? obj.error(error) : null;
              console.log(error);
              this.clicked = false;
            })
        }
        else {
          message.info("正在请求数据，请勿重复点击");
        }
      }
    },
    addBookmark(id, action) {
      if (this.url != '' && this.title != '' && this.folder_id != '') {
        let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
        params.append("url", this.url);
        params.append("title", this.title);
        params.append("folder_id", this.folder_id);
        if (this.is_private == true) {
          params.append("is_private", 1);
        }
        else {
          params.append("is_private", 0);
        }
        params.append("teacher_id", $cookies.get('teacher_id'));
        params.append("login", $cookies.get('login'));
        params.append("level", $cookies.get('level'));
        let ajax_url = '';
        if (id != '' && action == "删除") {
          params.append("id", id);
          ajax_url = '/ajax/delete_bookmark_ajax';
        }
        else if (id != '') {
          params.append("id", id);
          ajax_url = '/ajax/edit_bookmark_ajax';
        }
        else {
          ajax_url = '/ajax/add_bookmark_ajax';
        }
        const { data: res } = this.$http.post(ajax_url, params)
          .then(res => {
            //console.log(res.data);
            // obj.success ? obj.success(res) : null
            message.info(res.data.msg);
            if (res.data.data != null) {
              let temp_item = res.data.data;
              let folder_path = res.data.data.folder_path;
              if (id != '' && action == "删除") {
                this.items = this.$func.insert_item(this.items, folder_path, temp_item, "delete");
                //document.querySelector("[itemid='" + id + "']").remove();
              }
              else if (id != '') {
                let old_folder_path = res.data.data.old_folder_path;
                this.items = this.$func.insert_item(this.items, old_folder_path, temp_item, "delete");   //删除旧的
                this.items = this.$func.insert_item(this.items, folder_path, temp_item);                //插入新的
              }
              else {
                this.items = this.$func.insert_item(this.items, folder_path, temp_item);
              }
              this.onClose();
            }
          })
          .catch(error => {
            // obj.error ? obj.error(error) : null;
            console.log(error);
          })
      }
      else {
        message.info("请填写必要项目");
      }
    },
    newFolder() {
      if (this.new_folder == '') {
        message.info('新目录名称不能为空');
      }
      else {
        if (this.new_folder_clicked == false) {
          this.new_folder_clicked = true;
          message.info('创建新目录中，请稍等');
          let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
          params.append("new_folder", this.new_folder);
          params.append("folder_id", this.folder_id);
          params.append("teacher_id", $cookies.get('teacher_id'));
          params.append("login", $cookies.get('login'));
          params.append("level", $cookies.get('level'));
          const { data: res } = this.$http.post('/ajax/new_folder_ajax', params)
            .then(res => {
              // obj.success ? obj.success(res) : null
              console.log(res.data)
              if (res.data.msg == "新建目录成功") {
                message.info("新建目录成功");
                this.folder_list = res.data.select_folder;
                this.folder_id = String(res.data.inserted_id);
                let temp_folder = res.data.data;
                let folder_path = temp_folder.folder_path;
                folder_path.pop();
                this.items = this.$func.insert_item(this.items, folder_path, temp_folder, "newfolder");
              }
              else {
                message.info(res.data.msg);
              }
              this.new_folder_clicked = false;
            })
            .catch(error => {
              // obj.error ? obj.error(error) : null;
              console.log(error);
              this.new_folder_clicked = false;
            })
        }
        else {
          message.info("正在请求数据，请勿重复点击");
        }
      }
    },
    home_stream_ajax(folder_index) {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      this.$http.post('/ajax/home_stream_ajax/' + folder_index, params).then((res) => {
        if (res.data.data.next_folder_index != -1) {
          this.items.folder[folder_index] = res.data.data.folder[folder_index];
          this.home_stream_ajax(res.data.data.next_folder_index);
          this.increaseloading()
        }
        else {
          this.finishloading()
          this.loadingdone=true
          this.$http.post('/ajax/update_http_code/', params).then((res) => {
            console.log(res.data);
          })
        }
      })
    }
  },
  async mounted() {
    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("teacher_id", $cookies.get('teacher_id'));
    params.append("login", $cookies.get('login'));
    params.append("level", $cookies.get('level'));
    const { data: res } = await this.$http.post('/ajax/home_stream_ajax/', params)
    //console.log(res.data)
    this.items = res.data
    if (res.data.next_folder_index != -1) {
      this.home_stream_ajax(0);
    }
    const { data: folder_res } = await this.$http.post('/ajax/get_folder_ajax/', params)
    this.folder_list = folder_res.data.data
    this.folder_id = folder_res.data.data[0].value;

  },
});
</script>
 
<template>
    <div class="loadingbar" v-show="loadingdone==false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
        '0%': '#108ee9',
        '100%': '#87d068',
      }"/>
  </div>


  <div :folderid="-1">
    <h3 style="margin-top:15px;">根目录</h3>
    <bookmarkitem v-for="bookmarkitem in items.root_bookmarks" :id="bookmarkitem.id" :folder_id="bookmarkitem.folder_id"
      :url="bookmarkitem.url" :title="bookmarkitem.title" :short_title="bookmarkitem.short_title"
      :is_private="bookmarkitem.is_private" :http_code="bookmarkitem.http_code" :icon="bookmarkitem.icon_display" :search="search" :editable="editable"
      @editbookmark="fatherMethod"></bookmarkitem>
  </div>

  <div v-for="item in items.folder">
    <subfolder :folder_name="item.folder_name" :folder_id="item.id" :folder_bookmark="item.bookmarks"
      :subfolder="item.subfolder" :search="search" :editable="editable" :fatherMethod="fatherMethod"
      :display_offset="item.display_offset">
    </subfolder>
  </div>

  <div style="margin-bottom:20px;">
  </div>

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

  <a-drawer :width="500" :title="updatedDrawerTitle" placement="bottom" :visible="visible" @close="onClose">
    <template #extra v-if="updatedDrawerTitle == '编辑书签'">
      <a-button type="primary" danger @click="addBookmark(editId, '删除')">删除</a-button>
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
      <a-select style="width: 100%" v-model:value="folder_id" v-if="folder_list">
        <a-select-option v-for="item in folder_list" :value="item.value" :lv="item.lv"> {{ item.name }}</a-select-option>
      </a-select>
    </p>
    <p>
      <a-input v-model:value="new_folder" placeholder="新目录">
        <template #addonAfter>
          <plus-outlined @click="newFolder" v-if="!new_folder_clicked" />
          <a-spin size="small" v-if="new_folder_clicked" />
        </template>
      </a-input>
    </p>
    <p>
      <a-checkbox v-model:checked="is_private">私有</a-checkbox>
    </p>
    <p>
      <a-button type="primary" @click="addBookmark(editId)">提交</a-button>
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
    content: ' ';
    border-left: 1px solid var(--color-border);
    position: absolute;
    left: 0;
    bottom: calc(50% + 25px);
    height: calc(50% - 25px);
  }

  .folder-name:after {
    content: ' ';
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
  position:fixed;
  top:50%;
left:50%;
transform: translate(-50%,-50%);
  z-index: 10;
}

</style>
