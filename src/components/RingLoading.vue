<template>
  <div class="loadingbar" v-show="!done">
    <a-progress
      type="circle"
      :percent="percent"
      status="active"
      :show-info="false"
      :stroke-color="{
        '0%': '#108ee9',
        '100%': '#87d068',
      }"
    />
  </div>
</template>

<script>
// 全站共用的环形加载进度条
// 用法：<RingLoading v-model:done="loadingdone" />
// done 为 false 时显示并自动推进进度，父组件把 done 置为 true 即结束；
// 全局请求失败（断网/超时/服务器错误）时由 main.js 的 axios 拦截器广播
// request-error 事件，本组件监听后自动结束并同步 done，避免进度条卡住。
import { ref, watch, onUnmounted } from "vue";

export default {
  name: "RingLoading",
  props: {
    done: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["update:done"],
  setup(props, { emit }) {
    const percent = ref(10);
    let timer = null;

    const stop = () => {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    };
    const start = () => {
      stop();
      percent.value = 10;
      timer = setInterval(() => {
        const next = percent.value + Math.round(Math.random() * 7 + 2);
        percent.value = next > 95 ? 95 : next;
        if (percent.value > 90) {
          stop();
        }
      }, 100);
    };

    watch(
      () => props.done,
      (val) => {
        if (val) {
          stop();
        } else {
          start();
        }
      },
      { immediate: true }
    );

    // 请求失败时自动结束进度条，并同步父组件的 done 状态
    const onRequestError = () => {
      stop();
      emit("update:done", true);
    };
    window.addEventListener("request-error", onRequestError);
    onUnmounted(() => {
      stop();
      window.removeEventListener("request-error", onRequestError);
    });

    return {
      percent,
    };
  },
};
</script>
