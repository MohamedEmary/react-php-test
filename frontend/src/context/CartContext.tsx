import axios from "axios";
import { Component, createContext } from "react";
import { CartContextType } from "../types/cart.types";
import { ProductResponse, ProductType } from "../types/other.types";
import toast from "react-hot-toast";

export const cartContext = createContext<CartContextType | null>(null);

interface propsType {
  children: React.ReactNode;
}

export default class CartContextProvider extends Component<
  propsType,
  ProductType
> {
  handleAddToCart = async (state: ProductType) => {
    if (state.in_stock) {
      const selectedAttributesArr = [];
      for (const [key, value] of Object.entries(state.selectedAttributes)) {
        selectedAttributesArr.push({ name: key, value: value });
      }

      const data = {
        query: `
          mutation {
            addToCart(
                userId: ${1},
                productId: "${state.id}",
                attributes: ${JSON.stringify(selectedAttributesArr).replace(
                  /"([^"]+)":/g,
                  "$1:"
                )}
            )
          }`,
      };

      const config = {
        method: "post",
        url: "http://localhost:8000/graphql",
        data: data,
      };

      const toastId = toast.loading("Adding product to cart...");

      try {
        const response = await axios.request<ProductResponse>(config);
        if (response.data.data.addToCart) {
          toast.success("Product added to cart", { id: toastId });
        } else {
          toast.error(`${response.data.errors[0].message}`, { id: toastId });
        }
      } catch (error) {
        toast.error(
          "Something went wrong, Please refresh the page and try again",
          { id: toastId }
        );
        console.log("Error fetching product data:", error);
        return undefined;
      }
    }
  };

  render(): React.ReactNode {
    return (
      <cartContext.Provider value={{ handleAddToCart: this.handleAddToCart }}>
        {this.props.children}
      </cartContext.Provider>
    );
  }
}
