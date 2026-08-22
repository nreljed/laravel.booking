import "../css/app.css";
import "leaflet/dist/leaflet.css";
import "./bootstrap";

import { Building2, Calendar } from "lucide-react";
import {
  buildPageMap,
  createFoundationApp,
  type FoundationAppConfig,
  type PageImport,
} from "@dashboard/app";
import { registerCoreTemplates } from "@dashboard/dashboard";
import { registerFeatureIcons } from "@dashboard/lib/icons";

registerFeatureIcons({ Calendar, Building2 });

const localPages: Record<string, PageImport> = Object.fromEntries(
  Object.entries(import.meta.glob("./Pages/**/*.tsx") as Record<string, PageImport>)
    .map(([path, importFn]) => [
      path.match(/\/Pages\/(.+)\.tsx$/)?.[1]?.toLowerCase() ?? "",
      importFn,
    ])
    .filter(([key]) => key !== ""),
);

const bookingModulePages = buildPageMap({
  projectPages: import.meta.glob("@booking/Pages/**/*.tsx") as Record<string, PageImport>,
  projectPrefix: "modules/bookings",
});

const propertyModulePages = buildPageMap({
  projectPages: import.meta.glob("@properties/Pages/**/*.tsx") as Record<string, PageImport>,
  projectPrefix: "modules/properties",
});

const config: FoundationAppConfig = {
  basePages: {
    ...registerCoreTemplates(),
    ...localPages,
    ...bookingModulePages,
    ...propertyModulePages,
  },
};

createFoundationApp(config);
