import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import './assets/main.css'

 
/* 导入axios进行全局配置 */
import axios from 'axios'


const app = createApp(App)

 
/* 配置请求的根路径 */
axios.defaults.baseURL='http://bookmark.com'
 
/* 将 axios 挂载到全局，今后，每个组件中，
都可以直接通过this.$http 代替 axios 发起 Ajax 请求 */
app.config.globalProperties.$http=axios



app.use(router)

app.mount('#app')
