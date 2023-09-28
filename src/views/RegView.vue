<template>
  <div style="margin-top:15px;"></div>
  <a-form :model="formState" name="basic" :label-col="{ span: 8 }" :wrapper-col="{ span: 16 }" autocomplete="off"
    @finish="reg" @finishFailed="onFinishFailed">

    <a-form-item label="邮箱" name="email" :rules="[{ type: 'email', required: true, message: '请输入有效邮箱地址' }]">
      <a-input v-model:value="formState.email" />
    </a-form-item>

    <a-form-item label="密码" name="password" :rules="[{ required: true, message: '请输入至少6位密码' }]">
      <a-input-password v-model:value="formState.password" />
    </a-form-item>

    <a-form-item label="重复密码" name="repeat_password" :rules="[{ required: true, message: '请输入相同密码' }]">
      <a-input-password v-model:value="formState.repeat_password" />
    </a-form-item>

    <a-form-item label="验证码" name="captcha" :rules="[{ required: true, message: '请输入验证码' }]">
      <a-input v-model:value="formState.captcha">
        <template #addonAfter>
          <div><img id="captchaimg" @click="reload_captcha"></div>
        </template>
      </a-input>
    </a-form-item>


    <a-form-item :wrapper-col="{ offset: 8, span: 16 }">
      <a-button type="primary" html-type="submit" :loading="iconLoading">注册</a-button>
    </a-form-item>
  </a-form>
</template>

<script>
import { SettingOutlined } from '@ant-design/icons-vue';

import { message } from 'ant-design-vue';
import { defineComponent, reactive, getCurrentInstance, ref } from 'vue';
import Fingerprint2 from 'fingerprintjs2'

//要使用jquery，必须修改vite.config.js并把下面两句加上
import jQuery from "jquery";
Object.assign(window, { $: jQuery, jQuery });
//jquery结束

if ($cookies.get('login') == "yes") {
  window.location.href = "/user"
}
export default defineComponent({
  components: {
    SettingOutlined,
  },
  setup() {
    const iconLoading = ref(false);
    const { proxy } = getCurrentInstance()
    const murmur = ref('')
    const formState = reactive({     //熟悉下这里的数据写法
      email: '',
      password: '',
      repeat_password: '',
    });
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
    const reg = (values) => {
      iconLoading.value = true;
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("email", values.email);
      params.append("password", values.password);
      params.append("repeat_password", values.repeat_password);
      params.append("captcha", values.captcha);
      params.append("hashkey", murmur.value);
      proxy.$http.post('/ajax/reg_ajax/', params).then(res => {
        console.log(res.data)
        iconLoading.value = false;
        message.info(res.data.msg);
        // obj.success ? obj.success(res) : null
        if (res.data.code == "200") {
          $cookies.set('teacher_id', res.data.data.teacher_id, "720h")
          $cookies.set('login', res.data.data.login, "720h")
          $cookies.set('level', res.data.data.level, "720h")
          //window.location.href = "/user"
        }
      }).catch(error => {
        iconLoading.value = false;
        // obj.error ? obj.error(error) : null;
        console.log(error);
      });
    }
    return {
      reg,
      murmur,
      formState,
      onFinish,
      onFinishFailed,
      iconLoading,
      reload_captcha
    };
  }
});

</script>