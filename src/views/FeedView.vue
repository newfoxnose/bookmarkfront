<template>
  <div class="loadingbar" v-show="loadingdone == false">
    <a-progress type="circle" :percent="defaultPercent" status="active" :show-info="false" :stroke-color="{
      '0%': '#108ee9',
      '100%': '#87d068',
    }" />
 </div>
  <h3 class="content-title">RSS订阅源</h3>

  <a-form :model="formState" name="add" :label-col="{ span: 6 }" :wrapper-col="{ span: 18 }" autocomplete="off"
    @finish="onFinish" @finishFailed="onFinishFailed">
    <a-form-item label="地址" name="feed_url" :rules="[{ required:true, message: '地址不能为空' }]">
      <a-input v-model:value="formState.feed_url">
      <template #addonAfter>
          <search-outlined @click="getUrl" v-if="!iconLoading" />
          <a-spin size="small" v-if="iconLoading" />
        </template>
      </a-input>
    </a-form-item>
    <a-form-item label="名称" name="feed_name" :rules="[{ required:true, message: '名称不能为空' }]">
      <a-input v-model:value="formState.feed_name"  />
    </a-form-item> 
    <a-form-item label="私有" name="is_private">
      <a-checkbox v-model:checked="formState.is_private"></a-checkbox>
    </a-form-item>
    <a-form-item :wrapper-col="{ offset: 6, span: 18 }">
      <a-button type="primary" html-type="submit" :loading="iconLoading">添加</a-button>
    </a-form-item>
  </a-form>

  <div>
    <div v-for=" item  in  fileitems " style="margin-bottom:5px;">
      <a style="margin-left:20px;" @click="showDrawer(item.feed_name,item.feed_url,item.id,item.is_private)"><form-outlined /></a>
      <a :href=" '/rss/' + item.id "  style="margin-left:10px;">
        {{ item.feed_name }}
      </a>
      <span style="margin-left:10px;">
        {{ item.feed_url }}
      </span>
    </div>
  </div>
  
  <a-drawer :model="drawer" :width="500" title="编辑RSS源" placement="bottom" :visible="visible" @close="onClose">
    <template #extra >
      <a-button type="primary" danger @click="deletefeed(drawer.feed_id)" :loading="iconLoading">删除</a-button>
    </template>
    <a-form :model="drawer" name="edit" :label-col="{ span: 3 }" :wrapper-col="{ span: 21 }" autocomplete="off"
    @finish="onFinish" @finishFailed="onFinishFailed">
    <a-form-item name="feed_id" style="display:none">
      <a-input v-model:value="drawer.feed_id"  />
    </a-form-item>
    <a-form-item label="名称" name="feed_name" :rules="[{ required:true, message: '名称不能为空' }]">
      <a-input v-model:value="drawer.feed_name"  />
    </a-form-item> 
    <a-form-item label="地址" name="feed_url" :rules="[{ required:true, message: '地址不能为空' }]">
      <a-input v-model:value="drawer.feed_url" />
    </a-form-item>
    <a-form-item label="私有" name="is_private">
      <a-checkbox v-model:checked="drawer.is_private"></a-checkbox>
    </a-form-item>
    <a-form-item :wrapper-col="{ offset: 3, span: 21 }">
      <a-button type="primary" html-type="submit" :loading="iconLoading">提交</a-button>
      &nbsp;
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button>
    </a-form-item>
  </a-form>
  </a-drawer>
</template>
<style scoped>


.loadingbar {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}
</style>
<script>
import { message } from 'ant-design-vue';

import { FormOutlined,SearchOutlined } from '@ant-design/icons-vue';
import { onMounted, getCurrentInstance, defineComponent, ref } from 'vue';

export default {
  components: {
    FormOutlined,SearchOutlined
  },
  setup() {
    $cookies.set('selectedkey','6',"720h") 
    $cookies.set('openkey','') 
    const defaultPercent = ref(10);
    const loadingdone = ref(false);
    const iconLoading = ref(false);

    const { proxy } = getCurrentInstance()

    const file_key = ref('')
    const fileitems = ref([])

    const formState = ref([])
    const drawer = ref([])

    const visible = ref(false);
    const showDrawer = (feed_name,feed_url,feed_id,is_private) => {
      drawer.value.feed_id=feed_id;
      drawer.value.feed_name=feed_name;
      drawer.value.feed_url=feed_url;
      if (is_private == 1) {
        drawer.value.is_private = true;
        }
        else {
          drawer.value.is_private = false;
        }
      console.log(drawer.value)
      visible.value = true;
    };
    const onClose = () => {
      iconLoading.value = false;
      visible.value = false;
    };
    const getUrl = () => {
      if (proxy.$func.isValidUrl(formState.value.feed_url)==true){
        iconLoading.value = true;
      message.info('自动获取网页标题中，请稍等');
          let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
          params.append("url", formState.value.feed_url);
          params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
          const { data: res } = proxy.$http.post('/ajax/url_title', params)
            .then(res => {
              console.log(res.data);
              // obj.success ? obj.success(res) : null
              if (res.data.msg == "请求成功") {
                message.info("成功获取源名称");
                formState.value.feed_name = res.data.data.title
              }
              else {
                message.info("未获取到源名称，请手动输入");
              }
            })
            .catch(error => {
              // obj.error ? obj.error(error) : null;
              console.log(error);
            })
      iconLoading.value = false;
      }
      else{
        message.info('网址无效');
      }
    };
    onMounted(() => {
      const interval=setInterval(() => {
        const percent = defaultPercent.value + Math.round(Math.random()*7+2);
        defaultPercent.value = percent > 95 ? 95 : percent;
        if (defaultPercent.value>90){
          clearInterval(interval);
        }
      }, 100)
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
          params.append("timestamp",new Date().getTime());
          params.append("is_private",0);
      proxy.$http.post('/ajax/list_feed_ajax/', params).then(res => {
        if (res.data.code=='401'){      //不在登陆状态
      window.location.href ="/";
    }
        fileitems.value = res.data.data.feed
        defaultPercent.value = 100;
        loadingdone.value = true
      });
    })
    const deletefeed = (id) => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("feed_id", id);
      proxy.$http.post('/ajax/delete_feed_ajax/', params).then(res => {
        fileitems.value = res.data.data.feed
      });
      visible.value = false;
      iconLoading.value = false
    };
    const onFinish = values => {
      iconLoading.value = true;
      console.log('提交数据Success:', values);
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      params.append("feed_id", values.feed_id);
      params.append("feed_name", values.feed_name);
      params.append("feed_url", values.feed_url);
      if (values.is_private == true) {
          params.append("is_private", 1);
        }
        else {
          params.append("is_private", 0);
        }
      proxy.$http.post('/ajax/save_feed_ajax/', params).then(res => {
        if (res.data.msg!=''){
          iconLoading.value = false;
          message.success(res.data.msg);
          if (res.data.msg=='添加成功'){
            fileitems.value.unshift(res.data.data);
          }
          else{
            
          }
        }
      });
    };
    const onFinishFailed = errorInfo => {
      //message.success(`失败.`);
      console.log('Failed:', errorInfo);
    };
    return {
      file_key,
      fileitems,
      visible,
      drawer,
      showDrawer,
      onClose,
      deletefeed,
      fileList: ref([]),
      defaultPercent,
      loadingdone,
      iconLoading,
      formState,
      getUrl,
      onFinish,
      onFinishFailed
    };
  },
}
</script>
