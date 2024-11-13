export interface productType {
  images: {
    image_url: string;
  }[];
  name: string;
  in_stock: boolean;
  id: string;
  prices: {
    amount: number;
    currency: {
      symbol: string;
    };
  }[];
}
