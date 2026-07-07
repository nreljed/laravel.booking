import { Pie, PieChart, Cell } from "recharts";
import {
  ChartContainer,
  ChartTooltipContent,
} from "@/Components/ui/chart";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { PieChart as PieIcon } from "lucide-react";
import type { ChartConfig } from "@/Components/ui/chart";

interface BreakdownItem {
  name: string;
  value: number;
  percentage: number;
}

interface RevenueBreakdownChartProps {
  data: BreakdownItem[];
}

const COLORS = [
  "var(--chart-1)",
  "var(--chart-2)",
  "var(--chart-3)",
  "var(--chart-4)",
  "var(--chart-5)",
  "var(--muted-foreground)",
];

const currency = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "EUR",
  minimumFractionDigits: 0,
  maximumFractionDigits: 0,
});

export default function RevenueBreakdownChart({ data }: RevenueBreakdownChartProps) {
  const chartConfig = {} satisfies ChartConfig;

  if (data.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-sm font-medium">
            <PieIcon className="h-4 w-4" />
            Revenue Breakdown
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
          <PieIcon className="h-4 w-4" />
          Revenue Breakdown
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ChartContainer config={chartConfig} className="h-[280px] w-full">
          <PieChart margin={{ top: 0, right: 0, left: 0, bottom: 0 }}>
            <Pie
              data={data}
              dataKey="value"
              nameKey="name"
              cx="50%"
              cy="50%"
              innerRadius={70}
              outerRadius={110}
              paddingAngle={2}
              strokeWidth={0}
            >
              {data.map((_, i) => (
                <Cell key={i} fill={COLORS[i % COLORS.length]} />
              ))}
            </Pie>
            <ChartTooltipContent
              indicator="dot"
              formatter={(value) => {
                const v = typeof value === "number" ? value : Number(value);
                return currency.format(v);
              }}
            />
          </PieChart>
        </ChartContainer>
        <div className="mt-2 space-y-1.5">
          {data.map((item, i) => (
            <div key={item.name} className="flex items-center justify-between text-xs">
              <span className="flex items-center gap-1.5">
                <span
                  className="h-2 w-2 shrink-0 rounded-[2px]"
                  style={{ backgroundColor: COLORS[i % COLORS.length] }}
                />
                {item.name}
              </span>
              <span className="font-medium tabular-nums">
                {item.percentage}%
              </span>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
