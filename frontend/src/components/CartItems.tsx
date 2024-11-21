import { Component, ReactNode } from "react";
import { getUserCart } from "../types/cart.types";
import { cartContext } from "../context/CartContext";
import toast from "react-hot-toast";

interface CartItemsProps {
  items: getUserCart[];
  onClose: () => void;
}

interface CartItemsState {
  cartItems: getUserCart[];
}

export default class CartItems extends Component<
  CartItemsProps,
  CartItemsState
> {
  static contextType = cartContext;
  declare context: React.ContextType<typeof cartContext>;

  constructor(props: CartItemsProps) {
    super(props);
    this.state = {
      cartItems: props.items,
    };
  }

  getTotalPrice = () => {
    return this.state.cartItems
      .reduce((total, item) => total + item.totalPrice, 0)
      .toFixed(2);
  };

  handleChangeQuantity = async (increase: boolean, itemId: number) => {
    const res = await this.context?.changeItemQuantity(increase, itemId);
    if (res?.quantity) {
      this.setState((prevState) => ({
        cartItems: prevState.cartItems.map((item) =>
          item.id === String(itemId)
            ? {
                ...item,
                totalPrice: res.quantity * item.product.price,
                quantity: res.quantity,
              }
            : item
        ),
      }));
    }
  };

  handlePlaceOrder = async () => {
    const userId = 1
    //userId: number should be a param
    console.log("inside place order");
    const res = await this.context?.addOrder(userId);
    console.log(res);
    if (res?.addOrder.startsWith("Successfully")) {
      toast.success("Order placed successfully");
    }
  };

  render(): ReactNode {
    const { onClose } = this.props;
    const { cartItems } = this.state;

    return (
      <>
        <div className="fixed inset-0 bg-black/50 z-20" onClick={onClose} />
        <div className="fixed top-[40px] right-[110px] w-[400px] max-h-[600px] overflow-y-auto bg-white p-4 z-30 shadow-lg">
          <h2 className="text-2xl mb-4">Shopping Cart</h2>
          {cartItems.length === 0 ? (
            <p>Your cart is empty</p>
          ) : (
            <>
              {cartItems.map((item) => (
                <div
                  key={item.id}
                  className="flex flex-col border-b py-4"
                  data-testid={`cart-item-${item.product.name
                    .toLowerCase()
                    .replace(/\s+/g, "-")}`}
                >
                  <div className="flex flex-row-reverse gap-4">
                    <div className="flex gap-4 items-center">
                      <div className="flex flex-col justify-center gap-2">
                        <button
                          className="w-6 h-6 border border-gray-700 flex items-center justify-center text-sm"
                          data-testid="cart-item-amount-increase"
                          onClick={() =>
                            this.handleChangeQuantity(true, Number(item.id))
                          }
                        >
                          +
                        </button>
                        <span
                          className="text-base text-center"
                          data-testid="cart-item-amount"
                        >
                          {item.quantity}
                        </span>
                        <button
                          className="w-6 h-6 border border-gray-700 flex items-center justify-center text-sm"
                          data-testid="cart-item-amount-decrease"
                          onClick={() =>
                            this.handleChangeQuantity(false, Number(item.id))
                          }
                        >
                          -
                        </button>
                      </div>
                      <img
                        src={item.product.imageUrl}
                        alt={item.product.name}
                        className="w-24 h-24 object-contain"
                      />
                    </div>
                    <div className="flex-1">
                      <h3 className="text-xl font-medium mb-2">
                        {item.product.name}
                      </h3>
                      <p className="text-lg mb-4">
                        {item.currencySymbol}
                        {item.totalPrice.toFixed(2)}
                      </p>

                      {item.product.attributes.map((attr) => (
                        <div key={attr.name} className="mb-4">
                          <h2 className="text-sm font-medium mb-2">
                            {attr.name.toUpperCase()}:
                          </h2>
                          <div className="flex gap-2">
                            {attr.type === "swatch" ? (
                              <div
                                className={`w-5 h-5 ring-2 ring-black ring-offset-2`}
                                style={{
                                  backgroundColor:
                                    attr.selectedValue.toLowerCase(),
                                }}
                                data-testid={`cart-item-attribute-${attr.name
                                  .toLowerCase()
                                  .replace(/\s+/g, "-")}-${attr.selectedValue
                                  .toLowerCase()
                                  .replace(/\s+/g, "-")}-selected`}
                              />
                            ) : (
                              <div
                                className="w-8 h-8 flex items-center justify-center border border-black"
                                data-testid={`cart-item-attribute-${attr.name
                                  .toLowerCase()
                                  .replace(/\s+/g, "-")}-${attr.selectedValue
                                  .toLowerCase()
                                  .replace(/\s+/g, "-")}-selected`}
                              >
                                {attr.selectedValue}
                              </div>
                            )}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              ))}

              <div className="pt-6">
                <div className="flex justify-between text-lg font-medium mb-4">
                  <span>Total:</span>
                  <span>
                    {cartItems[0]?.currencySymbol}
                    {this.getTotalPrice()}
                  </span>
                </div>
                <button
                  className="w-full py-3 px-6 bg-emerald-500 hover:bg-emerald-600 text-white transition-colors disabled:bg-gray-200 disabled:text-gray-500"
                  disabled={cartItems.length === 0}
                  onClick={() => this.handlePlaceOrder()}
                >
                  PLACE ORDER
                </button>
              </div>
            </>
          )}
        </div>
      </>
    );
  }
}
