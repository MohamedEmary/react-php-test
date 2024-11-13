import { Component } from "react";
import { productType } from "../types/other.types";
import { Link } from "react-router-dom";
import greenCart from "../assets/green-cart.svg";

interface ProductCardProps {
  product: productType;
}

export default class ProductCard extends Component<ProductCardProps> {
  render() {
    const { product } = this.props;
    const { name, images, in_stock, prices, id } = product;

    return (
      <div
        className="relative group w-[386px] p-4 hover:shadow-md transition-shadow"
        data-testid={`product-${id}`}
      >
        <Link to={`/product/${id}`}>
          <div className="relative h-[330px]">
            <img
              loading="lazy"
              src={images[0].image_url}
              alt={name}
              className={`w-full h-full object-cover object-top transition-opacity ${
                !in_stock ? "opacity-50" : ""
              }`}
            />
            {!in_stock && (
              <div className="absolute inset-0 flex items-center justify-center">
                <span className="text-[24px] uppercase text-[#8D8F9A]">
                  Out of stock
                </span>
              </div>
            )}
          </div>
        </Link>

        <div className="mt-4 h-[58px]">
          <Link to={`/product/${id}`}>
            <h3 className="text-[18px] font-light truncate hover:underline transition-all">
              {name}
            </h3>
          </Link>

          <p className="text-[18px] font-medium">
            {prices[0].currency.symbol}
            {prices[0].amount.toFixed(2)}
          </p>
        </div>

        {in_stock && (
          <Link to={`/product/${id}`}>
            <button className="absolute bottom-16 right-7 bg-[#5ECE7B] rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
              <img src={greenCart} alt="add to cart" />
            </button>
          </Link>
        )}
      </div>
    );
  }
}

/*


        mutation {
          createOrder(
            userId: 1,
            productId: "ps-5",
            quantity: 1,
            attributes: [{"name":"Color","value":"#000000"},{"name":"Size","value":"XL"}]
          )
        }
*/
