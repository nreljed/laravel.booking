import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import { ShoppingCart } from "lucide-react";

interface RecentOrder {
  id: number;
  order_number: string;
  customer_name: string;
  total: number;
  status: string;
  items_count: number;
  created_at: string;
}

interface RecentOrdersTableProps {
  orders: RecentOrder[];
}

const statusBadge: Record<string, { color: string; label: string }> = {
  pending: {
    color: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
    label: "Pending",
  },
  confirmed: {
    color: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    label: "Confirmed",
  },
  processing: {
    color: "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400",
    label: "Processing",
  },
  completed: {
    color: "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400",
    label: "Completed",
  },
  cancelled: {
    color: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    label: "Cancelled",
  },
  refunded: {
    color: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
    label: "Refunded",
  },
};

function getInitials(name: string): string {
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
}

const currency = new Intl.NumberFormat("en-US", { style: "currency", currency: "EUR" });

export default function RecentOrdersTable({ orders }: RecentOrdersTableProps) {
  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle className="flex items-center gap-2 text-sm font-medium">
          <ShoppingCart className="h-4 w-4" />
          Recent Orders
        </CardTitle>
      </CardHeader>
      <CardContent className="p-0">
        {orders.length === 0 ? (
          <div className="py-8 text-center text-sm text-muted-foreground">No orders yet.</div>
        ) : (
          <div className="divide-y">
            {orders.map((order) => {
              const statusStyle = statusBadge[order.status] ?? { color: "", label: order.status };
              return (
                <div key={order.id} className="flex items-center gap-3 px-6 py-3 text-sm">
                  <Avatar className="h-8 w-8 shrink-0">
                    <AvatarFallback className="text-[10px] font-medium bg-muted">
                      {getInitials(order.customer_name)}
                    </AvatarFallback>
                  </Avatar>
                  <div className="flex-1 min-w-0">
                    <span className="font-medium truncate block leading-tight">
                      {order.customer_name}
                    </span>
                    <span className="text-xs text-muted-foreground">
                      {order.items_count} items &middot; {order.created_at}
                    </span>
                  </div>
                  <div className="flex items-center gap-3 shrink-0">
                    <span className="font-medium tabular-nums text-right">
                      {currency.format(order.total)}
                    </span>
                    <Badge
                      variant="outline"
                      className={`text-[10px] capitalize px-1.5 py-0 h-5 ${statusStyle.color}`}
                    >
                      {statusStyle.label}
                    </Badge>
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
