import {
  Bar,
  BarChart,
  CartesianGrid,
  XAxis,
  YAxis,
} from "recharts";
import {
  ChartContainer,
  ChartTooltipContent,
} from "@/Components/ui/chart";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { BarChart3 } from "lucide-react";
import type { ChartConfig } from "@/Components/ui/chart";

interface RevenueDay {
  date: string;
  revenue: number;
  orders: number;
}

interface RevenueChartProps {
  data: RevenueDay[];
}

const currency = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "EUR",
  minimumFractionDigits: 0,
  maximumFractionDigits: 0,
});

export default function RevenueChart({ data }: RevenueChartProps) {
  const chartConfig = {
    revenue: {
      label: "Revenue",
      color: "var(--chart-1)",
    },
    orders: {
      label: "Orders",
      color: "var(--chart-2)",
    },
  } satisfies ChartConfig;

  if (data.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <BarChart3 className="h-4 w-4" />
            Revenue (Last 14 Days)
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground text-center py-8">No revenue data yet.</p>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle className="flex items-center gap-2 text-sm font-medium">
          <BarChart3 className="h-4 w-4" />
          Revenue (Last 14 Days)
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ChartContainer config={chartConfig} className="h-[280px] w-full">
          <BarChart accessibilityLayer data={data} margin={{ top: 8, right: 8, left: -16, bottom: 4 }}>
            <CartesianGrid vertical={false} strokeDasharray="3 3" />
            <XAxis
              dataKey="date"
              tickLine={false}
              tickMargin={10}
              axisLine={false}
              fontSize={11}
            />
            <YAxis
              tickLine={false}
              axisLine={false}
              fontSize={11}
              tickFormatter={(v: number) => `€${(v / 1000).toFixed(0)}k`}
            />
            <ChartTooltipContent
              indicator="dot"
              formatter={(value, name) => {
                const v = typeof value === "number" ? value : Number(value);
                return (
                  <div className="flex items-center gap-2">
                    <span className="text-muted-foreground">{typeof name === "string" && name === "revenue" ? "Revenue" : "Orders"}</span>
                    <span className="font-mono font-medium tabular-nums text-foreground">
                      {typeof name === "string" && name === "revenue" ? currency.format(v) : v}
                    </span>
                  </div>
                );
              }}
            />
            <Bar
              dataKey="revenue"
              fill="var(--color-revenue)"
              radius={[4, 4, 0, 0]}
              maxBarSize={40}
            />
          </BarChart>
        </ChartContainer>
        <div className="mt-2 flex items-center justify-center gap-6 text-xs text-muted-foreground">
          <span className="flex items-center gap-1.5">
            <span className="h-2 w-2 rounded-[2px] bg-[var(--chart-1)]" />
            Revenue
          </span>
          <span className="flex items-center gap-1.5">
            <span className="h-2 w-2 rounded-[2px] bg-[var(--chart-2)]" />
            Orders
          </span>
        </div>
      </CardContent>
    </Card>
  );
}
