import axios from "axios";
import { Component } from "react";
import { useParams } from "react-router-dom";
import { ProductType } from "../types/other.types";
import ProductCard from "../components/ProductCard";
import { userContext } from "../context/UserContext";

const CategoryPageWrapper = () => {
  const { category } = useParams();
  return <CategoryPage routeCategory={category} />;
};

interface categoryPagePropsType {
  routeCategory?: string;
}

interface CategoryPageStateType {
  products: ProductType[];
  category: string;
}

class CategoryPage extends Component<
  categoryPagePropsType,
  CategoryPageStateType
> {
  state = {
    products: [],
    category: this.props.routeCategory || "all",
  };

  static contextType = userContext;
  declare context: React.ContextType<typeof userContext>;

  createUser = async () => {
    if (!localStorage.getItem("userId")) {
      const data = {
        query: `
        mutation {
          createUser
        }`,
      };

      try {
        const config = {
          method: "post",
          url: "http://localhost:8000/graphql",
          data: data,
        };
        const response = await axios.request(config);
        const newUserId = response.data.data.createUser;
        this.context?.setUserId(newUserId);
      } catch (error) {
        console.error("Error fetching products:", error);
      }
    }
  };

  fetchProducts = async (category: string) => {
    const data = {
      query: `
      query {
        GetCategoryProducts(category: "${category}"){
            images{
                image_url
            }
            name
            in_stock
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

    try {
      const config = {
        method: "post",
        url: "http://localhost:8000/graphql",
        data: data,
      };
      const response = await axios.request(config);
      this.setState({
        products: response.data.data.GetCategoryProducts,
        category: category,
      });
    } catch (error) {
      console.error("Error fetching products:", error);
    }
  };

  async componentDidMount() {
    await this.createUser();
    await this.fetchProducts(this.state.category);
  }

  async componentDidUpdate(prevProps: { routeCategory?: string }) {
    if (prevProps.routeCategory !== this.props.routeCategory) {
      await this.fetchProducts(this.props.routeCategory || "all");
    }
  }

  render() {
    const { products, category } = this.state;

    return (
      <>
        <h1 className="text-[42px] leading-[67.2px] mb-[100px] capitalize">
          {category}
        </h1>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
          {products.map((product: ProductType, index) => (
            <ProductCard key={index} product={product} />
          ))}
        </div>
      </>
    );
  }
}

export default CategoryPageWrapper;
