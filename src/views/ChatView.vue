<template>
  <h3>ChatGPT</h3>
<p id="chat_url" style="display:none">{{ chat_url }}</p>
  <ul id="content" class="content"></ul>
<br>
  <a-input-search id="msg_input"
    enter-button="发送"
    @search="sendquestion"
  />

</template>
<style scoped>
  .content-dark-theme ul{
    background: #002140;
  }
  .content-light-theme ul{
    background: #fff;
  }
  

.content {
  list-style: none;
  height: 400px;
  margin: 0;
  border: 1px dotted #d1d3d6;
  overflow-y: scroll;
  padding: 0;
}
  /*下面3个类写在style这里不生效，所以直接写在js代码里
.msgContent {
}
.content .left {
}
.content .right {
}
*/
</style>
<script>
import { message } from "ant-design-vue";

import {
  onBeforeUnmount,
  ref,
  onMounted,
  getCurrentInstance,
  shallowRef,
} from "vue";


export default {
  setup() {
    const { proxy } = getCurrentInstance();
    const chat_url = ref(proxy.$remoteDomain+"/ajax/chat_ajax")
    // ajax 异步获取内容
    onMounted(() => {
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      proxy.$http.post("/ajax/note_ajax/", params).then((res) => {
        if (res.data.code == "401") {
          //不在登陆状态
          window.location.href = "/";
        }
      });
    });

    return {
      sendquestion,
      chat_url
    };
  },
};

//要使用jquery，必须修改vite.config.js并把下面两句加上
import jQuery from "jquery";
Object.assign(window, { $: jQuery, jQuery });
//jquery结束

function jsonsafe(str) {
  str = str.replaceAll("\\", "\\\\");
  //str=str.replace('\n','\\n');
  str = str.replaceAll('"', '\\"');
  return str;
}

function sendquestion() {
  if ($("#msg_input").val().length < 3) {
    alert("输入内容至少为3个字符");
  } else {
    var content = $("#msg_input").val();
    $("#content")
      .append('<li class="msgContent right" style="width: auto;  height: auto;  word-break: break-all;  margin: 5px;  padding: 5px;  border-radius: 5px;  font-size: 16px;float: right;  text-align: left;  max-width: 90%;  background-color: yellowgreen;">' + content + "<li>")
      .append('<div style="clear:both"></div>');
      //msgContent right left这3个class不能删
    $("#content").animate({ scrollTop: 999999 }, 600);
    $("#msg_input").val("");
    let queryarr = new Array();
    let historylimit = $(".msgContent").length - 5;
    if (historylimit < 0) {
      historylimit = 0;
    }
    $(".msgContent").each(function (index) {
      //console.log(index);
      if (index >= historylimit) {
        if ($(this).hasClass("right")) {
          queryarr.push(
            '{"role":"user","content":"' + jsonsafe($(this).html()) + '"}'
          );
        } else {
          queryarr.push(
            '{"role":"assistant","content":"' + jsonsafe($(this).html()) + '"}'
          );
        }
      }
    });
    $.post(
      $("#chat_url").html()+"/ajax/chat_ajax",
      //$.post("http://localhost:800/riyutool.com/api/chatgpt.php",
      {
      token:$cookies.get("token"),
      timestamp:new Date().getTime(),
        questions: queryarr.join(",")
      },
      function (data, status) {
        let result_json=JSON.parse(data.data);
        //console.log(result_json);
        //alert("数据: \n" + data + "\n状态: " + status);
        if (data.error) {
          alert(data.error.message);
        } else {
          $("#content")
            .append(
              '<li class="msgContent left" style="width: auto;  height: auto;  word-break: break-all;  margin: 5px;  padding: 5px;  border-radius: 5px;  font-size: 16px;float: left;  text-align: left;  max-width: 90%;  background-color: grey;">' +
                result_json.choices[0].message.content.replace(/\n/gi, "<br>") +
                "<li>"
            )
            .append('<div style="clear:both"></div>');
          $("#content").animate({ scrollTop: 999999 }, 600);
        }
      }
    );
  }
}


</script>
