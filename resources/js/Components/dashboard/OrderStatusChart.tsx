import { Pie, PieChart, Cell } from "recharts";
import {
  ChartContainer,
  ChartTooltipContent,
} from "@/Components/ui/chart";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { BarChart3 } from "lucide-react";
import type { ChartConfig } from "@/Components/ui/chart";

interface OrderStatusChartProps {
  data: Record<string, number>;
}

const STATUS_COLORS: Record<string, string> = {
  pending: "var(--chart-4)",
  confirmed: "var(--chart-3)",
  processing: "var(--chart-2)",
  completed: "var(--chart-1)",
  cancelled: "var(--chart-5)",
  refunded: "var(--muted-foreground)",
};

const STATUS_ORDER = ["completed", "processing", "confirmed", "pending", "cancelled", "refunded"];

export default function OrderStatusChart({ data }: OrderStatusChartProps) {
  const chartConfig = {} satisfies ChartConfig;

  const items = Object.entries(data)
    .filter(([, count]) => count > 0)
    .sort(([a], [b]) => STATUS_ORDER.indexOf(a) - STATUS_ORDER.indexOf(b))
    .map(([status, count]) => ({
      name: status.charAt(0).toUpperCase() + status.slice(1),
      value: count,
      fill: STATUS_COLORS[status] ?? "var(--muted-foreground)",
    }));

  if (items.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-sm font-medium">
            <BarChart3 className="h-4 w-4" />
            Order Status
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground text-center py-8">No orders yet.</p>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle className="flex items-center gap-2 text-sm font-medium">
          <BarChart3 className="h-4 w-4" />
          Order Status
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ChartContainer config={chartConfig} className="h-[200px] w-full">
          <PieChart margin={{ top: 0, right: 0, left: 0, bottom: 0 }}>
            <Pie
              data={items}
              dataKey="value"
              nameKey="name"
              cx="50%"
              cy="50%"
              innerRadius={50}
              outerRadius={80}
              paddingAngle={1}
              strokeWidth={0}
            >
              {items.map((item, i) => (
                <Cell key={i} fill={STATUS_COLORS[item.name.toLowerCase()] ?? "var(--muted-foreground)"} />
              ))}
            </Pie>
            <ChartTooltipContent
              indicator="dot"
              formatter={(value, name) => {
                const v = typeof value === "number" ? value : Number(value);
                return (
                  <div className="flex items-center gap-2">
                    <span className="capitalize text-muted-foreground">{String(name)}</span>
                    <span className="font-mono font-medium tabular-nums text-foreground">{v}</span>
                  </div>
                );
              }}
            />
          </PieChart>
        </ChartContainer>
        <div className="mt-2 grid grid-cols-2 gap-x-4 gap-y-1">
          {items.map((item) => (
            <div key={item.name} className="flex items-center justify-between text-xs">
              <span className="flex items-center gap-1.5 capitalize">
                <span
                  className="h-2 w-2 shrink-0 rounded-[2px]"
                  style={{ backgroundColor: item.fill }}
                />
                {item.name}
              </span>
              <span className="font-medium tabular-nums">{item.value}</span>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
