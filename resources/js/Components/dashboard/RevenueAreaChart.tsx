import { useState } from "react";
import {
  Area,
  AreaChart,
  CartesianGrid,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
} from "recharts";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Tabs, TabsList, TabsTrigger } from "@/Components/ui/tabs";

interface RevenuePoint {
  date: string;
  revenue: number;
  orders: number;
}

interface RevenueAreaChartProps {
  data: RevenuePoint[];
}

function formatCurrency(val: number) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "EUR",
    maximumFractionDigits: 0,
  }).format(val);
}

export default function RevenueAreaChart({ data }: RevenueAreaChartProps) {
  const [activeMetric, setActiveMetric] = useState<"revenue" | "orders">("revenue");

  if (data.length === 0) return null;

  const totalRevenue = data.reduce((s, d) => s + d.revenue, 0);
  const totalOrders = data.reduce((s, d) => s + d.orders, 0);

  return (
    <Card>
      <CardHeader className="flex flex-col items-stretch border-b p-0 sm:flex-row">
        <div className="flex flex-1 flex-col justify-center gap-1 px-6 pt-5 pb-4 sm:py-4">
          <CardTitle>Revenue Overview</CardTitle>
          <CardDescription>
            {activeMetric === "revenue"
              ? `Total: ${formatCurrency(totalRevenue)}`
              : `Total: ${totalOrders} orders`}
          </CardDescription>
        </div>
        <div className="flex px-6 pb-4 sm:items-center sm:pb-0 sm:pr-6 sm:pt-4">
          <Tabs
            value={activeMetric}
            onValueChange={(v) => setActiveMetric(v as "revenue" | "orders")}
          >
            <TabsList className="grid w-[200px] grid-cols-2">
              <TabsTrigger value="revenue">Revenue</TabsTrigger>
              <TabsTrigger value="orders">Orders</TabsTrigger>
            </TabsList>
          </Tabs>
        </div>
      </CardHeader>
      <CardContent className="px-2 pt-4 sm:p-6 sm:pt-6">
        <div className="h-[320px] w-full">
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={data} margin={{ top: 5, right: 10, left: 10, bottom: 0 }}>
              <defs>
                <linearGradient id="fillRevenue" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="var(--color-revenue)" stopOpacity={0.8} />
                  <stop offset="95%" stopColor="var(--color-revenue)" stopOpacity={0.1} />
                </linearGradient>
                <linearGradient id="fillOrders" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="var(--color-orders)" stopOpacity={0.8} />
                  <stop offset="95%" stopColor="var(--color-orders)" stopOpacity={0.1} />
                </linearGradient>
              </defs>
              <CartesianGrid vertical={false} strokeDasharray="3 3" className="stroke-muted" />
              <XAxis
                dataKey="date"
                tickLine={false}
                axisLine={false}
                tickMargin={8}
                className="text-xs text-muted-foreground"
              />
              <YAxis
                tickLine={false}
                axisLine={false}
                tickMargin={8}
                className="text-xs text-muted-foreground"
                tickFormatter={(v) =>
                  activeMetric === "revenue" ? `€${v >= 1000 ? `${(v / 1000).toFixed(0)}k` : v}` : v
                }
                width={45}
              />
              <Tooltip
                content={({ active, payload, label }) => {
                  if (!active || !payload?.length) return null;
                  const val = payload[0]?.value as number | undefined;
                  if (val === undefined) return null;
                  return (
                    <div className="rounded-lg border bg-background p-3 shadow-sm">
                      <p className="text-xs text-muted-foreground mb-1">{label}</p>
                      <p className="text-sm font-bold tabular-nums">
                        {activeMetric === "revenue" ? formatCurrency(val) : `${val} orders`}
                      </p>
                    </div>
                  );
                }}
              />
              <Area
                dataKey={activeMetric === "revenue" ? "revenue" : "orders"}
                type="natural"
                fill={`url(#fill${activeMetric === "revenue" ? "Revenue" : "Orders"})`}
                fillOpacity={0.4}
                stroke={`var(--color-${activeMetric === "revenue" ? "revenue" : "orders"})`}
                strokeWidth={2}
              />
            </AreaChart>
          </ResponsiveContainer>
        </div>
      </CardContent>
    </Card>
  );
}
