export interface ProductType {
  name: string;
  in_stock: boolean;
  id: string;
  images: {
    image_url: string;
  }[];
  description: string;
  attributes: {
    name: string;
    type: string;
    values: string[];
  }[];
  prices: {
    amount: number;
    currency: {
      symbol: string;
    };
  }[];
  selectedAttributes: {
    [key: string]: string;
  };
  currentImageIndex: number;
}
