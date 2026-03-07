<template>
<h3 style="text-align: center;">
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
        :rules="[{ required: true, message: '请选择提醒时间' }]"
      >
        <a-select
          v-model:value="formState.alert"
          mode="multiple"
          placeholder="可多选，选择「不提醒」将清除其它选项"
          style="width: 100%"
          :options="ALERT_OPTIONS"
          @change="onAlertChange"
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
import { defineComponent, ref, reactive, onMounted, onUnmounted, getCurrentInstance } from "vue";
// 在子组件中注入刷新方法
import { inject } from 'vue';

// 提醒选项配置：value 为分钟数，0 表示不提醒
const ALERT_OPTIONS = [
  { value: "0", label: "不提醒" },
  { value: "5", label: "提前5分钟" },
  { value: "10", label: "提前10分钟" },
  { value: "15", label: "提前15分钟" },
  { value: "30", label: "提前30分钟" },
  { value: "60", label: "提前1小时" },
  { value: "120", label: "提前2小时" },
  { value: "1440", label: "提前1天" },
];

// 本地存储 key：待触发的提醒
const REMINDERS_STORAGE_KEY = "calendar_pending_reminders";

/** 将后端返回的 alert 规范化为字符串数组，供多选框使用 */
function normalizeAlertToArray(alert) {
  if (!alert) return ["0"];
  if (Array.isArray(alert)) return alert.map(String).filter(Boolean);
  if (typeof alert === "string") {
    const trimmed = alert.trim();
    if (trimmed.startsWith("[")) {
      try {
        const arr = JSON.parse(trimmed);
        return Array.isArray(arr) ? arr.map(String).filter(Boolean) : ["0"];
      } catch {
        // 解析失败则按逗号分割
      }
    }
    const parts = trimmed.split(/[,，]/).map((s) => s.trim()).filter(Boolean);
    return parts.length > 0 ? parts : ["0"];
  }
  return ["0"];
}

/** 从本地存储读取待提醒列表 */
function loadRemindersFromStorage() {
  try {
    const raw = localStorage.getItem(REMINDERS_STORAGE_KEY);
    return raw ? JSON.parse(raw) : {};
  } catch {
    return {};
  }
}

/** 保存单个事件的提醒到本地存储 */
function saveRemindersToStorage(eventId, { summary, start_time, alert, repetition }) {
  if (!eventId || !summary || !start_time || !alert?.length) return;
  const map = loadRemindersFromStorage();
  map[String(eventId)] = { summary, start_time, alert, repetition };
  localStorage.setItem(REMINDERS_STORAGE_KEY, JSON.stringify(map));
}

/** 从本地存储移除指定事件的提醒 */
function removeRemindersFromStorage(eventId) {
  if (!eventId) return;
  const map = loadRemindersFromStorage();
  delete map[String(eventId)];
  localStorage.setItem(REMINDERS_STORAGE_KEY, JSON.stringify(map));
}

/** 请求浏览器通知权限 */
function requestNotificationPermission() {
  if (!("Notification" in window)) return false;
  if (Notification.permission === "granted") return true;
  if (Notification.permission !== "denied") {
    Notification.requestPermission();
  }
  return false;
}

/** 显示浏览器桌面通知 */
function showReminderNotification(title, body) {
  if (!("Notification" in window) || Notification.permission !== "granted") return;
  try {
    const n = new Notification(title, {
      body,
      icon: "/favicon.ico",
      tag: "calendar-reminder",
    });
    n.onclick = () => {
      window.focus();
      n.close();
    };
    setTimeout(() => n.close(), 8000);
  } catch (e) {
    console.warn("桌面通知失败:", e);
  }
}

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
    // 使用 reactive 确保表单正确响应式，避免嵌套 ref
    const formState = reactive({
      event_id: "",
      summary: "",
      location: "",
      badge_type: "#666",
      repetition: "onetime",
      alert: ["0"],
      is_private: false,
      is_recommend: false,
      start_time: null,
      end_time: null,
      remark: "",
    });

    const { proxy } = getCurrentInstance();
    const updatedDrawerTitle = ref(String);
    const visible = ref(false);
    const event_id = ref(0);
    const drawerclass = ref("");
    const iconLoading = ref(false);
    const datetimeformat = ref("YYYY-MM-DD HH:mm:ss");
    const eventList = ref([]);
    /** 记录上一次提醒选择，用于判断用户最后点击的是「不提醒」还是具体提醒 */
    const prevAlert = ref([]);

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
          formState.event_id = event.id;
          formState.summary = event.summary;
          formState.badge_type = event.badge_type;
          formState.start_time = dayjs(event.start_time, "YYYY-MM-DD HH:mm:ss");
          formState.end_time = dayjs(event.end_time, "YYYY-MM-DD HH:mm:ss");
          formState.repetition = event.repetition;
          formState.location = event.location || "";
          // 规范化 alert：后端可能返回字符串 "10,30" 或数组
          formState.alert = normalizeAlertToArray(event.alert);
          formState.remark = event.remark || "";
          prevAlert.value = [...formState.alert];
        });
      } else {
        formState.event_id = "";
        formState.start_time = dayjs(selectedValue);
        formState.end_time = dayjs(selectedValue);
        formState.summary = "";
        formState.alert = ["0"];
        formState.is_private = false;
        formState.is_recommend = false;
        prevAlert.value = ["0"];
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
    /** 提醒选择变化：选「不提醒」时清除所有提醒；已选不提醒时再选其它提醒则自动去除不提醒 */
    const onAlertChange = (val) => {
      if (!Array.isArray(val)) return;
      const prev = prevAlert.value;
      if (val.includes("0") && val.length > 1) {
        const justAddedZero = !prev.includes("0") && val.includes("0");
        formState.alert = justAddedZero ? ["0"] : val.filter((v) => v !== "0");
      } else if (val.includes("0")) {
        formState.alert = ["0"];
      } else {
        formState.alert = val;
      }
      prevAlert.value = [...formState.alert];
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
    /** 检查并触发到期的提醒，每分钟执行一次 */
    const checkAndFireReminders = () => {
      const map = loadRemindersFromStorage();
      const now = dayjs();
      const toRemove = [];
      const toNotify = [];
      for (const [eid, data] of Object.entries(map)) {
        const start = dayjs(data.start_time);
        const remaining = [];
        for (const minutes of data.alert || []) {
          const remindAt = start.subtract(minutes, "minute");
          if (now.isAfter(remindAt) || now.isSame(remindAt, "minute")) {
            toNotify.push({ summary: data.summary, start });
          } else {
            remaining.push(minutes);
          }
        }
        if (remaining.length === 0) {
          toRemove.push(eid);
        } else {
          map[eid] = { ...data, alert: remaining };
        }
      }
      toRemove.forEach((eid) => delete map[eid]);
      localStorage.setItem(REMINDERS_STORAGE_KEY, JSON.stringify(map));
      toNotify.forEach(({ summary, start }) =>
        showReminderNotification("日历提醒", `${summary}\n开始时间：${start.format("YYYY-MM-DD HH:mm")}`)
      );
    };

    let reminderTimer = null;
    onMounted(() => {
      onPanelChange(selectedValue.value);
      requestNotificationPermission();
      checkAndFireReminders();
      reminderTimer = setInterval(checkAndFireReminders, 60 * 1000); // 每 60 秒检查一次
    });
    onUnmounted(() => {
      if (reminderTimer) clearInterval(reminderTimer);
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
        proxy.$func.getVarType(formState.summary) == "undefined" ||
        formState.summary == ""
      ) {
        message.info("标题不能为空");
        iconLoading.value = false;
      } else {
        // 格式化时间：dayjs 对象转为字符串
        const startStr = dayjs.isDayjs(formState.start_time)
          ? formState.start_time.format("YYYY-MM-DD HH:mm:ss")
          : String(formState.start_time || "");
        const endStr = dayjs.isDayjs(formState.end_time)
          ? formState.end_time.format("YYYY-MM-DD HH:mm:ss")
          : String(formState.end_time || "");
        // 过滤掉 "0"（不提醒），若仅剩 0 则传 ["0"]
        const alertArr = Array.isArray(formState.alert) ? formState.alert : [formState.alert];
        const alertFiltered = alertArr.filter((a) => a !== "0" && a !== "");
        const alertToSend = alertFiltered.length > 0 ? alertFiltered : ["0"];

        let params = new URLSearchParams(); //post内容必须这样传递，不然后台获取不到
        params.append("token", $cookies.get("token"));
        params.append("timestamp", new Date().getTime());
        params.append("event_id", formState.event_id);
        params.append("remark", formState.remark || "");
        params.append("summary", formState.summary);
        params.append("badge_type", formState.badge_type);
        params.append("start_time", startStr);
        params.append("end_time", endStr);
        params.append("repetition", formState.repetition);
        // 后端通常接收逗号分隔字符串，如 "10,30,60"
        params.append("alert", alertToSend.join(","));
        params.append("location", formState.location || "");

        proxy.$http
          .post("/ajax/save_event_ajax/", params)
          .then((res) => {
            // 保存成功后，将有效提醒写入本地，供定时器触发浏览器通知
            const savedEventId = formState.event_id || res.data?.data?.event_id || res.data?.event_id;
            if (alertToSend.length > 0 && alertToSend[0] !== "0" && savedEventId) {
              saveRemindersToStorage(savedEventId, {
                summary: formState.summary,
                start_time: startStr,
                alert: alertToSend.map(Number),
                repetition: formState.repetition,
              });
            } else if (savedEventId) {
              removeRemindersFromStorage(savedEventId);
            }
            if (res.data.msg == "新建成功") {
              message.info("添加事件成功");
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
          removeRemindersFromStorage(formState.event_id);
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
      ALERT_OPTIONS,
      onAlertChange,
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
