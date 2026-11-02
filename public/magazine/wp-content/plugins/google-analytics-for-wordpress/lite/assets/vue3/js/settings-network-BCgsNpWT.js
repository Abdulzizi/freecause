const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./chunks/monsterinsights-SettingsNetwork-Dy9dHCwA.js","./chunks/ajax-pmt98pQN.js","./chunks/AppOverlays-5d5Sx5mz.js","../css/main-monsterinsights-BqoTIBUO.css","./chunks/license-CZe4-J_K.js","./chunks/useNotices-ooYBwYj4.js","./chunks/SettingsBlock-kZ8sUmJ6.js","../css/main-monsterinsights-DCic6-mZ.css","./chunks/SettingsInputCheckbox-Bs64zm4R.js","./chunks/settings-cGLZnSNk.js","./chunks/monsterinsights-Lite-SK_c-e8R.js","./chunks/TheAppHeader-Dc7mNmwW.js","./chunks/addons-DT40ECM4.js","./chunks/Modal-CN0naihT.js","../css/main-monsterinsights-DRT55mf0.css","../css/main-monsterinsights-UNEVlsO2.css","./chunks/SettingsInfoTooltip-DWQ3rLUy.js","./chunks/monsterinsights-SettingsInputAuthenticate-Lite-70zHxAw9.js","./chunks/auth-BTdxFMcG.js","./chunks/SettingsLiteUpsellLarge-w-JhLcJn.js","./chunks/LaunchWizardButton-DnvghPUX.js","../css/main-monsterinsights-BL4OIBre.css","./chunks/AddonsPage-DPYsCtcn.js","../css/main-monsterinsights-CGAGRmkt.css","./chunks/AboutShell-BoYTbpAM.js","./chunks/monsterinsights-AboutTabAboutUs-Rkh4GmSM.js","./chunks/AboutBlock-CBqphb2y.js","./chunks/monsterinsights-AboutTabGettingStarted-Bc2UF0NU.js","./chunks/monsterinsights-AboutTabLiteVsPro-CZBnt9WL.js"])))=>i.map(i=>d[i]);
import { D as watch, z as onMounted, r as resolveComponent, o as openBlock, c as createElementBlock, F as Fragment, b as createVNode, E as withCtx, G as createBlock, v as createCommentVNode, a as createBaseVNode, _ as _sfc_main$8, j as normalizeClass, p as computed, d as createApp, i as installOverlays } from "./chunks/AppOverlays-5d5Sx5mz.js";
import { b as useRoute, e as _sfc_main$1, _ as _sfc_main$3, c as createRouter, a as createWebHashHistory } from "./chunks/TheAppHeader-Dc7mNmwW.js";
import { k as isPro, _ as __vitePreload, s as setupPinia } from "./chunks/ajax-pmt98pQN.js";
import { _ as _sfc_main$2, a as _sfc_main$4, b as _sfc_main$5, c as _sfc_main$6, u as useAuth } from "./chunks/monsterinsights-Lite-SK_c-e8R.js";
import { c as useAddonsStore, a as useToast, _ as _sfc_main$7 } from "./chunks/addons-DT40ECM4.js";
import { u as useSettingsStore } from "./chunks/settings-cGLZnSNk.js";
import { u as useLicenseStore } from "./chunks/license-CZe4-J_K.js";
import { a as authApi } from "./chunks/auth-BTdxFMcG.js";
import "./chunks/useNotices-ooYBwYj4.js";
import "./chunks/Modal-CN0naihT.js";
const _hoisted_5 = {
  key: 0,
  class: "monsterinsights-navigation-bar"
};
const _hoisted_6 = { class: "monsterinsights-container" };
const _hoisted_7 = { class: "monsterinsights-navigation-bar" };
const _hoisted_8 = { class: "monsterinsights-container" };
const _hoisted_9 = { class: "monsterinsights-main-navigation" };
const _sfc_main = {
  __name: "AppNetwork",
  setup(__props) {
    const route = useRoute();
    const settingsStore = useSettingsStore();
    const licenseStore = useLicenseStore();
    const addonsStore = useAddonsStore();
    const { updateAuth } = useAuth();
    const { loadingToast, closeToast } = useToast();
    const routeClass = computed(() => {
      return "monsterinsights-admin-page monsterinsights-settings-panel monsterinsights-settings-panel-network monsterinsights-path-" + route.name;
    });
    const isAddonsRoute = computed(() => route.name === "addons");
    const isAboutRoute = computed(() => typeof route.name === "string" && route.name.startsWith("about"));
    const showSave = computed(() => !isAddonsRoute.value && !isAboutRoute.value);
    let cached_settings_link = null;
    let cached_addons_link = null;
    let cached_about_link = null;
    function updateAdminMenuHighlight(name) {
      [cached_settings_link, cached_addons_link, cached_about_link].forEach((el) => {
        if (el) el.parentElement.classList.remove("current");
      });
      if (name === "addons" && cached_addons_link) {
        cached_addons_link.parentElement.classList.add("current");
      } else if (typeof name === "string" && name.indexOf("about") >= 0 && cached_about_link) {
        cached_about_link.parentElement.classList.add("current");
      } else if (cached_settings_link) {
        cached_settings_link.parentElement.classList.add("current");
      }
    }
    watch(() => route.name, (name) => updateAdminMenuHighlight(name));
    onMounted(() => {
      const settings_links = document.querySelectorAll('[href="admin.php?page=monsterinsights_network"]');
      cached_settings_link = settings_links[settings_links.length > 1 ? 1 : 0] || null;
      cached_addons_link = document.querySelector('[href*="monsterinsights_network#/addons"]');
      cached_about_link = document.querySelector('[href*="monsterinsights_network#/about"]');
      updateAdminMenuHighlight(route.name);
      settings_links.forEach((link) => {
        if (!link.href.includes("#")) {
          link.href = link.href + "#/";
        }
      });
    });
    onMounted(async () => {
      addonsStore.enableAddonNotices();
      loadingToast();
      try {
        await Promise.all([
          settingsStore.fetchSettings(),
          authApi.fetchAuth().then((data) => {
            if (data) updateAuth(data);
          }).catch(() => {
          }),
          addonsStore.fetchAddonsAction().catch(() => {
          }),
          isPro() ? licenseStore.fetchLicenseData().catch(() => {
          }) : Promise.resolve()
        ]);
      } finally {
        closeToast();
      }
    });
    return (_ctx, _cache) => {
      const _component_RouterView = resolveComponent("RouterView");
      return openBlock(), createElementBlock("div", {
        class: normalizeClass(routeClass.value)
      }, [
        (openBlock(), createElementBlock(Fragment, { key: 1 }, [
          createVNode(_sfc_main$3, null, {
            "header-right": withCtx(() => [
              createVNode(_sfc_main$1),
              showSave.value ? (openBlock(), createBlock(_sfc_main$2, { key: 0 })) : createCommentVNode("", true)
            ]),
            _: 1
          }),
          isAboutRoute.value ? (openBlock(), createElementBlock("div", _hoisted_5, [
            createBaseVNode("div", _hoisted_6, [
              createVNode(_sfc_main$4)
            ])
          ])) : createCommentVNode("", true),
          isAddonsRoute.value ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [
            createBaseVNode("div", _hoisted_7, [
              createBaseVNode("div", _hoisted_8, [
                createBaseVNode("nav", _hoisted_9, [
                  createVNode(_sfc_main$5)
                ])
              ])
            ]),
            createVNode(_sfc_main$6)
          ], 64)) : createCommentVNode("", true)
        ], 64)),
        createVNode(_sfc_main$7),
        createVNode(_component_RouterView),
        createVNode(_sfc_main$8)
      ], 2);
    };
  }
};
const { __ } = wp.i18n;
const SettingsNetwork = () => __vitePreload(() => import("./chunks/monsterinsights-SettingsNetwork-Dy9dHCwA.js"), true ? __vite__mapDeps([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21]) : void 0, import.meta.url);
const AddonsPage = () => __vitePreload(() => import("./chunks/AddonsPage-DPYsCtcn.js"), true ? __vite__mapDeps([22,11,2,3,1,12,5,13,14,4,23]) : void 0, import.meta.url);
const AboutShell = () => __vitePreload(() => import("./chunks/AboutShell-BoYTbpAM.js"), true ? __vite__mapDeps([24,2,3]) : void 0, import.meta.url);
const AboutTabAboutUs = () => __vitePreload(() => import("./chunks/monsterinsights-AboutTabAboutUs-Rkh4GmSM.js"), true ? __vite__mapDeps([25,12,5,2,3,1,13,14,26,11,4]) : void 0, import.meta.url);
const AboutTabGettingStarted = () => __vitePreload(() => import("./chunks/monsterinsights-AboutTabGettingStarted-Bc2UF0NU.js"), true ? __vite__mapDeps([27,4,2,3,1,5,26,20,12,13,14]) : void 0, import.meta.url);
const AboutTabLiteVsPro = () => __vitePreload(() => import("./chunks/monsterinsights-AboutTabLiteVsPro-CZBnt9WL.js"), true ? __vite__mapDeps([28,1,2,3,26]) : void 0, import.meta.url);
const routes = [
  {
    path: "/",
    name: "general",
    component: SettingsNetwork,
    meta: {
      title: __("Network Settings", "google-analytics-for-wordpress")
    }
  },
  {
    path: "/addons",
    name: "addons",
    component: AddonsPage,
    meta: {
      title: __("Addons", "google-analytics-for-wordpress")
    }
  },
  {
    path: "/about",
    component: AboutShell,
    meta: {
      title: __("About Us", "google-analytics-for-wordpress")
    },
    children: [
      {
        path: "",
        name: "about-us",
        component: AboutTabAboutUs,
        meta: { title: __("About Us", "google-analytics-for-wordpress") }
      },
      {
        path: "getting-started",
        name: "about-getting-started",
        component: AboutTabGettingStarted,
        meta: { title: __("Getting Started", "google-analytics-for-wordpress") }
      },
      {
        path: "lite-vs-pro",
        name: "about-lite-vs-pro",
        component: AboutTabLiteVsPro,
        meta: { title: __("Lite vs Pro", "google-analytics-for-wordpress") }
      }
    ]
  },
  // Catch-all redirect - MUST be last
  {
    path: "/:pathMatch(.*)*",
    redirect: "/"
  }
];
const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0, left: 0 };
  }
});
const app = createApp(_sfc_main);
app.use(router);
installOverlays(app);
setupPinia(app);
const mountEl = document.getElementById("monsterinsights-vue-network-settings");
if (mountEl) {
  app.mount(mountEl);
}
