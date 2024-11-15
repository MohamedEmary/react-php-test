import { Component } from "react";
import logo from "../assets/logo.svg";
import axios from "axios";
import { Link, NavLink } from "react-router-dom";
import { ShoppingCart } from "lucide-react";

interface navbarStateType {
  categories: string[];
}

export default class Navbar extends Component<object, navbarStateType> {
  state: Readonly<navbarStateType> = {
    categories: [""],
  };

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

  componentDidMount(): void {
    this.fetchCategories();
  }

  render(): React.ReactNode {
    return (
      <nav className="grid grid-cols-10 h-[80px] items-center z-10 fixed top-0 px-[100px] left-0 right-0 shadow-sm bg-white">
        <ul className="col-span-2 capitalize flex justify-between">
          <li>
            <NavLink to="/">All</NavLink>
          </li>
          {this.state.categories.map((category, index) => (
            <li key={index}>
              <NavLink to={`/categories/${category}`}>{category}</NavLink>
            </li>
          ))}
        </ul>
        <div className="col-span-6 flex justify-center">
          <Link to="/">
            <img src={logo} alt="Site Logo" className="block" />
          </Link>
        </div>
        <div className="col-span-2 flex justify-end">
          <ShoppingCart className="text-gray-600" />
        </div>
      </nav>
    );
  }
}
