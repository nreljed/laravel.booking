import { useMemo, useState } from "react";
import { Head, router } from "@inertiajs/react";
import { TrendingUp, BarChart3 } from "lucide-react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Tabs, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { useAuth } from "@/hooks/use-auth";
import KpiCard from "@/Components/dashboard/KpiCard";

interface AnalyticsData {
  days: number;
  kpis: Array<{
    label: string;
    subtitle?: string;
    value: number;
    format?: string;
    change?: number;
    icon: string;
  }>;
  revenue_over_time: Array<{
    date: string;
    revenue: number;
    orders: number;
  }>;
  total_customers: number;
}

interface DashboardIndexProps {
  analytics?: AnalyticsData;
}

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  TrendingUp,
  BarChart3,
};

const PERIODS = [
  { label: "7d", days: 7 },
  { label: "30d", days: 30 },
  { label: "90d", days: 90 },
  { label: "365d", days: 365 },
];

export default function DashboardIndex({ analytics }: DashboardIndexProps) {
  const { user } = useAuth();
  const [period, setPeriod] = useState(analytics?.days ?? 14);

  const analyticsKpis = analytics?.kpis ?? [];
  const revenueData = useMemo(() => analytics?.revenue_over_time ?? [], [analytics]);

  const sparklineData = useMemo(() => {
    if (revenueData.length < 2) return;
    return revenueData.map((d) => d.revenue);
  }, [revenueData]);

  const formatKpiValue = (val: number, fmt?: string) => {
    if (fmt === "currency")
      return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "EUR",
        maximumFractionDigits: 0,
      }).format(val);
    if (fmt === "percent") return `${val}%`;
    return val.toLocaleString();
  };

  const handlePeriodChange = (days: number) => {
    setPeriod(days);
    router.get(
      route("dashboard.index"),
      { days },
      { preserveState: true, preserveScroll: true, only: ["analytics"] },
    );
  };

  return (
    <AuthenticatedLayout header="Dashboard">
      <Head title="Dashboard" />

      <div className="space-y-6">
        {/* Header with period tabs */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 className="text-2xl font-bold tracking-tight">
              Welcome back, {user?.name || "User"}!
            </h2>
            <p className="text-sm text-muted-foreground mt-1">
              {analytics?.total_customers ?? 0} total customers
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Tabs value={String(period)} onValueChange={(v) => handlePeriodChange(Number(v))}>
              <TabsList>
                {PERIODS.map((p) => (
                  <TabsTrigger key={p.days} value={String(p.days)}>
                    {p.label}
                  </TabsTrigger>
                ))}
              </TabsList>
            </Tabs>
          </div>
        </div>

        {/* KPI row — 6 cards */}
        {analyticsKpis.length > 0 && (
          <div className="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            {analyticsKpis.map((kpi, i) => {
              const Icon = iconMap[kpi.icon] || TrendingUp;
              return (
                <KpiCard
                  key={i}
                  title={kpi.label}
                  subtitle={kpi.subtitle}
                  value={formatKpiValue(kpi.value, kpi.format)}
                  change={kpi.change}
                  icon={Icon}
                  sparklineData={sparklineData}
                />
              );
            })}
          </div>
        )}

        {/* Footer stats — compact row */}
        {analytics && (
          <div className="grid gap-4 grid-cols-2 sm:grid-cols-4">
            <Card>
              <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
                <CardTitle className="text-xs font-medium text-muted-foreground">
                  Total Customers
                </CardTitle>
                <BarChart3 className="h-3.5 w-3.5 text-muted-foreground" />
              </CardHeader>
              <CardContent className="p-4 pt-0">
                <div className="text-lg font-bold">
                  {analytics.total_customers.toLocaleString()}
                </div>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </AuthenticatedLayout>
  );
}
