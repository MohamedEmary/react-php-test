import { ProductType } from "./other.types";

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

interface CartProductType {
  id: string;
  name: string;
  brand: string;
  price: number;
  category: string;
  description: string;
  attributes: {
    name: string;
    type: string;
    selectedValue: string;
  }[];
  imageUrl: string;
}

export interface getUserCart {
  id: string;
  quantity: number;
  product: CartProductType;
  totalPrice: number;
  currencySymbol: string;
}

interface changeQuantity {
  id: string;
  quantity: number;
}

interface addOrderRes {
  addOrder: string;
}

export interface CartContextType {
  handleAddToCart: (state: ProductType) => void;
  handleGetUserCart: (userId: number) => Promise<getUserCart[]>;
  changeItemQuantity: (
    increase: boolean,
    id: number
  ) => Promise<changeQuantity>;
  addOrder: (userId: number) => Promise<addOrderRes>;
}
