<script>
import { message } from 'ant-design-vue';
import { onMounted,getCurrentInstance } from 'vue';
export default ({
  setup() {
    $cookies.set('selectedkey','14',"720h") 
    $cookies.set('openkey','sub1',"720h") 
    const { proxy } = getCurrentInstance()
    onMounted(() => {
      let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get('token'));
      params.append("timestamp",new Date().getTime());
      proxy.$http.post('/ajax/logout_ajax/', params).then(res => {
        message.info("已退出");
        console.log(res.data);
      });
      $cookies.set("token", "", "-720h");
      window.location.href = "/";
    })
  }
});
</script>