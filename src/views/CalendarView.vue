<template>
  <h3 class="content-title">日历<br><br>
  <a-button type="primary" @click="showDrawer('添加事件', selectedValue)"
    ><form-outlined />添加事件</a-button
  ></h3>
  <a-calendar
    v-model:value="value"
    @select="onSelect"
    @panelChange="onPanelChange"
  >
    <template #dateCellRender="{ current }">
      <ul class="events">
        <li
          v-for="item in getListData(current)"
          :key="item.content"
          @click="showDrawer('编辑事件', selectedValue, item.event_id)"
        >
          <a-badge :color="item.badge_type" :text="item.content" />
        </li>
      </ul>
    </template>
    <template #monthCellRender="{ current }">
      <div v-if="getMonthData(current)" class="notes-month">
        <section>{{ getMonthData(current) }}</section>
        <span>Backlog number</span>
      </div>
    </template>
  </a-calendar>

  <a-drawer
    :width="500"
    :height="450"
    :title="updatedDrawerTitle"
    placement="bottom"
    :visible="visible"
    :class="drawerclass"
    @close="onClose"
  >
    <template #extra>
      <a-button type="primary" @click="save()" :loading="iconLoading"
        >保存</a-button
      >
      &nbsp;
      <a-button @click="onClose">取消</a-button>
      &nbsp;
      <a-button type="danger" @click="deleteEvent()">删除</a-button>
    </template>

    <a-form :model="formState">
      <a-form-item
        label="事件标题"
        name="summary"
        :rules="[{ required: true, message: '请输入事件标题' }]"
      >
        <a-input v-model:value="formState.summary" />
      </a-form-item>
      <a-form-item
        label="颜色"
        name="badge_type"
        :rules="[{ required: true, message: '颜色' }]"
      >
        <a-radio-group v-model:value="formState.badge_type">
          <a-radio value="#666"
            ><a-badge color="#666" class="big-dot"
          /></a-radio>
          <a-radio value="red"><a-badge color="red" class="big-dot" /></a-radio>
          <a-radio value="orange">
            <a-badge color="orange" class="big-dot"
          /></a-radio>
          <a-radio value="green"
            ><a-badge color="green" class="big-dot"
          /></a-radio>
          <a-radio value="blue"
            ><a-badge color="blue" class="big-dot"
          /></a-radio>
          <a-radio value="purple"
            ><a-badge color="purple" class="big-dot"
          /></a-radio>
        </a-radio-group>
      </a-form-item>

      <a-row>
        <a-col :span="12">
          <a-form-item
            label="开始时间"
            name="start_time"
            :rules="[{ required: true, message: '开始时间' }]"
          >
            <a-date-picker
              v-model:value="formState.start_time"
              show-time
              :format="datetimeformat"
            /> </a-form-item
        ></a-col>

        <a-col :span="12">
          <a-form-item
            label="结束时间"
            name="end_time"
            :rules="[{ required: true, message: '结束时间' }]"
          >
            <a-date-picker
              v-model:value="formState.end_time"
              show-time
              :format="datetimeformat"
            /> </a-form-item
        ></a-col>
      </a-row>

      <a-row>
        <a-col :span="12">
          <a-form-item
            label="重复"
            style="margin-right: 20px"
            name="repetition"
            :rules="[{ required: true, message: '重复' }]"
          >
            <a-select ref="select" v-model:value="formState.repetition">
              <a-select-option value="onetime">一次性活动</a-select-option>
              <a-select-option value="everyday">每天</a-select-option>
              <a-select-option value="workday">周一至周五</a-select-option>
            </a-select>
          </a-form-item></a-col
        >
        <a-col :span="12"
          ><a-form-item label="地点" name="location">
            <a-input v-model:value="formState.location" />
          </a-form-item>
        </a-col>
      </a-row>
      <a-form-item
        label="提醒"
        name="alert"
        :rules="[{ required: true, message: '提醒' }]"
      >
        <a-select
          v-model:value="formState.alert"
          mode="multiple"
          style="width: 100%"
          :options="[
            { value: '0', label: '不提醒' },
            { value: '10', label: '提前10分钟' },
            { value: '15', label: '提前15分钟' },
            { value: '30', label: '提前30分钟' },
            { value: '60', label: '提前1小时' },
            { value: '1440', label: '提前1天' },
          ]"
        ></a-select>
      </a-form-item>

      <a-form-item label="备注" name="remark">
        <a-input v-model:value="formState.remark" />
      </a-form-item>
    </a-form>
  </a-drawer>
</template>
<style scoped>
.events {
  list-style: none;
  margin: 0;
  padding: 0;
}
.events .ant-badge-status {
  overflow: hidden;
  white-space: nowrap;
  width: 100%;
  text-overflow: ellipsis;
  font-size: 12px;
}
.notes-month {
  text-align: center;
  font-size: 28px;
}
.notes-month section {
  font-size: 28px;
}
</style>
<script>
import { message, Modal } from "ant-design-vue";
import {
  InboxOutlined,
  EyeInvisibleTwoTone,
  LikeTwoTone,
  StarOutlined,
  FormOutlined,
} from "@ant-design/icons-vue";
import dayjs from "dayjs";
import { defineComponent, ref, onMounted, getCurrentInstance } from "vue";
// 在子组件中注入刷新方法
import { inject } from 'vue';

export default defineComponent({
  components: {
    InboxOutlined,
    EyeInvisibleTwoTone,
    LikeTwoTone,
    StarOutlined,
    FormOutlined,
  },
  setup() {
    $cookies.set("selectedkey", "16", "720h");
    $cookies.set("openkey", "");
    const formState = ref([]);
    formState.value.summary = "";
    formState.value.location = "";
    formState.value.badge_type = "#666";
    formState.value.repetition = ref("onetime");
    formState.value.alert = ref(["0"]);
    formState.value.is_private = false;
    formState.value.is_recommend = false;

    const { proxy } = getCurrentInstance();
    const updatedDrawerTitle = ref(String);
    const visible = ref(false);
    const event_id = ref(0);
    const drawerclass = ref("");
    const iconLoading = ref(false);
    const datetimeformat = ref("YYYY-MM-DD HH:mm:ss");
    const eventList = ref([]);

    const reloadtodo = inject('reloadtodo');   //注入刷新方法

    const showDrawer = (drawerTitle, selectedValue, id) => {
      drawerclass.value = "drawer-" + $cookies.get("theme") + "-theme";
      visible.value = true;
      updatedDrawerTitle.value = drawerTitle;

      if (id != null) {
        event_id.value = id;
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("event_id", event_id.value);
        proxy.$http.post("/ajax/get_event_ajax/", params).then((res) => {
          console.log(res.data.data);
          let event = res.data.data.event;
          formState.value.event_id = event.id;
          formState.value.summary = event.summary;
          formState.value.badge_type = event.badge_type;
          formState.value.start_time = ref(
            dayjs(event.start_time, "YYYY-MM-DD HH:mm:ss")
          );
          formState.value.end_time = ref(
            dayjs(event.end_time, "YYYY-MM-DD HH:mm:ss")
          );
          formState.value.repetition = event.repetition;
          formState.value.location = event.location;
          formState.value.alert = event.alert;
          formState.value.remark = event.remark;
        });
      } else {
        formState.value.event_id = "";
        formState.value.start_time = ref(
          dayjs(selectedValue, "YYYY-MM-DD HH:mm:ss")
        );
        formState.value.end_time = ref(
          dayjs(selectedValue, "YYYY-MM-DD HH:mm:ss")
        );
        formState.value.summary = "";
        formState.value.is_private = false;
        formState.value.is_recommend = false;
      }
    };

    const showconfirmdelete = (editId, currentpage) => {
      Modal.confirm({
        title: "确认删除该项目吗？",
        content: "点击OK删除且无法找回, 点击cancel取消",
        onOk() {
          deletepost(editId, currentpage);
        },
        // eslint-disable-next-line @typescript-eslint/no-empty-function
        onCancel() {},
      });
    };
    const onClose = () => {
      iconLoading.value = false;
      visible.value = false;
      event_id.value = 0;
    };

    const value = ref();

    const selectedValue = ref(dayjs(proxy.$func.current_date_time()));
    console.log(proxy.$func.current_date_time());
    const onSelect = (value) => {
      selectedValue.value = value;
      console.log("select" + dayjs(value).format("YYYY-MM-DD HH:mm:ss"));
    };
    const onPanelChange = (value) => {
      console.log("PanelChanged的参数是：" + value);
      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("yearmonth", value.format("YYYY-MM"));
      proxy.$http.post("/ajax/list_event_ajax/", params).then((res) => {
        console.log(res.data.data.event);
        eventList.value = res.data.data.event;
        //getListData(value)
      });
    };
    onMounted(() => {
      onPanelChange(selectedValue.value);
    });
    const getListData = (value) => {
      console.log("事件数量：" + eventList.value.length);
      let listData = [];
      //console.log(value.format("MM-DD"));
      for (let i = 0; i < eventList.value.length; i++) {
        if (
          dayjs(eventList.value[i]["execute_time"]).format("MM-DD") ==
          value.format("MM-DD")
        ) {
          listData.push({
            badge_type: eventList.value[i]["badge_type"],
            content: eventList.value[i]["summary"],
            event_id: eventList.value[i]["id"],
          });
        }
      }
      return listData || [];
    };
    const getMonthData = (value) => {
      console.log(value.month());
    };
    const save = () => {
      iconLoading.value = true;
      if (
        proxy.$func.getVarType(formState.value.summary) == "undefined" ||
        formState.value.summary == ""
      ) {
        message.info("标题不能为空");
        iconLoading.value = false;
      } else {
        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("event_id", formState.value.event_id);
        params.append("remark", formState.value.remark);
        params.append("summary", formState.value.summary);
        params.append("badge_type", formState.value.badge_type);
        params.append("start_time", formState.value.start_time);
        params.append("end_time", formState.value.end_time);
        params.append("repetition", formState.value.repetition);
        params.append("alert", formState.value.alert);
        params.append("location", formState.value.location);

        proxy.$http
          .post("/ajax/save_event_ajax/", params)
          .then((res) => {
            //console.log(res.data.msg);
            if (res.data.msg == "新建成功") {
              //这条提示如果改的话要和后端一起改
              message.info("添加事件成功"); //看不到效果
              iconLoading.value = false;
            } else {
              message.info(res.data.msg);
              iconLoading.value = false;
            }
            onClose();
            onPanelChange(selectedValue.value);
            reloadtodo();   //调用app.vue里的刷新方法
          })
          .catch((error) => {
            message.info("无法正常保存");
            iconLoading.value = false;
            console.log(error);
          });
      }
    };

    const deleteEvent = () => {
      iconLoading.value = true;

      let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
      params.append("token", $cookies.get("token"));
      params.append("timestamp", new Date().getTime());
      params.append("event_id", formState.value.event_id);
      proxy.$http
        .post("/ajax/delete_event_ajax/", params)
        .then((res) => {
          //console.log(res.data.msg);
          message.info(res.data.msg);
          iconLoading.value = false;
          onClose();
          onPanelChange(selectedValue.value);
          reloadtodo();   //调用app.vue里的刷新方法
        })
        .catch((error) => {
          message.info("无法正常删除");
          iconLoading.value = false;
          console.log(error);
        });
    };
    return {
      value,
      getListData,
      getMonthData,
      selectedValue,
      onSelect,
      onPanelChange,

      iconLoading,
      formState,
      visible,
      showDrawer,
      onClose,
      updatedDrawerTitle,
      event_id,
      showconfirmdelete,
      drawerclass,
      datetimeformat,
      save,
      eventList,
      deleteEvent,
    };
  },
});

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
</script>
