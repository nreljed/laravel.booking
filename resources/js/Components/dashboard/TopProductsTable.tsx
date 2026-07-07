import { Link } from "@inertiajs/react";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import { Package, Star } from "lucide-react";

interface TopProduct {
  product_id: number;
  product_name: string;
  total_qty: number;
  total_revenue: number;
  category?: string;
  rating?: number;
  stock?: number;
  max_stock?: number;
}

interface TopProductsTableProps {
  products: TopProduct[];
}

const currency = new Intl.NumberFormat("en-US", { style: "currency", currency: "EUR" });

export default function TopProductsTable({ products }: TopProductsTableProps) {
  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle className="flex items-center gap-2 text-sm font-medium">
          <Package className="h-4 w-4" />
          Top Products
        </CardTitle>
        <Link
          href={route("dashboard.shop.products.index")}
          className="text-xs text-muted-foreground hover:text-foreground"
        >
          View All
        </Link>
      </CardHeader>
      <CardContent className="p-0">
        {products.length === 0 ? (
          <div className="py-8 text-center text-sm text-muted-foreground">
            No products sold yet.
          </div>
        ) : (
          <div className="divide-y">
            {products.map((product, i) => {
              const rating = product.rating ?? 0;
              return (
                <div
                  key={product.product_id}
                  className="flex items-center gap-3 px-6 py-3 text-sm"
                >
                  <span className="text-xs text-muted-foreground w-4 shrink-0 font-medium">
                    #{i + 1}
                  </span>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      <Link
                        href={route("dashboard.shop.products.edit", product.product_id)}
                        className="font-medium hover:underline truncate"
                      >
                        {product.product_name}
                      </Link>
                      {product.category && (
                        <Badge variant="outline" className="text-[10px] px-1.5 py-0 h-4 shrink-0">
                          {product.category}
                        </Badge>
                      )}
                    </div>
                    <div className="flex items-center gap-3 mt-1">
                      {rating > 0 && (
                        <span className="flex items-center gap-0.5 text-xs text-muted-foreground">
                          <Star className="h-3 w-3 fill-amber-400 text-amber-400" />
                          {rating.toFixed(1)}
                        </span>
                      )}
                      <span className="text-xs text-muted-foreground">
                        {product.total_qty} sold
                      </span>
                    </div>
                  </div>
                  <div className="flex flex-col items-end shrink-0">
                    <span className="font-medium tabular-nums">
                      {currency.format(product.total_revenue)}
                    </span>
                    {product.stock !== undefined && (
                      <div className="flex items-center gap-1.5 mt-1">
                        <div className="h-1.5 w-16 bg-muted rounded-full overflow-hidden">
                          <div
                            className={`h-full rounded-full ${
                              product.stock < 10 ? "bg-red-500" : product.stock < 50 ? "bg-amber-500" : "bg-emerald-500"
                            }`}
                            style={{
                              width: `${Math.min(
                                ((product.stock ?? 0) / (product.max_stock ?? product.stock ?? 100)) * 100,
                                100,
                              )}%`,
                            }}
                          />
                        </div>
                        <span className="text-[10px] text-muted-foreground tabular-nums">
                          {product.stock}
                        </span>
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
