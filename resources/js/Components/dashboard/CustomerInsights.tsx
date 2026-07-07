import { Area, AreaChart, CartesianGrid, XAxis, YAxis } from "recharts";
import { ChartContainer, ChartTooltipContent } from "@/Components/ui/chart";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Users } from "lucide-react";
import type { ChartConfig } from "@/Components/ui/chart";

interface CustomerGrowth {
  month: string;
  customers: number;
  new: number;
}

interface CustomerInsightsProps {
  totalCustomers: number;
  retentionRate: number;
  prevRetentionRate: number;
  avgLtv: number;
  growth: CustomerGrowth[];
}

const currency = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "EUR",
  minimumFractionDigits: 0,
  maximumFractionDigits: 0,
});

export default function CustomerInsights({
  totalCustomers,
  retentionRate,
  prevRetentionRate,
  avgLtv,
  growth,
}: CustomerInsightsProps) {
  const chartConfig = {
    customers: {
      label: "Total Customers",
      color: "var(--chart-1)",
    },
    new: {
      label: "New Customers",
      color: "var(--chart-2)",
    },
  } satisfies ChartConfig;

  const retentionChange = retentionRate - prevRetentionRate;

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle className="flex items-center gap-2 text-sm font-medium">
          <Users className="h-4 w-4" />
          Customer Insights
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid gap-6 lg:grid-cols-3">
          {/* KPI metrics */}
          <div className="space-y-4">
            <div>
              <p className="text-xs text-muted-foreground">Total Customers</p>
              <p className="text-2xl font-bold">{totalCustomers.toLocaleString()}</p>
            </div>
            <div>
              <p className="text-xs text-muted-foreground">Retention Rate</p>
              <p className="text-2xl font-bold">{retentionRate}%</p>
              <p
                className={`text-xs mt-0.5 ${retentionChange > 0 ? "text-emerald-600" : retentionChange < 0 ? "text-red-600" : "text-muted-foreground"}`}
              >
                {retentionChange > 0 ? "+" : ""}
                {retentionChange.toFixed(1)}% vs last period
              </p>
            </div>
            <div>
              <p className="text-xs text-muted-foreground">Avg. LTV</p>
              <p className="text-2xl font-bold">{currency.format(avgLtv)}</p>
            </div>
          </div>

          {/* Area chart */}
          <div className="lg:col-span-2">
            <ChartContainer config={chartConfig} className="h-[200px] w-full">
              <AreaChart data={growth} margin={{ top: 8, right: 8, left: -16, bottom: 4 }}>
                <CartesianGrid vertical={false} strokeDasharray="3 3" />
                <XAxis
                  dataKey="month"
                  tickLine={false}
                  tickMargin={8}
                  axisLine={false}
                  fontSize={11}
                />
                <YAxis tickLine={false} axisLine={false} fontSize={11} />
                <ChartTooltipContent indicator="dot" />
                <Area
                  type="monotone"
                  dataKey="customers"
                  fill="var(--color-customers)"
                  fillOpacity={0.15}
                  stroke="var(--color-customers)"
                  strokeWidth={2}
                />
                <Area
                  type="monotone"
                  dataKey="new"
                  fill="var(--color-new)"
                  fillOpacity={0.15}
                  stroke="var(--color-new)"
                  strokeWidth={2}
                />
              </AreaChart>
            </ChartContainer>
            <div className="mt-1 flex items-center justify-center gap-6 text-xs text-muted-foreground">
              <span className="flex items-center gap-1.5">
                <span className="h-2 w-2 rounded-[2px] bg-[var(--chart-1)]" />
                Total Customers
              </span>
              <span className="flex items-center gap-1.5">
                <span className="h-2 w-2 rounded-[2px] bg-[var(--chart-2)]" />
                New Customers
              </span>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
