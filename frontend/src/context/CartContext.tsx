import axios from "axios";
import { Component, createContext } from "react";
import {
  attributeType,
  CartContextType,
  AddToCartResponse,
} from "../types/cart.types";

export const cartContext = createContext<CartContextType | null>(null);

interface propsType {
  children: React.ReactNode;
}

export default class CartContextProvider extends Component<propsType> {
  addToCart = async (
    prodId: string,
    quantity: number,
    attributes: attributeType[]
  ): Promise<AddToCartResponse | undefined> => {
    const data = {
      query: `
        mutation {
          addToCart(
            userId: 1,
            productId: "${prodId}",
            quantity: ${quantity},
            attributes: ${JSON.stringify(attributes).replace(
              /"([^"]+)":/g,
              "$1:"
            )}
          )
        }`,
    };

    console.log(data);

    const config = {
      method: "post",
      url: "http://localhost:8000/graphql",
      data: data,
    };

    try {
      const response = await axios.request<AddToCartResponse>(config);
      console.log("from context function:", response.data);
      return response.data;
    } catch (error) {
      console.log(error);
      return undefined;
    }
  };

  render(): React.ReactNode {
    return (
      <cartContext.Provider value={{ addToCart: this.addToCart }}>
        {this.props.children}
      </cartContext.Provider>
    );
  }
}
