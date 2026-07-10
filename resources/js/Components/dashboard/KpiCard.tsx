import { TrendingUp, TrendingDown, Minus } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import Sparkline from "@/Components/dashboard/Sparkline";

interface KpiCardProps {
  title: string;
  subtitle?: string;
  value: string;
  change?: number;
  icon: React.ComponentType<{ className?: string }>;
  sparklineData?: number[];
  description?: string;
}

export default function KpiCard({
  title,
  subtitle,
  value,
  change,
  icon: Icon,
  sparklineData,
  description,
}: KpiCardProps) {
  const isPositive = change !== undefined && change > 0;
  const isNegative = change !== undefined && change < 0;
  const isNeutral = change === 0;

  return (
    <Card className="relative overflow-hidden">
      <CardHeader className="flex flex-row items-center justify-between pb-2">
        <div className="min-w-0">
          <CardTitle className="text-sm font-medium truncate">{title}</CardTitle>
          {subtitle && <p className="text-[10px] text-muted-foreground mt-0.5">{subtitle}</p>}
        </div>
        <Icon className="h-4 w-4 shrink-0 text-muted-foreground" />
      </CardHeader>
      <CardContent className="space-y-2">
        <p className="text-2xl font-bold tracking-tight">{value}</p>
        <div className="flex items-center gap-2">
          {sparklineData && sparklineData.length > 0 && <Sparkline data={sparklineData} />}
          {change !== undefined && (
            <span
              className={`inline-flex items-center gap-0.5 text-xs font-medium ${
                isPositive
                  ? "text-emerald-600"
                  : isNegative
                    ? "text-red-600"
                    : "text-muted-foreground"
              }`}
            >
              {isPositive && <TrendingUp className="h-3 w-3" />}
              {isNegative && <TrendingDown className="h-3 w-3" />}
              {isNeutral && <Minus className="h-3 w-3" />}
              {isPositive && "+"}
              {change}%
            </span>
          )}
        </div>
        {description && <p className="text-xs text-muted-foreground">{description}</p>}
      </CardContent>
    </Card>
  );
}
