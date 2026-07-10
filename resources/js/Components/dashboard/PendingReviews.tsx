import { router, Link } from "@inertiajs/react";
import { MessageSquare, Star, Check, X } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";

interface ReviewUser {
  id: number;
  name: string;
}

interface ReviewProduct {
  id: number;
  name: string;
  slug: string;
  thumbnail_url: string | null;
}

interface PendingReview {
  id: number;
  product: ReviewProduct;
  user: ReviewUser;
  rating: number;
  title: string;
  content: string;
  created_at: string;
}

interface PendingReviewsProps {
  reviews: PendingReview[];
}

function StarRating({ rating }: { rating: number }) {
  return (
    <div className="flex items-center gap-0.5">
      {Array.from({ length: 5 }, (_, i) => (
        <Star
          key={i}
          className={`h-3 w-3 ${i < rating ? "fill-amber-400 text-amber-400" : "fill-none text-muted-foreground/30"}`}
        />
      ))}
    </div>
  );
}

function handleApprove(id: number) {
  router.post(route("dashboard.shop.reviews.approve", id), undefined, {
    preserveScroll: true,
  });
}

function handleReject(id: number) {
  router.post(route("dashboard.shop.reviews.reject", id), undefined, {
    preserveScroll: true,
  });
}

export default function PendingReviews({ reviews }: PendingReviewsProps) {
  if (reviews.length === 0) return null;

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between pb-2">
        <div className="flex items-center gap-2">
          <MessageSquare className="h-4 w-4 text-sky-500" />
          <CardTitle className="text-sm font-medium">Pending Reviews</CardTitle>
        </div>
        <Badge variant="secondary" className="text-xs">
          {reviews.length}
        </Badge>
      </CardHeader>
      <CardContent className="space-y-3">
        {reviews.map((review) => (
          <div key={review.id} className="space-y-1.5 border-b pb-3 last:border-b-0 last:pb-0">
            <div className="flex items-center justify-between">
              <Link
                href={route("dashboard.shop.products.edit", review.product.id)}
                className="text-sm font-medium hover:underline truncate"
              >
                {review.product.name}
              </Link>
              <StarRating rating={review.rating} />
            </div>
            {review.title && (
              <p className="text-xs font-medium text-muted-foreground">{review.title}</p>
            )}
            {review.content && (
              <p className="text-xs text-muted-foreground line-clamp-2">{review.content}</p>
            )}
            <div className="flex items-center justify-between pt-1">
              <span className="text-xs text-muted-foreground">by {review.user.name}</span>
              <div className="flex items-center gap-1">
                <Button
                  variant="ghost"
                  size="sm"
                  className="h-7 w-7 p-0 text-green-600 hover:text-green-700 hover:bg-green-100"
                  onClick={() => handleApprove(review.id)}
                  title="Approve"
                >
                  <Check className="h-3.5 w-3.5" />
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  className="h-7 w-7 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                  onClick={() => handleReject(review.id)}
                  title="Reject"
                >
                  <X className="h-3.5 w-3.5" />
                </Button>
              </div>
            </div>
          </div>
        ))}
        <Button asChild variant="outline" size="sm" className="w-full mt-1">
          <Link href={route("dashboard.shop.reviews.index", { status: "pending" })}>
            <MessageSquare className="mr-1.5 h-3.5 w-3.5" />
            Moderate Reviews
          </Link>
        </Button>
      </CardContent>
    </Card>
  );
}
