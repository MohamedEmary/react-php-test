export interface OrderAttribute {
  name: string;
  value: string;
}

export interface AddToCartInput {
  userId: number;
  productId: string;
  quantity: number;
  attributes: OrderAttribute[];
}

export interface attributeType {
  name: string;
  value: string;
}

export interface AddToCartResponse {
  data: {
    addToCart: number;
  };
}

export interface CartContextType {
  addToCart: (
    prodId: string,
    quantity: number,
    attributes: attributeType[]
  ) => Promise<AddToCartResponse | undefined>;
}
