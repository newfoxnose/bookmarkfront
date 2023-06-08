<script>
if ($cookies.get('login') != "yes") {
  window.location.href = "/login"
}
import {
  HomeOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { defineComponent, ref } from 'vue';
export default defineComponent({
  components: {
    HomeOutlined
  },
  setup() {
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const visible = ref(false);
    const prompt_visible = ref(false);
    const empty_prompt_visible = ref(false);
    const movebookmark_drawer_visible = ref(false);
    const showDrawer = () => {
      visible.value = true;
    };
    const show_prompt = () => {
      prompt_visible.value = true;
    };
    const show_empty_prompt = () => {
      empty_prompt_visible.value = true;
    };
    const show_movebookmark_drawer = () => {
      movebookmark_drawer_visible.value = true;
    };
    const closeDrawer = () => {
      visible.value = false;
    };
    const hide_prompt = () => {
      prompt_visible.value = false;
    };
    const hide_empty_prompt = () => {
      empty_prompt_visible.value = false;
    };
    const close_movebookmark_drawer = () => {
      movebookmark_drawer_visible.value = false;
    };
    return {
      visible,
      showDrawer,
      closeDrawer,
      prompt_visible,
      show_prompt,
      hide_prompt,
      movebookmark_drawer_visible,
      show_movebookmark_drawer,
      close_movebookmark_drawer,
      empty_prompt_visible,
      show_empty_prompt,
      hide_empty_prompt,
      defaultPercent,
      loadingdone
    };
  },
  data() {
    return {
      folder_name: '',
      father_id: '',
      folder_id: '',
      folder_data: [{
        key: '',
        name: '',
        lv: '',
        value: '',
        disabled: false,
      }],
      data: [],
      columns: [{
        title: 'lv',
        dataIndex: 'lv'
      }, {
        title: '书签数量',
        dataIndex: 'amount'
      }, {
        title: '操作'
      }],
      columns_addnew: [ {
        title: '新目录名称'
      }, {
        title: '父目录',
      }, {
        title: '操作'
      }],
      data_addnew: [{}],   //这里至少要有一条空记录
      new_folder_name: '',
      new_father_id: '根目录',     
    }
  },
  methods: {
    editFolder(folder_id, folder_name, father_id) {
      this.folder_name = folder_name
      this.folder_id = folder_id
      this.father_id = father_id
      var meet_self = false
      var temp_lv = 0
      for (var i = 0; i < this.data.length; i++) {
        this.folder_data[i] = this.data[i]
        if (this.data[i]['value'] == folder_id) {
          meet_self = true
          this.folder_data[i]['disabled'] = true
        }
        else {
          if (meet_self == false) {
            this.folder_data[i]['disabled'] = false
          }
          else {
            if (this.data[i]['lv'] > temp_lv) {
              this.folder_data[i]['disabled'] = true
            }
            else {
              this.folder_data[i]['disabled'] = false
              meet_self = false
            }
          }
        }
        temp_lv = this.data[i]['lv']
      }
      this.showDrawer()
      console.log(this.data)
    },
    deleteFolder(folder_id) {
      this.show_prompt()
      this.folder_id = folder_id
    },
    moveBookmark(folder_id) {
      this.show_movebookmark_drawer()
      this.folder_id = folder_id
      this.father_id = folder_id
    },
    emptyFolder(folder_id) {
      this.show_empty_prompt()
      this.folder_id = folder_id
    },
    submitModify(action) {
      console.log("folder_id:" + this.folder_id)
      if (action == "修改" && (this.folder_id == '' || this.folder_name == '' || this.father_id == '')) {
        message.info("请填写必要项目");
      }
      else if (action != "修改" && this.folder_id == '') {
        message.info("请填写必要项目");
      }
      else {
        let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
        params.append("folder_id", this.folder_id);
        params.append("folder_name", this.folder_name);
        params.append("father_id", this.father_id);
        params.append("teacher_id", $cookies.get('teacher_id'));
        params.append("login", $cookies.get('login'));
        params.append("level", $cookies.get('level'));
        let ajax_url = '';
        if (action == "修改") {
          ajax_url = '/ajax/edit_folder_ajax/update_folder';
        }
        else if (action == "删除") {
          ajax_url = '/ajax/edit_folder_ajax/delete_folder';
        }
        else if (action == "移动") {
          ajax_url = '/ajax/edit_folder_ajax/move_bookmark';
        }
        else if (action == "清空") {
          ajax_url = '/ajax/edit_folder_ajax/empty_folder';
        }
        const { data: res } = this.$http.post(ajax_url, params)
          .then(res => {
            console.log(res.data);
            // obj.success ? obj.success(res) : null
            message.info(res.data.msg);
            if (res.data.data == true) {
              this.load_data()
              this.closeDrawer()
              this.hide_prompt()
              this.close_movebookmark_drawer()
              this.hide_empty_prompt()
            }
          })
          .catch(error => {
            // obj.error ? obj.error(error) : null;
            console.log(error);
          })
      }
    },
    newFolder() {
      if (this.new_folder_name != '' && this.new_father_id != '') {
        let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
        params.append("new_folder", this.new_folder_name);
        params.append("folder_id", this.new_father_id);
        params.append("teacher_id", $cookies.get('teacher_id'));
        params.append("login", $cookies.get('login'));
        params.append("level", $cookies.get('level'));
        const { data: res } = this.$http.post('/ajax/new_folder_ajax', params)
          .then(res => {
            console.log(res.data);
            // obj.success ? obj.success(res) : null
            message.info(res.data.msg);
            if (res.data.inserted_id != null) {
              this.load_data()
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
    async load_data() {     //async和await需要一起出现
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("teacher_id", $cookies.get('teacher_id'));
      params.append("login", $cookies.get('login'));
      params.append("level", $cookies.get('level'));
      const { data: folder_res } = await this.$http.post('/ajax/manage_folder_ajax/', params)
      this.data = folder_res.data.data
      this.defaultPercent = 95;
      this.loadingdone=true;
      if (folder_res.data.data[0]['name'] == '工作') {
        this.new_father_id = folder_res.data.data[0]['value'];
      }
    },
  },
  async mounted() {
    this.load_data()
    const interval=setInterval(() => {
        this.defaultPercent = (this.defaultPercent+10) > 95 ? 95 : this.defaultPercent;
        if (this.defaultPercent>90){
          clearInterval(interval);
        }
      }, 100)
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
  <a-modal v-model:visible="prompt_visible" title="操作确认" ok-text="确认" cancel-text="取消" @ok="submitModify('删除')">
    <p>此操作将删除所选择的目录及其子目录、所有书签</p>
  </a-modal>
  <a-modal v-model:visible="empty_prompt_visible" title="操作确认" ok-text="确认" cancel-text="取消" @ok="submitModify('清空')">
    <p>此操作将删除所选择目录内的所有书签</p>
  </a-modal>
  <div style="margin-top:15px;"></div>
  <a-table :columns="columns_addnew" :data-source="data_addnew" :pagination="false">
    <template #headerCell="{ column }">
    </template>
    <template #bodyCell="{ column, record }">
      <template v-if="column.title == '新目录名称'">
        <a-input v-model:value="new_folder_name" />
      </template>
      <template v-else-if="column.title == '父目录'">
        <a-select style="width: 160px" v-model:value="new_father_id" v-if="data">
          <a-select-option v-for="item in data" :value="item.value" :lv="item.lv"> {{ item.label
          }}</a-select-option>
        </a-select>
      </template>
      <template v-else-if="column.title == '操作'">
        <a-button type="primary" @click="newFolder()">添加</a-button>
      </template>
    </template>
  </a-table>


  <a-table :columns="columns" :data-source="data" v-if="data" :pagination="false">
    <template #headerCell="{ column }">
      <template v-if="column.title == 'lv'">
        <span>已有目录</span>
      </template>
    </template>
    <template #bodyCell="{ column, record }">
      <template v-if="column.title === 'lv'">
        <span v-if="record.lv == -1" style="margin-right:3px;">
          <home-outlined />
        </span>
        <span v-for="n in record.lv + 1" v-if="record.lv > -1">
          &nbsp;&nbsp;&nbsp;&nbsp;
        </span>
        <img src="@/assets/expansion.png?555" width="20" height="20" v-if="record.lv > -1">
        <span style="margin-left:3px;">{{ record.name }}</span>
      </template>
      <template v-else-if="column.title === '操作'">
        <span v-if="record.lv == -1">
          <a @click="emptyFolder(record.value)">清空</a>
          <a-divider type="vertical" />
          <a @click="deleteFolder(record.value)">删除</a>
        </span>
        <span v-else>
          <a @click="editFolder(record.value, record.name, record.father_id)">修改</a>
          <a-divider type="vertical" />
          <a @click="moveBookmark(record.value)">移动</a>
          <a-divider type="vertical" />
          <a @click="emptyFolder(record.value)">清空</a>
          <a-divider type="vertical" />
          <a @click="deleteFolder(record.value)">删除</a>
        </span>
      </template>
    </template>
  </a-table>
  <a-drawer width="100%" title="修改目录" placement="bottom" :visible="visible" @close="closeDrawer">
    <template #extra>
      <a-button type="primary" danger @click="addBookmark(editId, '删除')">删除</a-button>
    </template>
    <a-form name="basic" :label-col="{ span: 2 }" :wrapper-col="{ span: 22 }" autocomplete="off">
      <a-form-item label="目录名" name="username">
        <a-input v-model:value="folder_name" />
      </a-form-item>
      <a-form-item label="父目录" name="xxx">
        <a-select style="width: 100%" v-model:value="father_id" v-if="folder_data">
          <a-select-option v-for="item in folder_data" :value="item.value" :lv="item.lv" :disabled="item.disabled"> {{
            item.label
          }}</a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item :wrapper-col="{ offset: 2, span: 22 }">
        <a-button type="primary" @click="submitModify('修改')">提交</a-button>
        &nbsp;
        <a-button style="margin-right: 8px" @click="closeDrawer">取消</a-button>
      </a-form-item>
    </a-form>
  </a-drawer>

  <a-drawer width="100%" title="移动书签" placement="bottom" :visible="movebookmark_drawer_visible"
    @close="close_movebookmark_drawer">
    <a-form name="basic" :label-col="{ span: 4 }" :wrapper-col="{ span: 20 }" autocomplete="off">
      <a-form-item label="本目录内书签移动到：">
        <a-select style="width: 100%" v-model:value="father_id" v-if="data">
          <a-select-option v-for="item in data" :value="item.value" :lv="item.lv">
            {{ item.label }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item :wrapper-col="{ offset: 4, span: 20 }">
        <a-button type="primary" @click="submitModify('移动')">提交</a-button>
        &nbsp;
        <a-button style="margin-right: 8px" @click="close_movebookmark_drawer">取消</a-button>
      </a-form-item>
    </a-form>
  </a-drawer>
</template>
<style scoped>

.loadingbar {
  position:fixed;
  top:50%;
left:50%;
transform: translate(-50%,-50%);
  z-index: 10;
}
</style>