import { Link } from "@inertiajs/react";
import { AlertTriangle, Package } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { cn } from "@/lib/utils";

interface LowStockProduct {
  id: number;
  name: string;
  slug: string;
  thumbnail_url: string | null;
  stock_quantity: number;
  low_stock_threshold: number;
  track_stock: boolean;
}

interface LowStockAlertsProps {
  products: LowStockProduct[];
}

export default function LowStockAlerts({ products }: LowStockAlertsProps) {
  if (products.length === 0) return null;

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between pb-2">
        <div className="flex items-center gap-2">
          <AlertTriangle className="h-4 w-4 text-amber-500" />
          <CardTitle className="text-sm font-medium">Low Stock Alerts</CardTitle>
        </div>
        <Badge variant="destructive" className="text-xs">{products.length}</Badge>
      </CardHeader>
      <CardContent className="space-y-3">
        {products.map((product) => {
          const ratio = product.low_stock_threshold > 0
            ? Math.min(product.stock_quantity / product.low_stock_threshold, 1)
            : 0;
          const isCritial = product.stock_quantity === 0;
          return (
            <div key={product.id} className="flex items-center gap-3">
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-muted">
                {product.thumbnail_url ? (
                  <img
                    src={product.thumbnail_url}
                    alt=""
                    className="h-9 w-9 rounded-md object-cover"
                  />
                ) : (
                  <Package className="h-4 w-4 text-muted-foreground" />
                )}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium truncate">{product.name}</p>
                <div className="flex items-center gap-2 mt-1">
                  <div className="flex-1 h-1.5 rounded-full bg-muted overflow-hidden">
                    <div
                      className={cn(
                        "h-full rounded-full transition-all",
                        isCritial ? "bg-destructive" : ratio < 0.5 ? "bg-amber-500" : "bg-green-500",
                      )}
                      style={{ width: `${ratio * 100}%` }}
                    />
                  </div>
                  <span className={cn(
                    "text-xs tabular-nums shrink-0",
                    isCritial ? "text-destructive font-semibold" : "text-muted-foreground",
                  )}>
                    {product.stock_quantity} / {product.low_stock_threshold}
                  </span>
                </div>
              </div>
              <Button asChild variant="ghost" size="sm" className="shrink-0 h-7 px-2">
                <Link href={route("dashboard.shop.products.edit", product.id)}>
                  Edit
                </Link>
              </Button>
            </div>
          );
        })}
        <Button asChild variant="outline" size="sm" className="w-full mt-1">
          <Link href={route("dashboard.shop.products.index", { status: "low-stock" })}>
            <Package className="mr-1.5 h-3.5 w-3.5" />
            View All Products
          </Link>
        </Button>
      </CardContent>
    </Card>
  );
}
