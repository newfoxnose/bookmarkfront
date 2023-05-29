<script setup>
import bookmarkitem from './bookmarkitem.vue'
import { defineComponent, ref } from 'vue';
import {
  CloseOutlined,
} from '@ant-design/icons-vue';

</script>
 
<script>
export default {
  data() {
    return {
      items: {},
      question: '',
      search: '',
      editable:'no'
    }
  },
  watch: {
    // 每当 question 改变时，这个函数就会执行
    question(newQuestion, oldQuestion) {
      this.search = newQuestion
    }
  },
  methods: {
    clearQuestion() {
      this.question = ''
      this.search = ''
    }
  },
  async mounted() {
    console.log("mounted")
    const { data: res } = await this.$http.get('/ajax/index_ajax')
    this.items = res.data
    console.log(this.items)
    console.log("ajax requested")
  },
  defineComponent() {
    components: {
      CloseOutlined
    }
  },
}

</script>
<template>
   <h3 style="margin-top:15px;">随机公开书签</h3>
   <li v-for="item in items.root_bookmarks">
  {{ item.id }}
</li>
  <div>
    <bookmarkitem v-for="bookmarkitem in items.root_bookmarks" :id="bookmarkitem.id" :folder_id="bookmarkitem.folder_id"
      :url="bookmarkitem.url" :title="bookmarkitem.title" :short_title="bookmarkitem.short_title"
      :is_private="bookmarkitem.is_private" :icon="bookmarkitem.icon_display" :search="search" :editable="editable"></bookmarkitem>

  </div>
  <div style="margin-bottom:20px;">
  </div>

  <div class="search-div">
    <div class="search">

      <a-input v-model:value="question">
        <template #addonAfter>
          <close-outlined @click="clearQuestion" />
        </template>
      </a-input>

    </div>
  </div>
</template>


<style>


.search-div {
  display: flex;
  flex-direction: column;
}

.search {
  align-self: center;
  position: fixed;
  bottom: 0;
  z-index: 2;
  display: inline;
  width: 200px;
}

.search-div button {
  height: 34px !important;
}

#search {
  border-color: #4cae4c;
  border-width: 2px;
}

</style>
