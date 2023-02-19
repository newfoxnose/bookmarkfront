<script setup>
import WelcomeItem from './WelcomeItem.vue'
import bookmarkitem from './bookmarkitem.vue'
import subfolder from './subfolder.vue'
import DocumentationIcon from './icons/IconDocumentation.vue'
import ToolingIcon from './icons/IconTooling.vue'
import EcosystemIcon from './icons/IconEcosystem.vue'
import CommunityIcon from './icons/IconCommunity.vue'
import SupportIcon from './icons/IconSupport.vue'
</script>
 
<script>
export default {
  data() {
    return {
      items: {}
    }
  },
  async mounted() {
    const { data: res } = await this.$http.get('/index/')
    console.log(res)
    this.items = res.data
  }
}
</script>


<template>
  <div v-for="item in items">
    <WelcomeItem>
      <template #icon>
        {{ item.folder_name }}
      </template>
    </WelcomeItem>
    <bookmarkitem v-for="bookmarkitem in item.bookmark" :url="bookmarkitem.url" :title="bookmarkitem.title"
      :icon="bookmarkitem.icon" />
    <div v-for="sub_item in item.sub_folder">
      <subfolder :sub_folder_name="sub_item.sub_folder_name" :sub_folder_bookmark="sub_item.sub_folder_bookmark">
      </subfolder>
    </div>
  </div>
</template>
