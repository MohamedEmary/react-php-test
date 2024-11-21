import axios from "axios";
import { Component, createContext } from "react";
import { CartContextType, getUserCart } from "../types/cart.types";
import { ProductResponse, ProductType } from "../types/other.types";
import toast from "react-hot-toast";
import { userContext } from "./UserContext";

export const cartContext = createContext<CartContextType | null>(null);

interface propsType {
  children: React.ReactNode;
}

interface CartContextState {
  numberOfItems: number;
}

export default class CartContextProvider extends Component<
  propsType,
  CartContextState
> {
  state = {
    numberOfItems: 0,
  };

  static contextType = userContext;
  declare context: React.ContextType<typeof userContext>;

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

  handleGetUserCart = async () => {
    //userId: number parameter
    const data = {
      query: `
        query{
          GetUserCart(userId: 1) {
            id
            quantity
            product {
              id
              name
              price
              brand
              category
              description
              attributes {
                name
                type
                selectedValue
              }
              imageUrl
            }
            totalPrice
            currencySymbol
          }
        }`,
    };

    const config = {
      method: "post",
      url: "http://localhost:8000/graphql",
      data: data,
    };

    const response = await axios.request(config);
    const res: getUserCart[] = response.data.data.GetUserCart;
    return res;
  };

  changeItemQuantity = async (increase: boolean, id: number) => {
    const data = {
      query: `
      mutation {
        ${increase ? "In" : "De"}creaseCartItemQuantity(cartItemId: ${id}){
          id
          quantity
        }
      }`,
    };

    const config = {
      method: "post",
      url: "http://localhost:8000/graphql",
      data: data,
    };

    const response = await axios.request(config);

    return increase
      ? response.data.data.IncreaseCartItemQuantity
      : response.data.data.DecreaseCartItemQuantity;
  };

  addOrder = async (userId: number) => {
    const data = {
      query: `
        mutation {
          addOrder(userId: ${userId})
        }`,
    };

    const config = {
      method: "post",
      url: "http://localhost:8000/graphql",
      data: data,
    };

    const response = await axios.request(config);
    console.log(response.data.data);
    return response.data.data;
  };

  // async componentDidMount(): Promise<void> {
  //   if (this.context?.userId) {
  //     const items = await this.handleGetUserCart(this.context.userId);
  //     this.state.numberOfItems = items.length;
  //   }
  // }

  render(): React.ReactNode {
    return (
      <cartContext.Provider
        value={{
          handleAddToCart: this.handleAddToCart,
          handleGetUserCart: this.handleGetUserCart,
          changeItemQuantity: this.changeItemQuantity,
          addOrder: this.addOrder,
          // numberOfItems: this.state.numberOfItems,
        }}
      >
        {this.props.children}
      </cartContext.Provider>
    );
  }
}
