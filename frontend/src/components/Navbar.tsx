import { Component } from "react";
import logo from "../assets/logo.svg";
import axios from "axios";
import { Link, NavLink } from "react-router-dom";
import { ShoppingCart } from "lucide-react";
import { userContext } from "../context/UserContext";
import { cartContext } from "../context/CartContext";
import { CartContextType } from "../types/cart.types";
import { getUserCart } from "../types/cart.types";
import CartItems from "./CartItems";

interface navbarStateType {
  categories: string[];
  isCartOpen: boolean;
  cartItems: getUserCart[];
}

export default class Navbar extends Component<object, navbarStateType> {
  state: Readonly<navbarStateType> = {
    categories: [""],
    isCartOpen: false,
    cartItems: [],
  };

  static contextType = userContext;
  declare context: React.ContextType<typeof userContext>;

  fetchCategories = async () => {
    const data = {
      query: `query {
        GetCategories
      }`,
    };

    const config = {
      method: "post",
      url: "http://localhost:8000/graphql",
      data: data,
    };

    axios
      .request(config)
      .then((response) => {
        this.setState({
          categories: response.data.data.GetCategories,
        });
      })
      .catch((error) => {
        console.log(error);
      });
  };

  handleShowCart = async (userId: number, cartCtx: CartContextType | null) => {
    const cartItems = await cartCtx?.handleGetUserCart(userId);
    if (cartItems) {
      this.setState({
        cartItems,
        isCartOpen: true,
      });
    }
  };

  async componentDidMount(): Promise<void> {
    this.fetchCategories();
  }

  render(): React.ReactNode {
    return (
      <userContext.Consumer>
        {(userCtx) => (
          <cartContext.Consumer>
            {(cartCtx) => (
              <>
                <nav className="grid grid-cols-10 h-[80px] items-center z-10 fixed top-0 px-[100px] left-0 right-0 shadow-sm bg-white">
                  <ul className="col-span-2 capitalize flex justify-between">
                    <li>
                      <NavLink to="/">All</NavLink>
                    </li>
                    {this.state.categories.map((category, index) => (
                      <li key={index}>
                        <NavLink to={`/categories/${category}`}>
                          {category}
                        </NavLink>
                      </li>
                    ))}
                  </ul>

                  <div className="col-span-6 flex justify-center relative">
                    <Link to="/">
                      <img src={logo} alt="Site Logo" className="block" />
                    </Link>
                  </div>

                  <div className="col-span-2 flex justify-end">
                    <div className="relative">
                      <ShoppingCart
                        className="text-gray-600 cursor-pointer"
                        onClick={() =>
                          userCtx?.userId
                            ? this.handleShowCart(userCtx?.userId, cartCtx)
                            : ""
                        }
                      />
                      {this.state.cartItems.length > 0 && (
                        <div className="absolute -top-2 -right-2 h-5 w-5 flex items-center justify-center text-xs font-bold text-white bg-gray-500 rounded-full">
                          {this.state.cartItems.length}
                        </div>
                      )}
                    </div>
                  </div>
                </nav>

                {this.state.isCartOpen && (
                  <CartItems
                    items={this.state.cartItems}
                    onClose={() => this.setState({ isCartOpen: false })}
                  />
                )}
              </>
            )}
          </cartContext.Consumer>
        )}
      </userContext.Consumer>
    );
  }
}
