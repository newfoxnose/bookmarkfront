<script>
import {
  LinkOutlined,
} from "@ant-design/icons-vue";
import {
  defineComponent,
  ref,
  reactive,
  onMounted,
  getCurrentInstance,
} from "vue";
import { message } from "ant-design-vue";
import ScrollReveal from "scrollreveal";      //如果使用import ScrollReveal from "scrollReveal"就会无法部署到netlify，说明netlify对大小写敏感，而本机windows系统则不敏感

export default defineComponent({
  components: {
    LinkOutlined,
  },
  setup() {
    const { proxy } = getCurrentInstance();

    onMounted(() => {
      ScrollReveal().reveal(".introrow", {
        reset: true,
        distance: "50px",
        origin: "left",
        interval: 80,
        opacity: 0.1,
        rotate: {
          x: 20,
          z: 20,
        },
        scale: 0.6,
      });
      
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());

      proxy.$http.post("/ajax/get_folder_ajax/", params).then((folder_res) => {
        if (folder_res.data.code == "200") {
          //在登陆状态就跳转到home页
          window.location.href = "/home";
        }
      });
    });

    return {
    };
  },
});
</script>
 
<template>

  <a-carousel class="carousel-banner" autoplay>
    <div><h3>全面的书签管理功能</h3></div>
    <div><h3>接入七牛云作为网盘</h3></div>
    <div><h3>自动生成个人博客网站</h3></div>
    <div><h3>整合AI对话功能</h3></div>
  </a-carousel>

  <a-row class="introrow">
    <a-col :sm="24" :md="12">
      <a-carousel autoplay effect="fade">
        <div><a-image src="images/screenshot/ss1-1.png" style="border-radius: 10px;border: 2px solid #ccc;" /></div>
        <div><a-image src="images/screenshot/ss1-2.png" style="border-radius: 10px;border: 2px solid #ccc;"/></div>
      </a-carousel>
    </a-col>
    <a-col :sm="24" :md="12" class="screenshotintroparent">
      <div class="screenshotintrochild">
        <a-typography-title :level="3">功能齐全的书签站</a-typography-title>
        <a-typography-paragraph>
          在线添加、编辑、删除书签，支持根据汉字、拼音、网址关键字快速搜索书签。<a-typography-text mark>自动定期检查死链</a-typography-text>。支持保存网页快照。
        </a-typography-paragraph>
        <a-typography-paragraph>
          支持各种浏览器收藏夹备份文件的
          <a-typography-text mark>导入、导出</a-typography-text>，同时支持
          <a-typography-text mark>备份收藏夹至邮箱</a-typography-text>。
        </a-typography-paragraph>
      </div>
    </a-col>
  </a-row>

  <a-row class="introrow">
    <a-col :sm="24" :md="12" class="screenshotintroparent">
      <div class="screenshotintrochild">
        <a-typography-title :level="3">随手记</a-typography-title>
        <a-typography-paragraph>
          适合记录随时产生的灵感和信息。
        </a-typography-paragraph>
        <a-typography-paragraph>
          使用广受好评的<a href="https://www.wangeditor.com/" target="_blank"
            ><link-outlined />wangEditor 5</a
          >所见即所得富文本编辑器，可完成内容丰富的排版。
        </a-typography-paragraph>
      </div>
    </a-col>
    <a-col :sm="24" :md="12">
      <a-carousel autoplay effect="fade">
        <div><a-image src="images/screenshot/ss2-1.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
      </a-carousel>
    </a-col>
  </a-row>

  <a-row class="introrow">
    <a-col :sm="24" :md="12">
      <a-carousel autoplay effect="fade">
        <div><a-image src="images/screenshot/ss3-1.png"   style="border-radius: 10px;border: 2px solid #ccc;" /></div>
      </a-carousel>
    </a-col>
    <a-col :sm="24" :md="12" class="screenshotintroparent">
      <div class="screenshotintrochild">
        <a-typography-title :level="3">七牛云文件管理</a-typography-title>
        <a-typography-paragraph>
          可以上传各种文件用于长期、短期存储、下载，或者分享给朋友。
        </a-typography-paragraph>
        <a-typography-paragraph>
          支持在线预览<a-typography-text mark
            >DOC、XLS、PPT、PDF、TXT</a-typography-text
          >等文件。
        </a-typography-paragraph>
      </div>
    </a-col>
  </a-row>

  <a-row class="introrow">
    <a-col :sm="24" :md="12" class="screenshotintroparent">
      <div class="screenshotintrochild">
        <a-typography-title :level="3">在线AI对话</a-typography-title>
        <a-typography-paragraph>
          在线AI助理，可以实现翻译、问答、写作等各种功能。
        </a-typography-paragraph>
      </div>
    </a-col>
    <a-col :sm="24" :md="12">
      <a-carousel autoplay effect="fade">
        <div><a-image src="images/screenshot/ss4-1.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
      </a-carousel>
    </a-col>
  </a-row>

  <a-row class="introrow">
    <a-col :sm="24" :md="12">
      <a-carousel autoplay effect="fade">
        <div><a-image src="images/screenshot/ss5-1.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
        <div><a-image src="images/screenshot/ss5-2.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
        <div><a-image src="images/screenshot/ss5-3.png" style="border-radius: 10px;border: 2px solid #ccc;" /></div>
      </a-carousel>
    </a-col>
    <a-col :sm="24" :md="12" class="screenshotintroparent">
      <div class="screenshotintrochild">
        <a-typography-title :level="3">RSS阅读器</a-typography-title>
        <a-typography-paragraph>
          可以订阅符合<a-typography-text mark
            >RSS1.0、RSS2.0、ATOM</a-typography-text
          >标准的海量RSS资源，并在线阅读。
        </a-typography-paragraph>
      </div>
    </a-col>
  </a-row>


  <a-row class="introrow">
    <a-col :sm="24" :md="12" class="screenshotintroparent">
      <div class="screenshotintrochild">
        <a-typography-title :level="3">博客</a-typography-title>
        <a-typography-paragraph>
          整合个人博客系统，记录进步的每一天，可复制粘贴带格式内容，图片自动转存到七牛云。
        </a-typography-paragraph>
      </div>
    </a-col>
    <a-col :sm="24" :md="12">
      <a-carousel autoplay effect="fade">
        <div><a-image src="images/screenshot/ss6-1.png" style="border-radius: 10px;border: 2px solid #ccc;" /></div>
        <div><a-image src="images/screenshot/ss6-2.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
      </a-carousel>
    </a-col>
  </a-row>

  
  <a-row class="introrow">
    <a-col :sm="24" :md="12">
      <a-carousel autoplay effect="fade">
        <div><a-image src="images/screenshot/ss7-1.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
        <div><a-image src="images/screenshot/ss7-2.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
        <div><a-image src="images/screenshot/ss7-3.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
        <div><a-image src="images/screenshot/ss7-4.png"  style="border-radius: 10px;border: 2px solid #ccc;"/></div>
      </a-carousel>
    </a-col>
    <a-col :sm="24" :md="12" class="screenshotintroparent">
      <div class="screenshotintrochild">
        <a-typography-title :level="3">自动生成个人网站</a-typography-title>
        <a-typography-paragraph>
          状态为公开的博客、书签、图片等内容会被自动整合到一个个人网站，并自动发布。
        </a-typography-paragraph>
        <a-typography-paragraph>
          可<a-typography-text mark
            >自定义域名</a-typography-text
          >，或使用免费域名，提供25种配色方案。
        </a-typography-paragraph>
      </div>
    </a-col>
  </a-row>

  <div style="margin-bottom: 20px">&nbsp;</div>
</template>

<style scoped>
.carousel-banner :deep(.slick-slide) {
  margin-top:20px;
  text-align: center;
  height: 200px;
  line-height: 200px;
  background: #364d79;
  overflow: hidden;
}

.carousel-banner :deep(.slick-slide h3) {
  color: #fff;
  font-size:16px;
}

.introrow {
  margin-top: 30px;
}

.screenshotintroparent {
  display: grid;
  place-items: center;
}
.screenshotintrochild {
  padding: 20px;
}

.screenshotintrochild h3{
  padding-bottom:3px;
  border-bottom-style: solid;
  border-bottom-width: medium;
  border-bottom-color:cadetblue;
}

</style>
