import React, { Component } from "react";
import axios from "axios";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useParams } from "react-router-dom";
import { ProductResponse, ProductType } from "../types/other.types";
import { cartContext } from "../context/CartContext";

const ProductPageWrapper = () => {
  const { id } = useParams();
  return <ProductPage productId={id} />;
};

interface ProductPageProps {
  productId?: string;
}

class ProductPage extends Component<ProductPageProps, ProductType> {
  static contextType = cartContext;
  declare context: React.ContextType<typeof cartContext>;

  state: ProductType = {
    name: "",
    in_stock: false,
    id: "",
    images: [],
    description: "",
    attributes: [],
    prices: [],
    selectedAttributes: {},
    currentImageIndex: 0,
  };

  async componentDidMount() {
    if (this.props.productId) {
      await this.getProductData();
    }
  }

  getProductData = async () => {
    const data = {
      query: `
        query {
          GetProductWithId(id: "${this.props.productId}"){
              attributes{
                  name
                  type
                  values
              }
              brand
              description
              images{
                  image_url
              }
              in_stock
              name
              id
              prices{
                  amount
                  currency{
                      symbol
                  }
              }
          }
        }`,
    };

    const config = {
      method: "post",
      url: "http://localhost:8000/graphql",
      data: data,
    };

    try {
      const response = await axios.request<ProductResponse>(config);
      const product = response.data.data.GetProductWithId[0];

      this.setState({
        name: product.name,
        in_stock: product.in_stock,
        id: product.id,
        images: product.images,
        description: product.description,
        attributes: product.attributes,
        prices: product.prices,
      });
    } catch (error) {
      console.log("Error fetching product data:", error);
      return undefined;
    }
  };

  handleAttributeSelect = (attributeName: string, value: string) => {
    this.setState((prevState) => ({
      selectedAttributes: {
        ...prevState.selectedAttributes,
        [attributeName]: value,
      },
    }));
  };

  handleImageNavigate = (direction: "prev" | "next") => {
    this.setState((prevState) => ({
      currentImageIndex:
        direction === "next"
          ? (prevState.currentImageIndex + 1) % prevState.images.length
          : (prevState.currentImageIndex - 1 + prevState.images.length) %
            prevState.images.length,
    }));
  };

  handleThumbnailClick = (index: number) => {
    this.setState({ currentImageIndex: index });
  };

  render(): React.ReactNode {
    const {
      name,
      images,
      attributes,
      prices,
      description,
      in_stock,
      currentImageIndex,
    } = this.state;
    const currentPrice = prices[0] || { amount: 0, currency: { symbol: "$" } };

    return (
      <div className="max-w-6xl mx-auto p-8 flex flex-col md:flex-row gap-8">
        {/* Left side */}
        <div className="flex gap-4 w-full md:w-2/3">
          <div className="flex flex-col gap-2">
            {images.map((img, index) => (
              <div
                key={index}
                className={`w-16 h-16 cursor-pointer border-2 ${
                  currentImageIndex === index
                    ? "border-black"
                    : "border-transparent"
                }`}
                onClick={() => this.handleThumbnailClick(index)}
              >
                <img
                  src={img.image_url}
                  alt={`${name} view ${index + 1}`}
                  className="w-full h-full object-cover"
                />
              </div>
            ))}
          </div>

          <div className="relative flex-grow h-[600px] w-[800px]">
            <img
              src={images[currentImageIndex]?.image_url}
              alt={`${name} main view`}
              className="w-full h-full object-contain"
            />
            <button
              className="absolute left-4 top-1/2 -translate-y-1/2 text-white bg-gray-600 p-1 shadow hover:bg-gray-800 opacity-50 transition-colors"
              onClick={() => this.handleImageNavigate("prev")}
            >
              <ChevronLeft className="w-6 h-6" />
            </button>
            <button
              className="absolute right-4 top-1/2 -translate-y-1/2 text-white bg-gray-600 p-1 shadow hover:bg-gray-800 opacity-50 transition-colors"
              onClick={() => this.handleImageNavigate("next")}
            >
              <ChevronRight className="w-6 h-6" />
            </button>
          </div>
        </div>

        {/* Right side */}
        <div className="w-full md:w-1/3">
          <h1 className="text-2xl font-bold mb-6">{name}</h1>

          {attributes.map((attr) => (
            <div key={attr.name} className="mb-6">
              <h2 className="text-sm font-medium mb-2">
                {attr.name.toUpperCase()}:
              </h2>
              <div className="flex gap-2">
                {attr.values.map((value) => (
                  <button
                    key={value}
                    className={`${
                      attr.type === "swatch"
                        ? "w-8 h-8"
                        : "w-14 h-14 border overflow-auto"
                    } ${
                      this.state.selectedAttributes[attr.name] === value
                        ? attr.type === "swatch"
                          ? "ring-2 ring-black ring-offset-2"
                          : "bg-gray-700 text-white"
                        : attr.type === "swatch"
                        ? ""
                        : "border-gray-300"
                    }`}
                    style={
                      attr.type === "swatch"
                        ? {
                            backgroundColor: value.toLowerCase(),
                          }
                        : undefined
                    }
                    onClick={() => this.handleAttributeSelect(attr.name, value)}
                  >
                    {attr.type !== "swatch" && value}
                  </button>
                ))}
              </div>
            </div>
          ))}

          <div className="mb-6">
            <h2 className="text-sm font-medium mb-2">PRICE:</h2>
            <p className="text-xl">
              {currentPrice.currency.symbol}
              {currentPrice.amount.toFixed(2)}
            </p>
          </div>

          <button
            className={`w-full py-3 px-6 transition-colors ${
              in_stock
                ? "bg-emerald-500 hover:bg-emerald-600 text-white"
                : "bg-gray-200 text-gray-500 cursor-not-allowed"
            }`}
            onClick={() => this.context?.handleAddToCart(this.state)}
            disabled={!in_stock}
          >
            {in_stock ? "ADD TO CART" : "OUT OF STOCK"}
          </button>

          <div
            className="mt-12 text-gray-600 text-sm"
            // since the description is in html not plain text
            dangerouslySetInnerHTML={{ __html: description }}
          />
        </div>
      </div>
    );
  }
}

export default ProductPageWrapper;
