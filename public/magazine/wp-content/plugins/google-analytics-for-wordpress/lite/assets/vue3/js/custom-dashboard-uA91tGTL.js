const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./chunks/DashboardList.lite-B4-WM5Nn.js","./chunks/TheAppHeader-Dc7mNmwW.js","./chunks/AppOverlays-5d5Sx5mz.js","../css/main-monsterinsights-BqoTIBUO.css","./chunks/ajax-pmt98pQN.js","./chunks/useSampleData-CwRuQHiS.js","./chunks/default-i18n-KrIlCc2E.js","./chunks/Modal-CN0naihT.js","../css/main-monsterinsights-DRT55mf0.css","./chunks/Icon-CyTPFQ-E.js","../css/main-monsterinsights-DdCOUrGJ.css","./chunks/license-CZe4-J_K.js","./chunks/useNotices-ooYBwYj4.js","../css/main-monsterinsights-tn0RQdqM.css","./chunks/useFeatureGate-B3uCywkg.js","./chunks/DashboardCreate-BABsa6oP.js","./chunks/ErrorModal-DAEfhL_t.js","./chunks/useChartColors-Bi1Kbjjv.js","./chunks/LoadingSpinnerInline-D6gqDANX.js","../css/main-monsterinsights-CKJSBouy.css","./chunks/vue3-apexcharts-Drwy2afu.js","../css/main-monsterinsights-DcECXQxs.css","./chunks/ApexBarChart-BH56MIwD.js","../css/main-monsterinsights-DAYzOZ5f.css","./chunks/dateIntervals-BPoui_3H.js","./chunks/useAuthGate-BPBSyEmc.js","./chunks/flatpickr-Bqky3kHj.js","../css/main-monsterinsights-CMSe2SYd.css","../css/main-monsterinsights-h4_uXCXe.css","../css/main-monsterinsights-gsHLWIGr.css","./chunks/ReAuthModal-BAFYHRau.js","./chunks/auth-BTdxFMcG.js","../css/main-monsterinsights-BVgKjrnK.css","../css/main-monsterinsights-C_BN-_xE.css","./chunks/DashboardEdit-C6ZV2SjO.js","./chunks/DashboardView-DC_5fLx9.js"])))=>i.map(i=>d[i]);
import { c as createElementBlock, a as createBaseVNode, b as createVNode, r as resolveComponent, o as openBlock, _ as _sfc_main$2, d as createApp, i as installOverlays } from "./chunks/AppOverlays-5d5Sx5mz.js";
import { _ as _sfc_main$1, c as createRouter, a as createWebHashHistory } from "./chunks/TheAppHeader-Dc7mNmwW.js";
import { _ as __vitePreload, s as setupPinia } from "./chunks/ajax-pmt98pQN.js";
import "./chunks/default-i18n-KrIlCc2E.js";
const _hoisted_1 = { class: "mi-custom-dashboard-app monsterinsights-app-surface" };
const _hoisted_2 = { id: "monsterinsights-app" };
const _hoisted_3 = { class: "monsterinsights-dashboard-content" };
const _hoisted_4 = { class: "monsterinsights-container" };
const _sfc_main = {
  __name: "App",
  setup(__props) {
    return (_ctx, _cache) => {
      const _component_RouterView = resolveComponent("RouterView");
      return openBlock(), createElementBlock("div", _hoisted_1, [
        createBaseVNode("div", _hoisted_2, [
          createBaseVNode("div", _hoisted_3, [
            createVNode(_sfc_main$1),
            createBaseVNode("div", _hoisted_4, [
              createVNode(_component_RouterView)
            ])
          ])
        ]),
        createVNode(_sfc_main$2)
      ]);
    };
  }
};
const DashboardList = () => __vitePreload(() => import("./chunks/DashboardList.lite-B4-WM5Nn.js"), true ? __vite__mapDeps([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]) : void 0, import.meta.url);
const DashboardCreate = () => __vitePreload(() => import("./chunks/DashboardCreate-BABsa6oP.js"), true ? __vite__mapDeps([15,1,2,3,4,6,16,9,10,14,17,18,19,20,21,22,23,24,25,26,27,28,7,8,29,11,12,5,13,30,31,32,33]) : void 0, import.meta.url);
const DashboardEdit = () => __vitePreload(() => import("./chunks/DashboardEdit-C6ZV2SjO.js"), true ? __vite__mapDeps([34,15,1,2,3,4,6,16,9,10,14,17,18,19,20,21,22,23,24,25,26,27,28,7,8,29,11,12,5,13,30,31,32,33]) : void 0, import.meta.url);
const DashboardView = () => __vitePreload(() => import("./chunks/DashboardView-DC_5fLx9.js"), true ? __vite__mapDeps([35,1,2,3,4,6,16,9,10,14,17,18,19,20,21,22,23,24,25,26,27,28,7,8,29,5,11,12,13,30,31,32]) : void 0, import.meta.url);
const routes = [
  {
    path: "/",
    redirect: "/dashboards"
  },
  {
    path: "/dashboards",
    name: "dashboard-list",
    component: DashboardList,
    meta: {
      title: "Custom Views",
      requiresAuth: true
    }
  },
  {
    path: "/dashboards/add",
    name: "dashboard-add",
    component: DashboardList,
    meta: {
      title: "Add Custom View",
      requiresAuth: true,
      requiresEdit: true,
      showTemplateSelector: true
    }
  },
  {
    path: "/dashboards/new",
    name: "dashboard-create",
    component: DashboardCreate,
    meta: {
      title: "Create View",
      requiresAuth: true,
      requiresEdit: true
    }
  },
  {
    path: "/dashboards/:id/edit",
    name: "dashboard-edit",
    component: DashboardEdit,
    props: true,
    meta: {
      title: "Edit View",
      requiresAuth: true,
      requiresEdit: true
    }
  },
  {
    path: "/dashboards/:id/view",
    name: "dashboard-view",
    component: DashboardView,
    props: true,
    meta: {
      title: "View",
      requiresAuth: true
    }
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/dashboards"
  }
];
function hasCustomViewsAccess() {
  return false;
}
const router = createRouter({
  history: createWebHashHistory(),
  routes
});
router.beforeEach((to, _from, next) => {
  if (!hasCustomViewsAccess() && (to.name === "dashboard-list" || to.path === "/dashboards")) {
    next({ name: "dashboard-view", params: { id: "sample" } });
    return;
  }
  next();
});
const app = createApp(_sfc_main);
app.use(router);
setupPinia(app);
installOverlays(app);
app.config.errorHandler = (err, _vm, info) => {
  console.error("Custom View Error:", err, info);
};
app.mount("#monsterinsights-custom-dashboard-app");
