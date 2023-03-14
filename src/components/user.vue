<script>

import bookmarkitem from './bookmarkitem.vue'
import subfolder from './subfolder.vue'
import {
  CloseOutlined, SearchOutlined,StarOutlined
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
    return {
      visible,
      showDrawer,
      updatedDrawerTitle,
      onClose,
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
      is_private: false
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
          const { data: res } = this.$http.post('/user/url_title', params)
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
          ajax_url = '/user/delete_bookmark_ajax';
        }
        else if (id!=''){
          params.append("id", id);
          ajax_url = '/user/edit_bookmark_ajax';
        }
        else {
          ajax_url = '/user/add_bookmark_ajax';
        }
        const { data: res } = this.$http.post(ajax_url, params)
          .then(res => {
            console.log(res.data);
            // obj.success ? obj.success(res) : null
            message.info(res.data.msg);
            if (res.data.data != null) {
              window.location.href = "/user"
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
    }
  },
  async mounted() {
    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("teacher_id", $cookies.get('teacher_id'));
    params.append("login", $cookies.get('login'));
    params.append("level", $cookies.get('level'));
    const { data: res } = await this.$http.post('/user/home_ajax/', params)
    this.items = res.data
    console.log(res.data);
    const { data: folder_res } = await this.$http.post('/user/get_folder_ajax/', params)
    this.folder_list = folder_res.data.data
    this.folder_id = folder_res.data.data[0].value;
  },
});
console.log($cookies.get('teacher_id'))
console.log($cookies.get('level'))
console.log($cookies.get('login'))
</script>

<template>
  <div>
    <bookmarkitem v-for="bookmarkitem in items.root_bookmarks" :id="bookmarkitem.id" :folder_id="bookmarkitem.folder_id"
      :url="bookmarkitem.url" :title="bookmarkitem.title" :short_title="bookmarkitem.short_title"
      :is_private="bookmarkitem.is_private" :icon="bookmarkitem.icon_display" :search="search" :editable="editable"
      @editbookmark="fatherMethod"></bookmarkitem>
  </div>

  <div v-for="item in items.folder">
    <subfolder :folder_name="item.folder_name" :folder_bookmark="item.bookmarks" :subfolder="item.subfolder"
      :search="search" :editable="editable" :fatherMethod="fatherMethod">
    </subfolder>
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
    <template #extra>
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
</style>
