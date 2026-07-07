import { useMemo, useState } from "react";
import { Link, Head, router } from "@inertiajs/react";
import { Users, TrendingUp, DollarSign, ShoppingCart, BarChart3, Package } from "lucide-react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Tabs, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { useAuth } from "@/hooks/use-auth";
import KpiCard from "@/Components/dashboard/KpiCard";
import RevenueAreaChart from "@/Components/dashboard/RevenueAreaChart";
import RevenueBreakdownChart from "@/Components/dashboard/RevenueBreakdownChart";
import OrderStatusChart from "@/Components/dashboard/OrderStatusChart";
import RecentOrdersTable from "@/Components/dashboard/RecentOrdersTable";
import TopProductsTable from "@/Components/dashboard/TopProductsTable";
import CustomerInsights from "@/Components/dashboard/CustomerInsights";
import LowStockAlerts from "@/Components/dashboard/LowStockAlerts";
import PendingReviews from "@/Components/dashboard/PendingReviews";

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
  recent_orders: Array<{
    id: number;
    order_number: string;
    customer_name: string;
    total: number;
    status: string;
    items_count: number;
    created_at: string;
  }>;
  top_products: Array<{
    product_id: number;
    product_name: string;
    total_qty: number;
    total_revenue: number;
  }>;
  order_status_counts: Record<string, number>;
  total_revenue: number;
  total_orders: number;
  total_products: number;
  total_customers: number;
  active_products: number;
  abandoned_carts: number;
  recovered_carts: number;
  customer_insights: {
    total_customers: number;
    retention_rate: number;
    prev_retention_rate: number;
    avg_ltv: number;
    growth: Array<{
      month: string;
      customers: number;
      new: number;
    }>;
  };
  revenue_breakdown: Array<{
    name: string;
    value: number;
    percentage: number;
  }>;
  low_stock_products: Array<{
    id: number;
    name: string;
    slug: string;
    thumbnail_url: string | null;
    stock_quantity: number;
    low_stock_threshold: number;
    track_stock: boolean;
  }>;
  pending_reviews: Array<{
    id: number;
    product: { id: number; name: string; slug: string; thumbnail_url: string | null };
    user: { id: number; name: string };
    rating: number;
    title: string;
    content: string;
    created_at: string;
  }>;
}

interface DashboardIndexProps {
  analytics?: AnalyticsData;
}

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  Users,
  TrendingUp,
  DollarSign,
  ShoppingCart,
  BarChart3,
  Package,
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
  const revenueData = analytics?.revenue_over_time ?? [];

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
              {analytics?.total_orders ?? 0} total orders · €
              {((analytics?.total_revenue ?? 0) / 1000).toFixed(0)}k revenue
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
            <Button asChild size="sm">
              <Link href={route("dashboard.shop.products.create")}>
                <Package className="mr-1.5 h-4 w-4" />
                New Product
              </Link>
            </Button>
            <Button asChild variant="outline" size="sm">
              <Link href={route("dashboard.shop.orders.index")}>
                <ShoppingCart className="mr-1.5 h-4 w-4" />
                Orders
              </Link>
            </Button>
          </div>
        </div>

        {/* KPI row — 6 cards */}
        {analyticsKpis.length > 0 && (
          <div className="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            {analyticsKpis.map((kpi, i) => {
              const Icon = iconMap[kpi.icon] || Package;
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

        {/* Hero Area Chart — full width */}
        {revenueData.length > 0 && <RevenueAreaChart data={revenueData} />}

        {/* Charts row: Order Status + Revenue Breakdown side by side */}
        <div className="grid gap-6 lg:grid-cols-2">
          <OrderStatusChart data={analytics?.order_status_counts ?? {}} />
          <RevenueBreakdownChart data={analytics?.revenue_breakdown ?? []} />
        </div>

        {/* Customer Insights */}
        {analytics?.customer_insights && analytics.customer_insights.growth.length > 0 && (
          <CustomerInsights
            totalCustomers={analytics.customer_insights.total_customers}
            retentionRate={analytics.customer_insights.retention_rate}
            prevRetentionRate={analytics.customer_insights.prev_retention_rate}
            avgLtv={analytics.customer_insights.avg_ltv}
            growth={analytics.customer_insights.growth}
          />
        )}

        {/* Recent Orders + Top Products side by side */}
        <div className="grid gap-6 lg:grid-cols-2">
          <RecentOrdersTable orders={analytics?.recent_orders ?? []} />
          {analytics && analytics.top_products.length > 0 && (
            <TopProductsTable products={analytics.top_products} />
          )}
        </div>

        {/* Bottom widgets row */}
        {analytics &&
          (analytics.low_stock_products?.length ?? 0) + (analytics.pending_reviews?.length ?? 0) >
            0 && (
            <div className="grid gap-6 lg:grid-cols-2">
              {(analytics.low_stock_products?.length ?? 0) > 0 && (
                <LowStockAlerts products={analytics.low_stock_products ?? []} />
              )}
              {(analytics.pending_reviews?.length ?? 0) > 0 && (
                <PendingReviews reviews={analytics.pending_reviews ?? []} />
              )}
            </div>
          )}

        {/* Footer stats — compact row */}
        {analytics && (
          <div className="grid gap-4 grid-cols-2 sm:grid-cols-4">
            <Card>
              <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
                <CardTitle className="text-xs font-medium text-muted-foreground">
                  Abandoned Carts
                </CardTitle>
                <ShoppingCart className="h-3.5 w-3.5 text-muted-foreground" />
              </CardHeader>
              <CardContent className="p-4 pt-0">
                <div className="text-lg font-bold">{analytics.abandoned_carts}</div>
                <p className="text-xs text-muted-foreground">
                  Recovered: {analytics.recovered_carts}
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
                <CardTitle className="text-xs font-medium text-muted-foreground">
                  Total Revenue
                </CardTitle>
                <DollarSign className="h-3.5 w-3.5 text-muted-foreground" />
              </CardHeader>
              <CardContent className="p-4 pt-0">
                <div className="text-lg font-bold">
                  {formatKpiValue(analytics.total_revenue, "currency")}
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
                <CardTitle className="text-xs font-medium text-muted-foreground">
                  Total Orders
                </CardTitle>
                <BarChart3 className="h-3.5 w-3.5 text-muted-foreground" />
              </CardHeader>
              <CardContent className="p-4 pt-0">
                <div className="text-lg font-bold">{analytics.total_orders.toLocaleString()}</div>
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="p-4 pb-2 flex flex-row items-center justify-between">
                <CardTitle className="text-xs font-medium text-muted-foreground">
                  Products
                </CardTitle>
                <Package className="h-3.5 w-3.5 text-muted-foreground" />
              </CardHeader>
              <CardContent className="p-4 pt-0">
                <div className="text-lg font-bold">
                  {analytics.active_products.toLocaleString()}
                </div>
                <p className="text-xs text-muted-foreground">
                  Active / {analytics.total_products} total
                </p>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </AuthenticatedLayout>
  );
}
