<script setup>
import bookmarkitem from './bookmarkitem.vue'
import subfolder from './subfolder.vue'
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
      editable:'no',
      answer: 'Questions usually contain a question mark. ;-)'
    }
  },
  watch: {
    // 每当 question 改变时，这个函数就会执行
    question(newQuestion, oldQuestion) {
      this.search = newQuestion
      if (newQuestion.includes('?')) {
        this.getAnswer()
      }
    }
  },
  methods: {
    async getAnswer() {
      this.answer = 'Thinking...'
      try {
        this.items.forEach(function (item) {

        })
        const res = await fetch('https://yesno.wtf/api')
        this.answer = (await res.json()).answer

      } catch (error) {
        this.answer = 'Error! Could not reach the API. ' + error
      }
    },
    clearQuestion() {
      this.question = ''
      this.search = ''
    }
  },
  async mounted() {
    const { data: res } = await this.$http.get('/index/home/338')
    console.log(res)
    this.items = res.data
  },
  defineComponent() {
    components: {
      CloseOutlined
    }
  },
  setup() {
    const userName = ref < string > ('');
    return {
      userName,
    };
  },
}

</script>
<template>
  <div>
    <bookmarkitem v-for="bookmarkitem in items.root_bookmarks" :url="bookmarkitem.url" :title="bookmarkitem.title"
      :short_title="bookmarkitem.short_title" :icon="bookmarkitem.icon_display" :search="search" :editable="editable" />
  </div>
  <div v-for="item in items.folder">
      <subfolder :folder_name="item.folder_name" :folder_bookmark="item.bookmarks" :subfolder="item.subfolder"
        :search="search" :editable="editable">
      </subfolder>
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


<style scoped>
.folder-name {
  margin-top: 2rem;
  display: flex;
}

.folder-name i {
  display: flex;
  place-items: center;
  place-content: center;
  width: 32px;
  height: 32px;
  color: var(--color-text);
}

@media (min-width: 1024px) {
  .folder-name {
    margin-top: 0;
    padding: 0.4rem 0 1rem calc(var(--section-gap) / 2);
  }

  .folder-name i {
    top: calc(50% - 25px);
    left: -26px;
    position: absolute;
    border: 1px solid var(--color-border);
    background: var(--color-background);
    border-radius: 8px;
    width: 80px;
    height: 50px;
  }

  .folder-name:before {
    content: ' ';
    border-left: 1px solid var(--color-border);
    position: absolute;
    left: 0;
    bottom: calc(50% + 25px);
    height: calc(50% - 25px);
  }

  .folder-name:after {
    content: ' ';
    border-left: 1px solid var(--color-border);
    position: absolute;
    left: 0;
    top: calc(50% + 25px);
    height: calc(50% - 25px);
  }

  .folder-name:first-of-type:before {
    display: none;
  }

  .folder-name:last-of-type:after {
    display: none;
  }
}


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

.input {}
</style>
