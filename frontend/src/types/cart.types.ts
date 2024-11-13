export interface OrderAttribute {
  name: string;
  value: string;
}

export interface CreateOrderInput {
  userId: number;
  productId: string;
  quantity: number;
  attributes: OrderAttribute[];
}

export interface attributeType {
  name: string;
  value: string;
}

export interface CreateOrderResponse {
  data: {
    createOrder: number;
  };
}

export interface CartContextType {
  addToCart: (
    prodId: string,
    quantity: number,
    attributes: attributeType[]
  ) => Promise<CreateOrderResponse | undefined>;
}
