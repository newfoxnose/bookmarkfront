<script>
import {  getCurrentInstance, ref } from "vue";
import { useRouter } from "vue-router";
export default {
  setup() {
    $cookies.set('selectedkey','1',"720h") 
    $cookies.set('openkey','') 
    const { proxy } = getCurrentInstance();
    const router = useRouter();
    return {
      go_url: ref(proxy.$remoteDomain + "/go/"+router.currentRoute.value.params.id),
    };
  }
};
</script>
<template>
  <iframe
    :src="go_url"
    title="External Content"
    width="100%"
    height="100%"
    frameborder="0"
    allowfullscreen
  >
  </iframe>
</template>