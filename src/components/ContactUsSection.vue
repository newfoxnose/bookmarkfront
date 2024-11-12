<template>
  <v-sheet class="text-center py-16" color="primary" id="5">
    <div class="text-white text-h4 font-weight-medium">开始使用</div>
    <div class="text-center text-body-1 mb-15">登入或注册</div>
    <form :model="formState">
      <v-container>
        <v-row>
          <v-col cols="12">
            
            <v-text-field
              v-model="formState.email"
              label="邮箱*"
              type="text"
              variant="outlined"
              autocomplete="off"
            >
            </v-text-field>
          </v-col>

          <v-col cols="12">
            <v-text-field
              label="密码*"
              variant="outlined"
              v-model="formState.password"
              autocomplete="off"
              :type="show1 ? 'text' : 'password'"
              @click:append-inner="show1 = !show1"
              :append-inner-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
            >
            </v-text-field>
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="formState.captcha"
              label="验证码*"
              type="text"
              variant="outlined"
              autocomplete="off"
            >
              <template v-slot:append-inner>
                <v-fade-transition leave-absolute>
                  <img
                    alt=""
                    height="32"
                    class="captchaimg"
                    @click="reload_captcha"
                    src="/images/noimg.png"
                    width="95"
                  />
                </v-fade-transition>
              </template>
            </v-text-field>
          </v-col>
          <v-col cols="12">
            <v-checkbox v-model="checked" label="仅在此设备登入"></v-checkbox>
          </v-col>
        </v-row>
      </v-container>

      <div class="d-flex ga-12 justify-center">
        <v-btn
          class="text-primary text-body-2"
          flat
          height="55"
          rounded
          text="登入"
          width="128"
          @click="login"
        />

        <v-btn
          class="text-body-2"
          color="accent"
          flat
          height="55"
          rounded
          width="128"
          @click="reg"
        >
          注册
        </v-btn>
      </div>
    </form>
  </v-sheet>
</template>

<style>
.v-application input:-webkit-autofill,
.v-application textarea:-webkit-autofill,
.v-application select:-webkit-autofill {
  -webkit-transition-delay: 111111s;
  -webkit-transition: color 11111s ease-out, background-color 111111s ease-out;
}

</style>
<script>
import { message } from "ant-design-vue";
import {
  defineComponent,
  reactive,
  ref,
  getCurrentInstance,
  onMounted,
} from "vue";
import Fingerprint2 from "fingerprintjs2";
import md5 from "js-md5";

//要使用jquery，必须修改vite.config.js并把下面两句加上
import jQuery from "jquery";
Object.assign(window, { $: jQuery, jQuery });
//jquery结束

export default defineComponent({
  setup() {
    $cookies.set("selectedkey", "15", "720h");
    $cookies.set("openkey", "");
    const logged = ref(0);
    const activeKey = ref("1");
    const iconLoading = ref(false);
    const show1 = ref(false);
    const murmur = ref("");
    const { proxy } = getCurrentInstance();
    const formState = reactive({
      //熟悉下这里的数据写法
      email: "",
      password: "",
      repeat_password: "",
    });
    const checked = ref(false);
    const onFinish = (values) => {
      console.log("Success:", values);
    };
    const onFinishFailed = (errorInfo) => {
      console.log("Failed:", errorInfo);
    };
    Fingerprint2.get(function (components) {
      const values = components.map(function (component, index) {
        if (index === 0) {
          //把微信浏览器里UA的wifi或4G等网络替换成空,不然切换网络会ID不一样
          return component.value.replace(/\bNetType\/\w+\b/, "");
        }
        return component.value;
      });
      // 生成最终id murmur
      murmur.value = Fingerprint2.x64hash128(values.join(""), 31);
      proxy.$http
        .get("/ajax/captcha_ajax/" + murmur.value, {
          params: {},
          responseType: "blob",
        })
        .then((res) => {
          //必须使用get方法，post会出错
          var src = window.URL.createObjectURL(res.data);
          $(".captchaimg").attr("src", src);
        });
    });
    const reload_captcha = () => {
      proxy.$http
        .get("/ajax/captcha_ajax/" + murmur.value, {
          params: {},
          responseType: "blob",
        })
        .then((res) => {
          //必须使用get方法，post会出错
          var src = window.URL.createObjectURL(res.data);
          $(".captchaimg").attr("src", src);
        });
    };
    const login = () => {
      console.log("login:", formState.email);
      iconLoading.value = true;
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("email", formState.email);
      params.append("password", md5(formState.password));
      params.append("captcha", formState.captcha);
      params.append("exclusive_login", checked.value);
      params.append("hashkey", murmur.value);
      params.append("timestamp", new Date().getTime());
      proxy.$http
        .post("/ajax/login_ajax/", params)
        .then((res) => {
          console.log(res.data)
          iconLoading.value = false;
          message.info(res.data.msg);
          // obj.success ? obj.success(res) : null
          if (res.data.code == "200") {
            $cookies.set("token", res.data.data.token, "720h");
            logged.value = 2;
            window.location.href = "/home";
          } else {
            $cookies.set("token", "", "-720h");
            logged.value = 1;
          }
        })
        .catch((error) => {
          iconLoading.value = false;
          // obj.error ? obj.error(error) : null;
          console.log(error);
        });
    };
    const reg = () => {
      iconLoading.value = true;
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("email", formState.email);
      params.append("password", md5(formState.password));
      //params.append("repeat_password", md5(values.repeat_password));
      params.append("captcha", formState.captcha);
      params.append("hashkey", murmur.value);
      proxy.$http
        .post("/ajax/reg_ajax/", params)
        .then((res) => {
          console.log(res.data);
          iconLoading.value = false;
          message.info(res.data.msg);
          // obj.success ? obj.success(res) : null
          if (res.data.code == "200") {
            $cookies.set("token", res.data.data.token, "720h");
            window.location.href = "/home";
          }
        })
        .catch((error) => {
          iconLoading.value = false;
          // obj.error ? obj.error(error) : null;
          console.log(error);
        });
    };
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
        } else {
          logged.value = 1;
        }
      });
    });
    return {
      login,
      reg,
      murmur,
      reload_captcha,
      formState,
      onFinish,
      iconLoading,
      onFinishFailed,
      checked,
      logged,
      activeKey,
      show1,
    };
  },
});
</script>