import { createApp } from "vue";
import Antd from "ant-design-vue"; //导入ant design，全局注册
import App from "./App.vue";
import "ant-design-vue/dist/antd.css"; //for ant design
import router from "./router";
import VueCookies from "vue-cookies";

import "./assets/main.css";

/* 导入axios进行全局配置 */
import axios from "axios";

//导入自定义的全局函数
import func from '@/assets/func'

//导入ueditor
import VueUeditorWrap from 'vue-ueditor-wrap';

const app = createApp(App);

/* 配置请求的根路径，远程后端 */
axios.defaults.baseURL = "https://bm.o-oo.net.cn";
//axios.defaults.baseURL = "https://bm.gm.ws";
//axios.defaults.baseURL = "http://bookmark.com";

axios.defaults.timeout = 120000;   //axios全局超时时间，单位毫秒，120000等于2分钟

/* 将 axios 挂载到全局，今后，每个组件中，
都可以直接通过this.$http 代替 axios 发起 Ajax 请求 */
app.config.globalProperties.$http = axios;
app.config.globalProperties.$cookies = VueCookies; //全局挂载cookies

app.config.globalProperties.$func = func;//全局挂载自定义全局函数

app.config.globalProperties.$remoteDomain = axios.defaults.baseURL    //全局定义远程域名

app.use(router);
app.use(VueUeditorWrap);  //ueditor
app.use(Antd); //for ant design

app.mount("#app");
