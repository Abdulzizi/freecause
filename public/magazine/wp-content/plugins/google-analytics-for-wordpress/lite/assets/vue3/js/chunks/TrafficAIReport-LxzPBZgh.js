import { a3 as storeToRefs, o as openBlock, G as createBlock, E as withCtx, b as createVNode, u as unref, p as computed, k as ref } from "./AppOverlays-5d5Sx5mz.js";
import { _ as __ } from "./default-i18n-KrIlCc2E.js";
import { u as useOverviewReportStore, A as isSampleDataEnabled, b as buildApiFilters } from "../reports-BlsvkvNU.js";
import { m as generateTrafficAISample, n as fetchTrafficAIData } from "./trafficSampleData-CnP_na0C.js";
import { u as useReportPermissions } from "./useReportPermissions-_u596bZa.js";
import { u as useReport } from "./useReport-DlsvMPON.js";
import { f as formatPct, a as formatCurr, b as formatNum } from "./overviewTableFormatters-CiLYjEZJ.js";
import { f as formatDateLabel } from "./useOverviewChartData-Ck6feSW8.js";
import { a as aggregateDateEntityRows } from "./aggregateDateEntityRows-i7QMgwng.js";
import { g as getCompareDateLabels } from "./compareDateLabels-B56Y3XjZ.js";
import { j as getMiGlobal } from "./ajax-pmt98pQN.js";
import { R as ReportPageLayout } from "./ReportPageLayout-DR6z7ANN.js";
import { _ as _sfc_main$2 } from "./ReportChartSection-_EiOrYt9.js";
import { _ as _sfc_main$1 } from "./ReportDataTable-CVJ5_plU.js";
import "./TheAppHeader-Dc7mNmwW.js";
import "./dateIntervals-BPoui_3H.js";
import "./addons-DT40ECM4.js";
import "./useNotices-ooYBwYj4.js";
import "./Modal-CN0naihT.js";
import "./Icon-CyTPFQ-E.js";
import "./useAuthGate-BPBSyEmc.js";
import "./flatpickr-Bqky3kHj.js";
import "./useFeatureGate-B3uCywkg.js";
import "./UniversallyPromo-C4JB78Jr.js";
import "./reportCache-DlMEvb6L.js";
import "./ReAuthModal-BAFYHRau.js";
import "./auth-BTdxFMcG.js";
import "./ApexLineChart-B1Ki6nie.js";
import "./vue3-apexcharts-Drwy2afu.js";
import "./useChartColors-Bi1Kbjjv.js";
import "./LoadingSpinnerInline-D6gqDANX.js";
import "./SiteNotes-DBM0jG2y.js";
import "./siteNotes-Dv-yQnn7.js";
import "./ReportTableModal-BmJo0zPG.js";
const _sfc_main = {
  __name: "TrafficAIReport",
  setup(__props) {
    const overviewStore = useOverviewReportStore();
    const { dateRange, activeFilters: storeActiveFilters, activeDevice: storeActiveDevice } = storeToRefs(overviewStore);
    const { isBlocked } = useReportPermissions({ minTier: "plus" });
    const activeChartTab = ref("sessions");
    const chartTabs = [
      { id: "sessions", label: __("Sessions", "google-analytics-for-wordpress"), icon: "users" },
      { id: "pageviews", label: __("Pageviews", "google-analytics-for-wordpress"), icon: "view" }
    ];
    const AI_PLATFORMS = [
      "chatgpt",
      "openai",
      "copilot",
      "gemini",
      "perplexity",
      "claude",
      "anthropic",
      "bard",
      "bing",
      "grok",
      "metaai",
      "mistral",
      "poe",
      "you",
      "character-ai",
      "huggingface",
      "midjourney",
      "dalle",
      "replicate",
      "runway",
      "stability"
    ];
    function isAIPlatform(sourceName) {
      const name = (sourceName || "").toLowerCase().replace(/[^a-z]/g, "");
      return AI_PLATFORMS.some((platform) => name.includes(platform));
    }
    function getAIIcon(sourceName) {
      const name = (sourceName || "").toLowerCase().replace(/[^a-z]/g, "");
      const match = AI_PLATFORMS.find((icon) => name.includes(icon));
      if (!match) return "";
      const baseAssets = getMiGlobal("plugin_assets_url", "");
      if (!baseAssets) return "";
      return baseAssets + "images/ai/icon-" + match + ".svg";
    }
    const chartRawDates = computed(() => {
      const chartResult = rawData.value?.sessions_chart;
      if (!chartResult?.rows?.length) return [];
      return chartResult.rows.map((row) => row?.d?.[0] || "");
    });
    const isCompareActive = computed(
      () => !!(dateRange.value?.compareReport && dateRange.value?.compareStart && dateRange.value?.compareEnd)
    );
    const chartData = computed(() => {
      const chartResult = rawData.value?.sessions_chart;
      if (!chartResult?.rows?.length) return { categories: [], series: [] };
      const rows = chartResult.rows;
      const categories = [];
      const sessionsCurr = [];
      const pageViewsCurr = [];
      const sessionsPrev = [];
      const pageViewsPrev = [];
      const firstM = rows[0]?.m;
      const isCompareFormat = Array.isArray(firstM) && firstM.length === 2 && Array.isArray(firstM[0]) && firstM[0].length === 2 && isCompareActive.value;
      for (const row of rows) {
        const date = row?.d?.[0] || "";
        categories.push(formatDateLabel(date));
        if (isCompareFormat) {
          const mSessions = row?.m?.[0] || [];
          const mPageViews = row?.m?.[1] || [];
          sessionsPrev.push(Number(mSessions[0]) || 0);
          sessionsCurr.push(Number(mSessions[1]) || 0);
          pageViewsPrev.push(Number(mPageViews[0]) || 0);
          pageViewsCurr.push(Number(mPageViews[1]) || 0);
        } else {
          const m0 = Array.isArray(row?.m?.[0]) ? row.m[0] : [];
          sessionsCurr.push(Number(m0[0]) || 0);
          pageViewsCurr.push(Number(m0[1]) || 0);
        }
      }
      const primaryColor = "#3A93DD";
      const compareColor = "#A0AEC0";
      const isSessionsTab = activeChartTab.value === "sessions";
      const series = [];
      const colors = [];
      const strokeDashArray = [];
      series.push({
        name: isSessionsTab ? "Sessions" : "Pageviews",
        data: isSessionsTab ? sessionsCurr : pageViewsCurr
      });
      colors.push(primaryColor);
      strokeDashArray.push(0);
      if (isCompareFormat) {
        series.push({
          name: "Previous Period",
          data: isSessionsTab ? sessionsPrev : pageViewsPrev
        });
        colors.push(compareColor);
        strokeDashArray.push(5);
      }
      return { categories, series, colors, strokeDashArray };
    });
    const columns = [
      { key: "source", label: __("AI Source", "google-analytics-for-wordpress"), sortable: true, iconKey: "sourceIcon" },
      { key: "sessions", label: __("Sessions", "google-analytics-for-wordpress"), sortable: true },
      { key: "engagedSessions", label: __("Engaged Sessions", "google-analytics-for-wordpress"), sortable: true },
      { key: "purchases", label: __("Purchases", "google-analytics-for-wordpress"), sortable: true },
      { key: "revenue", label: __("Revenue", "google-analytics-for-wordpress"), sortable: true },
      { key: "bounceRate", label: __("Bounce Rate", "google-analytics-for-wordpress"), sortable: true, totalType: "average" },
      { key: "avgSessionDuration", label: __("Avg. Session Duration", "google-analytics-for-wordpress"), sortable: true, type: "duration", totalType: "average" }
    ];
    const aggregatedAI = computed(
      () => aggregateDateEntityRows(rawData.value?.ai_table?.rows, {
        metricCount: 6,
        avgIndices: [4, 5],
        weightIndex: 0
      }).filter((entity) => {
        const dimVal = entity.dims[0];
        return dimVal && isAIPlatform(String(dimVal));
      })
    );
    function formatAIRow(dims, vals) {
      const name = String(dims[0]).trim();
      return {
        source: name,
        sourceIcon: getAIIcon(name),
        sessions: formatNum(vals[0] || 0),
        engagedSessions: formatNum(vals[1] || 0),
        purchases: formatNum(vals[2] || 0),
        revenue: formatCurr(vals[3] || 0),
        // API returns bounce rate as a decimal (0.05 = 5%).
        bounceRate: formatPct((vals[4] || 0) * 100),
        // Duration column expects raw seconds as a number.
        avgSessionDuration: vals[5] || 0
      };
    }
    const tableRows = computed(
      () => aggregatedAI.value.map((entity) => formatAIRow(entity.dims, entity.current))
    );
    const compareRows = computed(
      () => aggregateDateEntityRows(rawData.value?.ai_table_prev?.rows, {
        metricCount: 6,
        avgIndices: [4, 5],
        weightIndex: 0
      }).map((entity) => formatAIRow(entity.dims, entity.current))
    );
    const compareDateLabelsForTable = computed(() => getCompareDateLabels(dateRange.value));
    const { rawData, loading, error, reload } = useReport({
      fetch: () => fetchTrafficAIData(
        dateRange.value,
        buildApiFilters(storeActiveFilters.value, storeActiveDevice.value)
      ),
      sample: () => generateTrafficAISample(dateRange.value),
      sampleWhen: () => isBlocked.value || isSampleDataEnabled(),
      isBlocked,
      watch: [dateRange, storeActiveFilters, storeActiveDevice],
      guard: () => !!(dateRange.value?.start && dateRange.value?.end)
    });
    return (_ctx, _cache) => {
      return openBlock(), createBlock(ReportPageLayout, {
        "required-license": "plus",
        "upsell-feature": "traffic-ai"
      }, {
        chart: withCtx(() => [
          createVNode(_sfc_main$2, {
            tabs: chartTabs,
            "active-tab": activeChartTab.value,
            "chart-data": chartData.value,
            loading: unref(loading),
            error: unref(error),
            "show-site-notes": !unref(isBlocked),
            "date-range": unref(overviewStore).dateRange,
            "raw-dates": chartRawDates.value,
            "onUpdate:activeTab": _cache[0] || (_cache[0] = ($event) => activeChartTab.value = $event),
            onSiteNotesSaved: unref(reload)
          }, null, 8, ["active-tab", "chart-data", "loading", "error", "show-site-notes", "date-range", "raw-dates", "onSiteNotesSaved"])
        ]),
        table: withCtx(() => [
          createVNode(_sfc_main$1, {
            title: unref(__)("AI Traffic Details", "google-analytics-for-wordpress"),
            columns,
            rows: tableRows.value,
            "compare-rows": compareRows.value,
            "compare-date-labels": compareDateLabelsForTable.value,
            loading: unref(loading),
            "empty-message": unref(__)("No AI traffic data currently available.", "google-analytics-for-wordpress")
          }, null, 8, ["title", "rows", "compare-rows", "compare-date-labels", "loading", "empty-message"])
        ]),
        _: 1
      });
    };
  }
};
export {
  _sfc_main as default
};
