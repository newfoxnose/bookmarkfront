<script>
import { message } from 'ant-design-vue';
import { onMounted, getCurrentInstance } from 'vue';

export default {
  setup() {
    const { proxy } = getCurrentInstance();

    // 设置 cookies
    $cookies.set('selectedkey', '14', "720h");
    $cookies.set('openkey', 'sub1', "720h");

    onMounted(() => {
      let params = new URLSearchParams(); // post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp", new Date().getTime());

      // 进行 POST 请求
      proxy.$http.post('/ajax/logout_ajax/', params)
        .then(res => {
          message.info("已退出");
          console.log(res.data);
          // 成功后再清除 token
          $cookies.set("token", "", "-720h");
          window.location.href = "/";
        })
        .catch(err => {
          message.error("退出失败，请重试");
          console.error(err);
        });
    });
  }
}
</script>
