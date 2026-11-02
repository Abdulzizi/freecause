import { u as useRouter } from "./TheAppHeader-Dc7mNmwW.js";
import { U as UpsellModal, u as useSampleData } from "./useSampleData-CwRuQHiS.js";
import { u as useFeatureGate } from "./useFeatureGate-B3uCywkg.js";
import { z as onMounted, o as openBlock, c as createElementBlock, b as createVNode, u as unref } from "./AppOverlays-5d5Sx5mz.js";
import "./ajax-pmt98pQN.js";
import "./default-i18n-KrIlCc2E.js";
import "./Modal-CN0naihT.js";
import "./Icon-CyTPFQ-E.js";
import "./license-CZe4-J_K.js";
import "./useNotices-ooYBwYj4.js";
const _hoisted_1 = { class: "monsterinsights-dashboard-list" };
const _sfc_main = {
  __name: "DashboardList.lite",
  setup(__props) {
    const router = useRouter();
    const {
      shouldShowUpsell,
      upsellContent,
      hasSampleData,
      openUpsellModal,
      closeUpsellModal,
      enableSampleMode
    } = useFeatureGate("custom-dashboard");
    const { sampleData: sampleViewData, loadSampleData: loadSampleView } = useSampleData("custom-dashboard", "sample-view");
    onMounted(async () => {
      await loadSampleView();
      openUpsellModal();
    });
    async function handleSeeSample() {
      if (!sampleViewData.value) {
        await loadSampleView();
      }
      enableSampleMode();
      if (sampleViewData.value?.[0]?.id) {
        router.push({
          name: "dashboard-view",
          params: { id: sampleViewData.value[0].id }
        });
      }
    }
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("div", _hoisted_1, [
        createVNode(UpsellModal, {
          isOpen: unref(shouldShowUpsell),
          feature: "custom-dashboard",
          content: unref(upsellContent),
          showSampleButton: unref(hasSampleData),
          customImage: "sample-image-monsterinsights.png",
          onClose: unref(closeUpsellModal),
          onSeeSample: handleSeeSample
        }, null, 8, ["isOpen", "content", "showSampleButton", "onClose"])
      ]);
    };
  }
};
export {
  _sfc_main as default
};
