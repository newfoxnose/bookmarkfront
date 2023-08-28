<script>
import { message } from 'ant-design-vue';
import { defineComponent } from 'vue';

export default defineComponent({
  mounted() {
    let params = new URLSearchParams();    //post内容必须这样传递，不然后台获取不到
    params.append("teacher_id", $cookies.get('teacher_id'));
    params.append("level", $cookies.get('level'));
    const { data: res } = this.$http.post('/ajax/note_ajax', params)
      .then(res => {
        // obj.success ? obj.success(res) : null
        if (res.data.msg == "请求成功") {
          var pattern_En = new RegExp("[0-9a-z]");
          if (pattern_En.test(res.data.data.domain)) {
            window.location.href = "http://" + res.data.data.domain + ".gm.ws"
          }
          else {
            window.location.href = res.data.data.domain;
          }
        }
        else {
          message.info("请求失败");
        }
        //console.log(res.data);
      })
      .catch(error => {
        // obj.error ? obj.error(error) : null;
        console.log(error);
      })

  }
});
</script>