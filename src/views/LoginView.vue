<template>
    
    <h3>登入</h3>

  <a-result v-if="logged==0" title="检查是否已登入......" sub-title="请稍等.......">
  </a-result>
  <a-form v-if="logged==1" :model="formState" name="basic" :label-col="{ span: 6 }" :wrapper-col="{ span: 14 }" autocomplete="off"
    @finish="login" @finishFailed="onFinishFailed">

    <a-form-item label="邮箱" name="email" :rules="[{ type: 'email', required: true, message: '请输入有效邮箱地址' }]">
      <a-input v-model:value="formState.email" />
    </a-form-item>

    <a-form-item label="密码" name="password" :rules="[{ required: true, message: '请输入密码' }]">
      <a-input-password v-model:value="formState.password" />
    </a-form-item>


    <a-form-item label="验证码" name="captcha" :rules="[{ required: true, message: '请输入验证码' }]">
      <a-input v-model:value="formState.captcha">
        <template #addonAfter>
          <div><img id="captchaimg" @click="reload_captcha"></div>
        </template>
      </a-input>
    </a-form-item>

    <a-form-item :wrapper-col="{ offset: 6, span: 14 }">
      <a-checkbox v-model:checked="checked">仅在此设备登入</a-checkbox>
    </a-form-item>

    <a-form-item :wrapper-col="{ offset: 6, span: 14 }">
      <a-button type="primary" html-type="submit" :loading="iconLoading">登入</a-button>
    </a-form-item>
  </a-form>
</template>
<script>
import { message } from 'ant-design-vue'; 
import { defineComponent, reactive,ref,getCurrentInstance,onMounted } from 'vue';
import Fingerprint2 from 'fingerprintjs2'
import md5 from 'js-md5';

//要使用jquery，必须修改vite.config.js并把下面两句加上
import jQuery from "jquery";
Object.assign(window, { $: jQuery, jQuery });
//jquery结束

export default defineComponent({
  setup() {
    $cookies.set('selectedkey','15',"720h") 
    $cookies.set('openkey','') 
    const logged = ref(0);
    const iconLoading = ref(false);
    const murmur = ref('')
    const { proxy } = getCurrentInstance()
    const formState = reactive({     //熟悉下这里的数据写法
      email: '',
      password: ''
    });
    const checked = ref(false);
    const onFinish = values => {
      console.log('Success:', values);
    };
    const onFinishFailed = errorInfo => {
      console.log('Failed:', errorInfo);
    };
    Fingerprint2.get(function (components) {
      const values = components.map(function (component, index) {
        if (index === 0) { //把微信浏览器里UA的wifi或4G等网络替换成空,不然切换网络会ID不一样
          return component.value.replace(/\bNetType\/\w+\b/, '')
        }
        return component.value
      })
      // 生成最终id murmur   
      murmur.value = Fingerprint2.x64hash128(values.join(''), 31);
      proxy.$http.get('/ajax/captcha_ajax/' + murmur.value, { params: {}, responseType: 'blob' }).then(res => {
        //必须使用get方法，post会出错
        var src = window.URL.createObjectURL(res.data);
        $("#captchaimg").attr("src", src);
      });
    });
    const reload_captcha= () =>{
      proxy.$http.get('/ajax/captcha_ajax/' + murmur.value, { params: {}, responseType: 'blob' }).then(res => {
        //必须使用get方法，post会出错
        var src = window.URL.createObjectURL(res.data);
        $("#captchaimg").attr("src", src);
      });
    }
    const login = (values) => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("email", values.email);
      params.append("password", md5(values.password));
      params.append("captcha", values.captcha);
      params.append("exclusive_login", checked.value);
      params.append("hashkey", murmur.value);
      params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/login_ajax/', params).then(res => {
        //console.log(res.data)
        iconLoading.value = false;
        message.info(res.data.msg);
        // obj.success ? obj.success(res) : null
        if (res.data.code == "200") {
          $cookies.set('token',res.data.data.token,"720h") 
          logged.value = 2; 
            window.location.href ="/home"
          }
          else{
            $cookies.set("token", "", "-720h");
            logged.value = 1;
          }
      }).catch(error => {
        iconLoading.value = false;
        // obj.error ? obj.error(error) : null;
        console.log(error);
      });
    }
    onMounted(() => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());

      proxy.$http.post("/ajax/get_folder_ajax/", params).then((folder_res) => {
        console.log(folder_res);
        if (folder_res.data.code == "200") {
          //在登陆状态就跳转到home页
          logged.value = 2; 
          window.location.href = "/home";
        }
        else{
          logged.value = 1; 
        }
      });
    });
    return {
      login,
      murmur,
      reload_captcha,
      formState,
      onFinish,
      iconLoading,
      onFinishFailed,
      checked,
      logged,
    };
  },

});
</script>