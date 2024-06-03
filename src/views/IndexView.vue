<script>

import bookmarkitem from '../components/bookmarkitem.vue'
import subfolder from '../components/subfolder.vue'
import {
  CloseOutlined, SearchOutlined, StarOutlined, PlusOutlined
} from '@ant-design/icons-vue';
import { defineComponent, ref, reactive } from 'vue';
import { message } from 'ant-design-vue';


export default defineComponent({
  components: {
    bookmarkitem,
    subfolder,
    CloseOutlined,
    StarOutlined,
    SearchOutlined,
    PlusOutlined
  },
  setup() {
    const iconLoading = ref(false);
    const visible = ref(false);
    const updatedDrawerTitle = ref(String);
    const showDrawer = (drawerTitle) => {
      visible.value = true;
      updatedDrawerTitle.value = drawerTitle;
    };
    const onClose = () => {
      iconLoading.value = false;
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
      finishloading,
      iconLoading
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
      is_published: false,
      is_recommend: false,
      is_friendlink: false,
      loadingdone:false
    }
  },
  async mounted() {
    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("timestamp",new Date().getTime());
    params.append("token", $cookies.get('token'));
    /*
    const { data: folder_res } = await this.$http.post('/ajax/get_folder_ajax/', params)
    if (folder_res.code=='401'){      //不在登陆状态
      window.location.href ="/login";
    }
    */
    //console.log(folder_res.data)
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
      :url="bookmarkitem.url" :title="bookmarkitem.title" :pinyin="bookmarkitem.pinyin" :short_title="bookmarkitem.short_title"
      :is_private="bookmarkitem.is_private" :is_published="bookmarkitem.is_published" :is_recommend="bookmarkitem.is_recommend" :is_friendlink="bookmarkitem.is_friendlink" :http_code="bookmarkitem.http_code" :icon="bookmarkitem.icon_display" :search="search" :editable="editable"
      @editbookmark="fatherMethod"></bookmarkitem>
  </div>

  <div v-for="item in items.folder">
    <subfolder :folder_name="item.folder_name" :folder_id="item.id" :folder_bookmark="item.bookmarks"
      :subfolder="item.subfolder" :search="search" :editable="editable" :fatherMethod="fatherMethod"
      :display_offset="item.display_offset">
    </subfolder>
  </div>

  <div style="margin-bottom:20px;">
    &nbsp;
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
      <a-button type="primary" danger @click="addBookmark(editId, '删除')" :loading="iconLoading" >删除</a-button>
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
      <a-checkbox v-model:checked="is_published">采集</a-checkbox>
      <a-checkbox v-model:checked="is_recommend">推荐</a-checkbox>
      <a-checkbox v-model:checked="is_friendlink">友链</a-checkbox>
    </p>
    <p>
      <a-button type="primary" @click="addBookmark(editId)" :loading="iconLoading">提交</a-button>
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
